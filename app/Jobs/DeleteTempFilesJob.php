<?php

namespace App\Jobs;

use App\Models\DigitalizationBatch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Storage;

class DeleteTempFilesJob implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (Storage::disk('public')->exists('temp_digitalizations')) {
            Storage::disk('public')->deleteDirectory('temp_digitalizations');
        }

        DigitalizationBatch::whereNull('user_id')->chunkById(100,function ($digitalizationBatches) {
            foreach ($digitalizationBatches as $digitalizationBatch) {
                Storage::disk('public')->deleteDirectory($digitalizationBatch->folder_path);
                $digitalizationBatch->delete();
            }
        });

    }
}
