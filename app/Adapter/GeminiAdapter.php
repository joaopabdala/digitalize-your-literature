<?php

namespace App\Adapter;

use App\Actions\ExtractPlaintTextFromJsonAction;
use App\Interfaces\DigitalizesInterface;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Log;
use function back;
use function json_decode;
use function str_replace;

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

        if ($response instanceof \Illuminate\Http\JsonResponse) {
            $response = $response->getData(true);
        }

        $text = data_get($response, 'candidates.0.content.parts.0.text');
        if (!$text) {
            Log::warning('A resposta do Gemini não contém a estrutura de texto esperada.', ['response' => $response]);

            return back()->with(['message' => 'A resposta do Gemini não veio no formato esperado.', 'type' => 'error']);
        }

        $jsonString = str_replace(['```json', '```', "\n"], '', $text);

        $parsedContent = json_decode($jsonString, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Erro ao decodificar JSON da resposta do Gemini: ' . json_last_error_msg() . ' - String JSON: ' . $jsonString);
            return back()->with(['message' => 'Erro ao interpretar a resposta do serviço.', 'type' => 'error']);
        }

        return $parsedContent;
    }

    public function formatJsonToHTMLandPlainText($parsedContent)
    {
        if (isset($parsedContent['page'])) {
            $pageData = $parsedContent['page'];

            $plainText = (new ExtractPlaintTextFromJsonAction)->execute($pageData);

            return ['plainText' => $plainText, 'pageData' => $pageData];
        }
        Log::warning('A chave "page" não foi encontrada no JSON decodificado.');
        return back()->with(['message' => 'A estrutura de dados retornada não é a esperada.', 'type' => 'error']);

    }
}
