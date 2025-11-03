<?php

namespace App\Http\Controllers;

use App\Actions\MountPagesDataFromDigitalizationBatchAction;
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

        if (!Gate::authorize('view', $digitalizationBatch)) {
            abort(403, 'Unauthorized');
        };

        $pages = (new MountPagesDataFromDigitalizationBatchAction)->execute($digitalizationBatch);


        return view('scan-result', compact('pages', 'digitalizationBatch'));
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
