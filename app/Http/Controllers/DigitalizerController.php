<?php

namespace App\Http\Controllers;

use App\Actions\ExtractPlaintTextFromJsonAction;
use App\Actions\MountPagesDataFromDigitalizationBatchAction;
use App\Exceptions\InvalidPageDataException;
use App\Models\Digitalization;
use App\Models\DigitalizationBatch;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use function abort;
use function auth;
use function back;
use function compact;
use function data_get;
use function is_null;
use function json_decode;
use function pathinfo;
use function redirect;
use function str_replace;
use function view;
use const PATHINFO_FILENAME;

class DigitalizerController extends Controller
{

    public function downloadPDF(DigitalizationBatch $digitalizationBatch)
    {
        if (!auth()->user()->can('download', $digitalizationBatch)) {
            abort(403, 'Unauthorized');
        }

        try {
            $pages = (new MountPagesDataFromDigitalizationBatchAction)->execute($digitalizationBatch);
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

    public function show($digitalizationBatchHash)
    {
        $digitalizationBatch = DigitalizationBatch::where('folder_path', $digitalizationBatchHash)->firstOrFail();

        if (request()->has('page_id')) {
            $pageId = request()->get('page_id');

            $digitalization = Digitalization::where('digitalization_batch_id', $digitalizationBatch->id)
                ->where('id', $pageId)
                ->first();
            if (!$digitalization) {
                return response()->json(['error' => 'Page not found'], 404);
            }

            $imageUrl = Storage::url($digitalization->original_file_path);
            $transcriptionPath = $digitalization->transcription_file_path;
            $jsonContent = Storage::disk('public')->get($transcriptionPath);
            $jsonDecoded = json_decode($jsonContent, true);

            $pageData = data_get($jsonDecoded, 'page');
            if (is_null($pageData)) {
                throw new InvalidPageDataException('Os dados da página estão ausentes ou inválidos.');
            }

            $plainText = (new ExtractPlaintTextFromJsonAction)->execute($pageData);

            $pageData = [
                'digitalization_id' => $digitalization->id,
                'imageUrl' => $imageUrl,
                'pageData' => $pageData,
                'plainText' => $plainText,
            ];

            return view('partials.digitalization-page', compact('pageData'))->render();
        }

        if (!Gate::authorize('view', $digitalizationBatch)) {
            abort(403, 'Unauthorized');
        };

        $pages = (new MountPagesDataFromDigitalizationBatchAction)->execute($digitalizationBatch);
        $processedPages = $digitalizationBatch->digitalizations()->whereNotNull('transcription_file_path')->count();
        $totalFiles = $digitalizationBatch->pages_count ?? 0;
        $isComplete = $totalFiles > 0 && $processedPages === $totalFiles;
        $channelIdentifier = $digitalizationBatch->folder_path;
        return view('scan-result', compact('pages', 'digitalizationBatch', 'isComplete', 'totalFiles', 'channelIdentifier'));
    }

    public function destroy(DigitalizationBatch $digitalizationBatch)
    {
        if (!auth()->user()->can('destroy', $digitalizationBatch)) {
            abort(403, 'Unauthorized');
        }

        try {
            Storage::disk('public')->deleteDirectory(DigitalizationBatch::DIGITALIZATION_DIR . $digitalizationBatch->folder_path);

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
