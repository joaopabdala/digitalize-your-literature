<?php

namespace App\Interfaces;

interface DigitalizesInterface
{
    public function returnJson(string $filePath);

    public function formatJsonToHTMLandPlainText($parsedContent);
}
