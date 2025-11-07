<?php

namespace App\Actions;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Imagick;

class ConvertPDFtoImageAction
{
    private const DPI = 150;
    private const COMPRESSION_QUALITY = 30;
    private const MAX_WIDTH = 2000;
    private const OUTPUT_FORMAT = 'jpg';

    public function execute(string $filePath): array
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 0);
        $absolutePath = Storage::disk('local')->path($filePath);

        $tempDirRelative = 'temp/pdf_images_' . Str::random(8);
        Storage::disk('local')->makeDirectory($tempDirRelative);

        $tempDirPath = Storage::disk('local')->path($tempDirRelative);

        $pdfPath = $absolutePath;
        $fileSize = Storage::disk('local')->size($filePath);

        Log::info("Processing PDF: {$pdfPath} ({$fileSize} bytes)");

        $imagick = new Imagick();
        $imagick->pingImage($pdfPath);
        $pageCount = $imagick->getNumberImages();
        $imagick->clear();
        $imagick->destroy();

        Log::info("PDF has {$pageCount} pages");

        $uploadedFiles = [];

        for ($pageNum = 0; $pageNum < $pageCount; $pageNum++) {
            try {
                $imagick = new Imagick();

                $imagick->setResolution(self::DPI, self::DPI);
                $imagick->setColorspace(Imagick::COLORSPACE_RGB);

                $imagick->readImage("{$pdfPath}[{$pageNum}]");


                $imagick->setImageBackgroundColor('white');
                $imagick->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);

                if ($imagick->getImageColorspace() !== Imagick::COLORSPACE_RGB) {
                    $imagick->transformImageColorspace(Imagick::COLORSPACE_RGB);
                }

                $imagick = $imagick->flattenImages();

                $imagick->setImageFormat(self::OUTPUT_FORMAT);

                $width = $imagick->getImageWidth();
                $height = $imagick->getImageHeight();


                if ($width > self::MAX_WIDTH) {
                    $newHeight = (int)(($height / $width) * self::MAX_WIDTH);
                    $imagick->resizeImage(
                        self::MAX_WIDTH,
                        $newHeight,
                        Imagick::FILTER_LANCZOS,
                        1
                    );
                }

                $imagick->stripImage();
                $imagick->setImageCompression(Imagick::COMPRESSION_JPEG);
                $imagick->setImageCompressionQuality(self::COMPRESSION_QUALITY);

                $imagick->normalizeImage();

                $filename = "page_{$pageNum}." . self::OUTPUT_FORMAT;
                $filepath = $tempDirPath . DIRECTORY_SEPARATOR . $filename;

                $imagick->writeImage($filepath);

                $fileSize = filesize($filepath);
                Log::info("Created {$filename} ({$fileSize} bytes)");

                if ($fileSize < 1000) {
                    Log::warning("Image file is suspiciously small!");
                }

                $uploadedFiles[] = $tempDirRelative . DIRECTORY_SEPARATOR . $filename;

                $imagick->clear();
                $imagick->destroy();

                gc_collect_cycles();

            } catch (Exception $e) {
                Log::error("Error processing page {$pageNum}: " . $e->getMessage());
                Log::error($e->getTraceAsString());
                throw $e;
            }
        }
        return $uploadedFiles;
    }

}
