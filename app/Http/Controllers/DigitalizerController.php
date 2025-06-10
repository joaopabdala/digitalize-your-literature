<?php

namespace App\Http\Controllers;

use App\Actions\MountPagesDataFromDigitalizationBatch;
use App\Factories\DigitalizesFactory;
use App\Http\Requests\DigitalizerRequest;
use App\Models\DigitalizationBatch;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use function abort;
use function auth;
use function back;
use function compact;
use function dd;
use function file_get_contents;
use function json_encode;
use function pathinfo;
use function redirect;
use function str_replace;
use function view;
use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_UNICODE;
use const PATHINFO_FILENAME;

class DigitalizerController extends Controller
{
    public function digitalizes(DigitalizerRequest $request)
    {
        $request->validated();
        $files = $request->file('file');

        $folder_path = 'digitalizations/' . now()->format('Ymd_His') . '_' . uniqid();

        $userCheck = auth()->check();
        $userId = $userCheck ? auth()->user()->id : null;

        $digitalizationBatch = DigitalizationBatch::create([
            'title' => $files[0]->getClientOriginalName() ?? 'undefined title',
            'folder_path' => $folder_path,
            'user_id' => $userId,
            'belongs_to_user' => $userCheck
        ]);

        foreach ($files as $file) {

            $digitalizer = DigitalizesFactory::make();

            try {
                $parsedContent = $digitalizer->returnJson($file);

                if ($parsedContent instanceof \Illuminate\Http\RedirectResponse) {
                    return $parsedContent;
                }
            } catch (Exception $e) {
                Log::error('Erro inesperado no DigitalizerController: ' . $e->getMessage());
//                return back()->with(['message' => 'Ocorreu um erro inesperado: ' . $e->getMessage(), 'type' => 'error']);
                continue;
            }

            $jsonOutputString = json_encode($parsedContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $jsonFileName = $file->hashName() . '.json';
            $transcriptionFilePath = $folder_path . '/json_outputs/' . $jsonFileName;
            Storage::disk('public')->put($transcriptionFilePath, $jsonOutputString);
            $originalFilePath = $file->storeAs($folder_path . '/original_files/', $file->hashName(), 'public');


            $digitalizationBatch->digitalizations()->create([
                'original_file_path' => $originalFilePath,
                'transcription_file_path' => $transcriptionFilePath,
                'user_id' => $userId
            ]);

        }
        $pages = (new MountPagesDataFromDigitalizationBatch)->execute($digitalizationBatch);

        return view('scan-result', compact('pages', 'digitalizationBatch'));
    }

    public function downloadPDF(DigitalizationBatch $digitalizationBatch)
    {
        if (!auth()->user()->can('download', $digitalizationBatch)) {
            abort(403, 'Unauthorized');
        }

        try {
            $pages = (new MountPagesDataFromDigitalizationBatch)->execute($digitalizationBatch);
            $html = view('pdf.document_template', compact('pages'))->render();
            $pdf = Pdf::loadHtml($html);
            $baseFileName = pathinfo($digitalizationBatch->title, PATHINFO_FILENAME);
            $pdfFileName = str_replace('gemini_response_', 'documento_', $baseFileName) . '.pdf';

            return $pdf->download($pdfFileName);

        } catch (Exception $e) {
            Log::error('Erro ao gerar PDF: ' . $e->getMessage());
            return back()->with(['message' => 'Não foi possível gerar o PDF: ' . $e->getMessage(), 'type' => 'error']);
        }
    }

    public function destroy(DigitalizationBatch $digitalizationBatch)
    {
        if (!auth()->user()->can('destroy', $digitalizationBatch)) {
            abort(403, 'Unauthorized');
        }

        try {
            Storage::disk('public')->deleteDirectory($digitalizationBatch->folder_path);

            $digitalizationBatch->delete();

            return redirect()->route('dashboard')
                ->with(['message' => 'Deleted successfully!', 'type' => 'success']);
        } catch (Exception $e) {
            Log::error('Erro ao deletar digitalization ID ' . $digitalizationBatch->id . ': ' . $e->getMessage());

            return redirect()->route('dashboard')
                ->with(['message' => 'Erro ao deletar digitalização.', 'type' => 'error']);
        }
    }
}
