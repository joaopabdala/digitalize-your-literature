<?php

namespace App\Interfaces;

interface DigitalizesInterface
{
    public function returnJson(string $file);

    public function formatJsonToHTMLandPlainText($parsedContent);
}
