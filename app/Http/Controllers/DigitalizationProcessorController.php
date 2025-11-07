<?php

namespace App\Http\Controllers;

use App\Actions\ProcessDigitalizationAction;
use App\Factories\DigitalizesFactory;
use App\Http\Requests\DigitalizerRequest;
use App\Jobs\ProcessDigitalizationJob;
use App\Models\Digitalization;
use App\Models\DigitalizationBatch;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use function abort;
use function auth;
use function back;
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
        if ($this->redirectOnNullPath($filePaths)) {
            return redirect()->route('index')->withErrors('Wait for the whole file to be uploaded before submitting');
        };
        $folderUniqueId = $this->generateFolderUniqueId();
        $userId = auth()->id();
        $belongsToUser = auth()->check();
        $batch = $this->createBatch($filePaths, $folderUniqueId, $userId, $belongsToUser);

        (new ProcessDigitalizationAction)->execute($filePaths, $batch, $folderUniqueId, $userId);

        return redirect()->route('digitalize.show', ['digitalizationBatchHash' => $batch->folder_path]);
    }

    public function digitalizesJob(DigitalizerRequest $request)
    {
        $filePaths = $request->validated()['file'];
        if ($this->redirectOnNullPath($filePaths)) {
            return redirect()->route('index')->withErrors('Wait for the whole file to be uploaded before submitting');
        };

        $folderUniqueId = $this->generateFolderUniqueId();
        $userId = auth()->id();
        $belongsToUser = auth()->check();
        $batch = $this->createBatch($filePaths, $folderUniqueId, $userId, $belongsToUser);
        ProcessDigitalizationJob::dispatch($filePaths, $batch, $folderUniqueId, $userId);

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


    private function logProcessingTime(string $file, float $start): void
    {
        $duration = round(microtime(true) - $start, 4);
        Log::info("Tempo para processar {$file}: {$duration} segundos");
    }

    private function redirectOnNullPath(array $filePaths)
    {
        foreach ($filePaths as $filePath) {
            if ($filePath === null || empty($filePath)) {
                return true;
            }
        }
        return false;
    }
}
