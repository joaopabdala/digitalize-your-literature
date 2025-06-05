<?php

namespace App\Actions;


use function is_array;
use function str_replace;
use function trim;

class ExtractPlaintTextFromJsonAction
{
    public function execute($json)
    {
        $plainText = '';
        if (!empty($json['headerTitle'])) $plainText .= $json['headerTitle'] . "\n\n";
        if (!empty($json['title'])) $plainText .= $json['title'] . "\n";
        if (!empty($json['subtitle'])) $plainText .= $json['subtitle'] . "\n\n";
        if (!empty($json['paragraphs']) && is_array($json['paragraphs'])) {
            foreach ($json['paragraphs'] as $paragraph) {
                $plainText .= str_replace('\t', "\t", $paragraph) . "\n\n";
            }
        }
        if (!empty($json['pageNumber'])) $plainText .= "Página: " . $json['pageNumber'] . "\n";
        $plainText = trim($plainText);

        return $plainText;
    }
}
