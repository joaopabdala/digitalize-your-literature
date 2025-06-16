<?php

namespace App\Factories;

use App\Adapter\GeminiAdapter;
use function config;

class DigitalizesFactory
{
    public static function make()
    {
        $provider = config('ocr-service.provider');

        return match ($provider) {
            'gemini' => new GeminiAdapter(),
            default => throw new \Exception("Provider '{$provider}' not supported")
        };
    }
}
