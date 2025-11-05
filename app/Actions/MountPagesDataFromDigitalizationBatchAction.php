<?php

namespace App\Actions;


use App\Exceptions\InvalidPageDataException;
use App\Models\DigitalizationBatch;
use Illuminate\Support\Facades\Storage;
use function is_null;
use function json_decode;

class MountPagesDataFromDigitalizationBatchAction
{
    public function execute(DigitalizationBatch $digitalizationBatch, int $perPage = 5)
    {
        $digitalizations = $digitalizationBatch->digitalizations()->paginate($perPage);
        $pages = $digitalizations->through(function ($digitalization) {
            $imageUrl = Storage::url($digitalization->original_file_path);
            $transcriptionPath = $digitalization->transcription_file_path;
            $jsonContent = Storage::disk('public')->get($transcriptionPath);
            $jsonDecoded = json_decode($jsonContent, true);

            $pageData = data_get($jsonDecoded, 'page');
            if (is_null($pageData)) {
                throw new InvalidPageDataException('Os dados da página estão ausentes ou inválidos.');
            }

            $plainText = (new ExtractPlaintTextFromJsonAction)->execute($pageData);

            return [
                'digitalization_id' => $digitalization->id,
                'imageUrl' => $imageUrl,
                'pageData' => $pageData,
                'plainText' => $plainText,
            ];
        });

        return $pages;
    }
}
