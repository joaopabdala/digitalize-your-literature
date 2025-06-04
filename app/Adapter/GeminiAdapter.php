<?php

namespace App\Adapter;

use App\Interfaces\DigitalizesInterface;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Log;
use function back;
use function is_array;
use function json_decode;
use function str_replace;
use function trim;

class GeminiAdapter implements DigitalizesInterface
{
    public $service;
    public function __construct()
    {
        $this->service = (new GeminiService);
    }
    public function returnJson($file)
    {
        $response = $this->service->returnJson($file);

        if(isset($response['candidates'][0]['content']['parts'][0]['text'])){
            $rawGeminiOutputText = $response['candidates'][0]['content']['parts'][0]['text'];

            $jsonString = str_replace(['```json', '```', "\n"], '', $rawGeminiOutputText);

            $parsedContent = json_decode($jsonString, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Erro ao decodificar JSON da resposta do Gemini: ' . json_last_error_msg() . ' - String JSON: ' . $jsonString);
                return back()->with(['message' => 'Erro ao interpretar a resposta do serviço.', 'type' => 'error']);
            }

            return $parsedContent;
        }

        Log::warning('A resposta do Gemini não contém a estrutura de texto esperada.');
        return back()->withErrors(['response_structure_error' => 'A resposta do Gemini não veio no formato esperado.']);
    }

    public function formatJsonToHTMLanPlainText($parsedContent)
    {
        if (isset($parsedContent['page'])) {
            $pageData = $parsedContent['page'];


            $plainText = '';
            if (!empty($pageData['headerTitle'])) $plainText .= $pageData['headerTitle'] . "\n\n";
            if (!empty($pageData['title'])) $plainText .= $pageData['title'] . "\n";
            if (!empty($pageData['subtitle'])) $plainText .= $pageData['subtitle'] . "\n\n";
            if (!empty($pageData['paragraphs']) && is_array($pageData['paragraphs'])) {
                foreach ($pageData['paragraphs'] as $paragraph) {
                    $plainText .= str_replace('\t', "\t", $paragraph) . "\n\n";
                }
            }
            if (!empty($pageData['pageNumber'])) $plainText .= "Página: " . $pageData['pageNumber'] . "\n";
            $plainText = trim($plainText);

            return ['plainText' => $plainText, 'pageData' => $pageData];
        }
        Log::warning('A chave "page" não foi encontrada no JSON decodificado.');
        return back()->with(['message' => 'A estrutura de dados retornada não é a esperada.', 'type' => 'error']);

    }
}
