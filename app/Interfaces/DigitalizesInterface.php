<?php

namespace App\Interfaces;

interface DigitalizesInterface
{
    public function returnJson($file);

    public function formatJsonToHTMLandPlainText($parsedContent);
}
