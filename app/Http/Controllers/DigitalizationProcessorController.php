<?php

namespace App\Http\Controllers;

use App\Actions\ConvertPDFtoImageAction;
use App\Factories\DigitalizesFactory;
use App\Http\Requests\DigitalizerRequest;
use App\Models\Digitalization;
use App\Models\DigitalizationBatch;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use function abort;
use function array_merge;
use function auth;
use function back;
use function basename;
use function dirname;
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
        $filePaths = $request->validated()['file'];
        $folderUniqueId = $this->generateFolderUniqueId();
        $userId = auth()->id();
        $belongsToUser = auth()->check();
        $batch = $this->createBatch($filePaths, $folderUniqueId, $userId, $belongsToUser);
        $filesTempUpload = $this->expandAndFilterFiles($filePaths);
        foreach ($filesTempUpload as $file) {
            $this->processFile($file, $batch, $folderUniqueId, $userId);
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

        return redirect()->route('digitalize.show', ['digitalizationBatchHash' => $batch->folder_path]);
    }

    public function reDigitalize(Digitalization $digitalization)
    {

        if (!auth()->user()->can('view', $digitalization)) {
            abort(403, 'Unauthorized');
        }
        $imagePath = $digitalization->original_file_path;
        $jsonPath = $digitalization->transcription_file_path;
        try {
            $start = microtime(true);
            $digitalizer = DigitalizesFactory::make();
            $jsonData = $digitalizer->returnJson($imagePath, 'public');
            $this->logProcessingTime($imagePath, $start);
            if ($jsonData instanceof \Illuminate\Http\RedirectResponse) {
                redirect()->back();
            }
            $json = json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            Storage::disk('public')->put($jsonPath, $json);
            Log::info("{$jsonPath} atualizado");
        } catch (Exception $e) {
            Log::error("Erro ao processar arquivo {$imagePath}: " . $e->getMessage());
            return back()->withErrors('Erro ao processar arquivo ' . $imagePath . ': ' . $e->getMessage());
        }

        return back()->with(['success' => 'Arquivo atualizado com sucesso']);
    }


    private function verifyIfIsPDF(string $filePath)
    {
        $mimeType = Storage::disk('local')->mimeType($filePath);
        return $mimeType === 'application/pdf';
    }

    private function generateFolderUniqueId(): string
    {
        return now()->format('Ymd_His') . '_' . uniqid();
    }

    private function createBatch(array $files, string $folderPath, ?int $userId, bool $belongsToUser)
    {
        return DigitalizationBatch::create([
            'title' => pathinfo($files[0], PATHINFO_FILENAME) ?? 'undefined title',
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

    private function processFile(string $filePath, DigitalizationBatch $batch, string $folderId, ?int $userId): void
    {
        try {
            $start = microtime(true);
            $digitalizer = DigitalizesFactory::make();
            $parsed = $digitalizer->returnJson($filePath);
            $this->logProcessingTime($filePath, $start);

            if ($parsed instanceof \Illuminate\Http\RedirectResponse) {
                redirect()->back();
            }

            $this->storeResults($filePath, $parsed, $batch, $folderId, $userId);
        } catch (Exception $e) {
            Log::error("Erro ao processar arquivo {$filePath}: " . $e->getMessage());
        }
    }

    private function storeResults(string $filePath, $jsonData, DigitalizationBatch $batch, string $folderId, ?int $userId): void
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


        $batch->digitalizations()->create([
            'original_file_path' => $destinationPath,
            'transcription_file_path' => "{$folderPath}/json_outputs/{$jsonName}",
            'user_id' => $userId,
        ]);
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
