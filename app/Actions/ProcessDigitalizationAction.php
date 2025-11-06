<?php

namespace App\Actions;


use App\Events\PageProcessedEvent;
use App\Factories\DigitalizesFactory;
use App\Models\DigitalizationBatch;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use function array_map;
use function array_merge;
use function array_unique;
use function basename;
use function dirname;
use function is_array;
use function is_string;
use function json_encode;
use function microtime;
use function pathinfo;
use function redirect;
use function round;
use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_UNICODE;
use const PATHINFO_EXTENSION;

class ProcessDigitalizationAction
{

    public function execute(array $filePaths, DigitalizationBatch $batch, string $folderUniqueId, ?int $userId)
    {

        $filesTempUpload = $this->expandAndFilterFiles($filePaths);
        $pageCount = count($filesTempUpload);
        Log::info('quantidade de paginas total: ' . $pageCount);
        $batch->pages_count = $pageCount;
        $batch->update();
        foreach ($filesTempUpload as $index => $file) {
            $digitalizationId = $this->processFile($file, $batch, $folderUniqueId, $userId);

            event(new PageProcessedEvent(
                batchId: $batch->id,
                processedCount: $index + 1,
                totalImages: $pageCount,
                pageId: $digitalizationId,
            ));


        }
        $tempFolderNames = array_map(function ($file) {
            if (is_string($file)) {
                return basename($file);
            };
            $absoluteDirPath = $file->getPath();
            return basename($absoluteDirPath);
        }, $filesTempUpload);

        $tempFolderNames = array_unique($tempFolderNames);
        $this->deleteTemporaryFiles($filePaths, $tempFolderNames);

    }


    private function verifyIfIsPDF(string $filePath)
    {
        $mimeType = Storage::disk('local')->mimeType($filePath);
        return $mimeType === 'application/pdf';
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

    private function processFile(string $filePath, DigitalizationBatch $batch, string $folderId, ?int $userId)
    {
        try {
            $start = microtime(true);
            $digitalizer = DigitalizesFactory::make();
            $parsed = $digitalizer->returnJson($filePath);
            $this->logProcessingTime($filePath, $start);

            if ($parsed instanceof \Illuminate\Http\RedirectResponse) {
                redirect()->back();
            }

            $digitalizationId = $this->storeResults($filePath, $parsed, $batch, $folderId, $userId);
            return $digitalizationId;
        } catch (Exception $e) {
            Log::error("Erro ao processar arquivo {$filePath}: " . $e->getMessage());
        }
    }

    private function storeResults(string $filePath, $jsonData, DigitalizationBatch $batch, string $folderId, ?int $userId)
    {
        $fileHashName = Str::random(40);

        $fileNameWithExtension = basename($filePath);
        $extension = pathinfo($fileNameWithExtension, PATHINFO_EXTENSION);
        $folderPath = DigitalizationBatch::DIGITALIZATION_DIR . $folderId;
        $json = json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $jsonName = "{$fileHashName}.json";

        Storage::disk('public')->put("{$folderPath}/json_outputs/{$jsonName}", $json);

        $newFileName = "{$fileHashName}.{$extension}";
        $destinationPath = "{$folderPath}/original_files/{$newFileName}";

        Storage::disk('public')->put($destinationPath, Storage::disk('local')->get($filePath));


        $digitalization = $batch->digitalizations()->create([
            'original_file_path' => $destinationPath,
            'transcription_file_path' => "{$folderPath}/json_outputs/{$jsonName}",
            'user_id' => $userId,
        ]);
        return $digitalization->id;
    }

    private function deleteTemporaryFiles(array $filePaths, array $filesTempUpload): void
    {
        foreach ($filePaths as $filePath) {
            try {

                Storage::disk('local')->deleteDirectory(dirname($filePath));
                Log::info("Pasta temporária deletada: {$filePath}");
            } catch (Exception $e) {
                Log::error("Erro ao deletar arquivo {$e->getMessage()}");
            }
        }
        foreach ($filesTempUpload as $fileTempUpload) {
            try {
                Storage::disk('local')->deleteDirectory('temp/' . $fileTempUpload);
                Log::info("Pasta temporária deletada: {$fileTempUpload}");
            } catch (Exception $e) {
                Log::error("Erro ao deletar arquivo {$e->getMessage()}");
            }
        };
    }

    private function logProcessingTime(string $file, float $start): void
    {
        $duration = round(microtime(true) - $start, 4);
        Log::info("Tempo para processar {$file}: {$duration} segundos");
    }
}
