<?php

namespace App\Http\Controllers;

use App\Factories\DigitalizesFactory;
use App\Http\Requests\DeleteDigitalizationRequest;
use App\Http\Requests\DigitalizerRequest;
use App\Models\Digitalization;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use function abort;
use function auth;
use function back;
use function compact;
use function file_get_contents;
use function json_decode;
use function json_encode;
use function json_last_error;
use function json_last_error_msg;
use function pathinfo;
use function redirect;
use function str_replace;
use function unlink;
use function view;
use const JSON_ERROR_NONE;
use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_UNICODE;
use const PATHINFO_FILENAME;

class DigitalizerController extends Controller
{
    public function digitalizes(DigitalizerRequest $request)
    {
        $file = $request->file('file');

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
            $user = auth()->user();
            $originalFilePath = $file->storeAs('digitalizations/original_file', $file->hashName(), 'public');

            $jsonOutputString = json_encode($parsedContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $jsonFileName = $file->hashName() . '.json';
            $transcriptionFilePath = 'digitalizations/json_outputs/' . $jsonFileName;
            Storage::disk('public')->put($transcriptionFilePath, $jsonOutputString);


            $digitalization = $user->digitalizations()->create([
                'original_file_path' => $originalFilePath,
                'transcription_file_path' => $transcriptionFilePath,
                'title' => $pageData['title'] ?? 'undefined title',
            ]);
            Storage::disk('public')->delete($tempDisplayPath);
            $imageUrl = Storage::url($originalFilePath);
        }

        return view('scan-result', compact('pageData', 'plainText', 'imageUrl', 'digitalization'));
    }

    public function downloadPDF(Digitalization $digitalization)
    {
        if (!auth()->user()->can('download', $digitalization)) {
            abort(403, 'Unauthorized');
        }

        try {
            $transcriptionFilePath = $digitalization->transcription_file_path;

            if (!Storage::exists($transcriptionFilePath)) {
                Log::warning('Arquivo de transcrição JSON não encontrado para PDF: ' . $transcriptionFilePath);
                abort(404, 'PDF não pode ser gerado: Arquivo de transcrição não encontrado.');
            }
            $jsonContent = Storage::get($transcriptionFilePath);

            $parsedContent = json_decode($jsonContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Erro ao decodificar JSON do arquivo de transcrição para PDF: ' . json_last_error_msg());
                abort(500, 'PDF não pode ser gerado: Erro de leitura dos dados.');
            }

            $pageData = $parsedContent['page'] ?? [];

            $html = view('pdf.document_template', compact('pageData'))->render();

            $pdf = Pdf::loadHtml($html);

            $baseFileName = pathinfo($transcriptionFilePath, PATHINFO_FILENAME);
            $pdfFileName = str_replace('gemini_response_', 'documento_', $baseFileName) . '.pdf';


            return $pdf->download($pdfFileName);

        } catch (Exception $e) {
            Log::error('Erro ao gerar PDF: ' . $e->getMessage());
            return back()->withErrors(['pdf_error' => 'Não foi possível gerar o PDF: ' . $e->getMessage()]);
        }
    }

    public function destroy(Digitalization $digitalization)
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
