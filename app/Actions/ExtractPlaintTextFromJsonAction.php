<?php

namespace App\Actions;


use function is_array;
use function str_replace;
use function trim;

class ExtractPlaintTextFromJsonAction
{
    public function execute($pages)
    {
        $plainText = '';

        foreach ($pages as $page) {

            if (!empty($page['headerTitle'])) $plainText .= $page['headerTitle'] . "\n\n";
            if (!empty($page['title'])) $plainText .= $page['title'] . "\n";
            if (!empty($page['subtitle'])) $plainText .= $page['subtitle'] . "\n\n";
            if (!empty($page['paragraphs']) && is_array($page['paragraphs'])) {
                foreach ($page['paragraphs'] as $paragraph) {
                    $plainText .= str_replace('\t', "\t", $paragraph) . "\n\n";
                }
            }
            if (!empty($page['pageNumber'])) $plainText .= "Página: " . $page['pageNumber'] . "\n";
        }
        $plainText = trim($plainText);
        return $plainText;
    }
}
