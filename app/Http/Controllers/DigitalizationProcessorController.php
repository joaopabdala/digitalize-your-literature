<?php

namespace App\Http\Controllers;

use App\Actions\ConvertPDFtoImageAction;
use App\Factories\DigitalizesFactory;
use App\Http\Requests\DigitalizerRequest;
use App\Models\DigitalizationBatch;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use function array_merge;
use function auth;
use function is_array;
use function json_encode;
use function microtime;
use function now;
use function redirect;
use function round;
use function uniqid;
use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_UNICODE;

class DigitalizationProcessorController extends Controller
{
    public function digitalizes(DigitalizerRequest $request)
    {
        $files = $request->validated()['file'];
        $folderUniqueId = $this->generateFolderUniqueId();
        $userId = auth()->id();
        $belongsToUser = auth()->check();

        $batch = $this->createBatch($files, $folderUniqueId, $userId, $belongsToUser);
        $files = $this->expandAndFilterFiles($files);

        foreach ($files as $file) {
            $this->processFile($file, $batch, $folderUniqueId, $userId);
        }

        return redirect()->route('digitalize.show', ['digitalizationBatchHash' => $batch->folder_path]);
    }


    private function verifyIfIsPDF(UploadedFile $file)
    {
        $mimeType = $file->getClientMimeType();
        return $mimeType === 'application/pdf';
    }

    private function generateFolderUniqueId(): string
    {
        return now()->format('Ymd_His') . '_' . uniqid();
    }

    private function createBatch(array $files, string $folderPath, ?int $userId, bool $belongsToUser)
    {
        return DigitalizationBatch::create([
            'title' => $files[0]->getClientOriginalName() ?? 'undefined title',
            'folder_path' => $folderPath,
            'user_id' => $userId,
            'belongs_to_user' => $belongsToUser,
        ]);
    }

    private function expandAndFilterFiles(array $files): array
    {
        $extraFiles = [];

        foreach ($files as $key => $file) {
            if ($this->verifyIfIsPDF($file)) {
                $converted = (new ConvertPDFtoImageAction)->execute($file);
                if (is_array($converted)) {
                    $extraFiles = array_merge($extraFiles, $converted);
                    unset($files[$key]);
                }
            }
        }

        return array_merge($files, $extraFiles);
    }

    private function processFile(UploadedFile $file, DigitalizationBatch $batch, string $folderId, ?int $userId): void
    {
        try {
            $start = microtime(true);
            $digitalizer = DigitalizesFactory::make();
            $parsed = $digitalizer->returnJson($file);
            $this->logProcessingTime($file, $start);

            if ($parsed instanceof \Illuminate\Http\RedirectResponse) {
                redirect()->back(); // Ou qualquer redirecionamento que faça sentido
            }

            $this->storeResults($file, $parsed, $batch, $folderId, $userId);
        } catch (Exception $e) {
            Log::error("Erro ao processar arquivo {$file->getClientOriginalName()}: " . $e->getMessage());
        }
    }

    private function storeResults(UploadedFile $file, $jsonData, DigitalizationBatch $batch, string $folderId, ?int $userId): void
    {
        $folderPath = DigitalizationBatch::DIGITALIZATION_DIR . $folderId;
        $json = json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $jsonName = $file->hashName() . '.json';

        Storage::disk('public')->put("{$folderPath}/json_outputs/{$jsonName}", $json);
        $originalPath = $file->storeAs("{$folderPath}/original_files", $file->hashName(), 'public');

        $batch->digitalizations()->create([
            'original_file_path' => $originalPath,
            'transcription_file_path' => "{$folderPath}/json_outputs/{$jsonName}",
            'user_id' => $userId,
        ]);
    }

    private function logProcessingTime(UploadedFile $file, float $start): void
    {
        $duration = round(microtime(true) - $start, 4);
        Log::info("Tempo para processar {$file->getPathname()}: {$duration} segundos");
    }
}
