<?php

namespace App\Actions;


use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Imagick;
use function is_array;
use function str_replace;
use function trim;

class ConvertPDFtoImageAction
{
    public function execute(UploadedFile $file): array
    {
        $tempDir = storage_path('app/temp/pdf_images_' . Str::random(8));
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $imagick = new Imagick();
        $imagick->setResolution(150, 150);
        $imagick->readImage($file->getRealPath());

        $uploadedFiles = [];

        foreach ($imagick as $index => $page) {
            $page->setImageFormat('png');
            $filename = "page_{$index}.png";
            $filepath = $tempDir . DIRECTORY_SEPARATOR . $filename;

            // Salva a imagem temporariamente no disco
            $page->writeImage($filepath);

            // Cria uma instância de UploadedFile
            $uploadedFiles[] = new UploadedFile(
                $filepath,
                $filename,
                'image/png',
                null,
                true // $testMode para evitar erros no Laravel
            );
        }

        $imagick->clear();
        $imagick->destroy();

        return $uploadedFiles;
    }
}
