<?php

namespace App\Jobs;

use App\Actions\ProcessDigitalizationAction;
use App\Models\DigitalizationBatch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessDigitalizationJob implements ShouldQueue
{
    use Queueable;

    public array $filePaths;
    public DigitalizationBatch $batch;
    public string $folderUniqueId;
    public ?int $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(array $filePaths, DigitalizationBatch $batch, string $folderUniqueId, ?int $userId)
    {
        $this->filePaths = $filePaths;
        $this->batch = $batch;
        $this->folderUniqueId = $folderUniqueId;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        (new ProcessDigitalizationAction)->execute($this->filePaths, $this->batch, $this->folderUniqueId, $this->userId);
    }

}
