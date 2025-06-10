<?php

namespace App\Actions;


use App\Models\DigitalizationBatch;
use Illuminate\Support\Facades\Storage;
use function is_array;
use function json_decode;
use function str_replace;
use function trim;

class MountPagesDataFromDigitalizationBatch
{
    public function execute(DigitalizationBatch $digitalizationBatch)
    {
        foreach ($digitalizationBatch->digitalizations->sortByDesc('id') as $digitalization) {
            $imageUrl = Storage::url($digitalization->original_file_path);
            $transcriptionPath = $digitalization->transcription_file_path;
            $jsonContent = Storage::disk('public')->get($transcriptionPath);
            $pageData = json_decode($jsonContent, true)['page'];

            $plainText = (new ExtractPlaintTextFromJsonAction)->execute($pageData);

            $pages[] = [
                'imageUrl' => $imageUrl,
                'pageData' => $pageData,
                'plainText' => $plainText,
            ];
        }

        return $pages;
    }
}
