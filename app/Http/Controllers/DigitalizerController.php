<?php

namespace App\Http\Controllers;

use App\Actions\MountPagesDataFromDigitalizationBatch;
use App\Factories\DigitalizesFactory;
use App\Http\Requests\DeleteDigitalizationRequest;
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
        if (auth()->check()){
            $user = auth()->user();
            $digitalizationBatch = $user->digitalizationBatches()->create([
                'title' => $files[0]->getClientOriginalName() ?? 'undefined title',
            ]);
        }

        foreach ($files as $file) {

            $imageUrl = '';
            $originalFilePath = null;
            $tempDisplayPath = 'temp_digitalizations/' . $file->hashName();

            Storage::disk('public')->put($tempDisplayPath, file_get_contents($file->getRealPath()));
            $imageUrl = Storage::url($tempDisplayPath);



            $digitalizer = DigitalizesFactory::make();
            $response = '';

            try {
                $parsedContent = $digitalizer->returnJson($file);

                if ($parsedContent instanceof \Illuminate\Http\RedirectResponse) {
                    return $parsedContent;
                }
                $formatted = $digitalizer->formatJsonToHTMLandPlainText($parsedContent);
                $pageData = $formatted['pageData'];
                $plainText = $formatted['plainText'];


            } catch (Exception $e) {
                Log::error('Erro inesperado no DigitalizerController: ' . $e->getMessage());
                return back()->with(['message' => 'Ocorreu um erro inesperado: ' . $e->getMessage(), 'type' => 'error']);
            }

            $digitalization = null;
                if (auth()->check()) {
                    $originalFilePath = $file->storeAs('digitalizations/original_file', $file->hashName(), 'public');

                    $jsonOutputString = json_encode($parsedContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    $jsonFileName = $file->hashName() . '.json';
                    $transcriptionFilePath = 'digitalizations/json_outputs/' . $jsonFileName;
                    Storage::disk('public')->put($transcriptionFilePath, $jsonOutputString);



                    $digitalization = $digitalizationBatch->digitalizations()->create([
                        'original_file_path' => $originalFilePath,
                        'transcription_file_path' => $transcriptionFilePath,
                        'user_id' => $user->id
                    ]);

                    Storage::disk('public')->delete($tempDisplayPath);
                    $imageUrl = Storage::url($originalFilePath);
                    $pages = (new MountPagesDataFromDigitalizationBatch)->execute($digitalizationBatch);
                } else {
                    $pages[] = [
                        'imageUrl' => $imageUrl,
                        'pageData' => $pageData,
                        'plainText' => $plainText,
                    ];
                }
        }

        return view('scan-result', compact('pages', 'digitalization'));
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

    public function destroy(DigitalizationBatch $digitalization)
    {
        if (!auth()->user()->can('destroy', $digitalization)) {
            abort(403, 'Unauthorized');
        }
        try {
            // Apaga arquivos do disco
            Storage::disk('public')->delete($digitalization->original_file_path);
            Storage::disk('public')->delete($digitalization->transcription_file_path);

            // Deleta do banco
            $digitalization->delete();

            return redirect()->route('dashboard')
                ->with(['message' => 'Deleted successfully!', 'type' => 'success']);
        } catch (Exception $e) {
            Log::error('Erro ao deletar digitalization ID ' . $digitalization->id . ': ' . $e->getMessage());

            return redirect()->route('dashboard')
                ->with(['message' => 'Erro ao deletar digitalização.', 'type' => 'error']);
        }
    }
}
