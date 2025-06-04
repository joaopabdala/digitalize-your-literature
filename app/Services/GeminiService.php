<?php

namespace App\Services;

use Exception;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use function base64_decode;
use function base64_encode;
use function config;
use function report;
use function response;

class GeminiService
{


    private $httpClient;
    private $endpoint;

    public function __construct()
    {
        $apiKey = config('ai-service.ai_api_key');
        $this->httpClient = Http::withHeaders([
            'Content-Type' => 'application/json',
        ]);

        $this->endpoint = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}";

    }

    public function returnJson(UploadedFile $file)
    {
        $imageData = base64_encode(file_get_contents($file));
        try {
            $response = $this->httpClient->post($this->endpoint, [
                'contents' => [[
                    'parts' => [
                        [
                        'text' => 'Transcreva o conteúdo da imagem mantendo a formatação. A imagem é uma página de livro ou de uma revista. Foque somente na página principal, ignore o conteúdo das bordas. O conteúdo deve ser retornado em json classificando o título, subtítulo, o número da página, qualquer header com o título do livro/capítulo. O conteúdo principal deve ser retornado por parágrafos, mantendo ao máximo a estrutura original do texto, mas as quebras de linha originais não precisam ser especificadas como quebras, o importante é saber o início e fim dos parágrafos, "\n" e "\t" não devem ser retornados. No caso de tabulações no formato de entrevistas separar em outro parágrafo. Caso alguma dessas informações esteja faltando deve ser retornado null. Caso a imagem tenha mais de uma página o conteúdo deve retornar várias páginas. Exemplo de construção do json:
                         {
                            "page" : {
                                "headerTitle": null
                                "title": null,
                                "subtitle": null,
                                "paragraphs" : [
                                    "parágrafo1",
                                    "parágrafo2",
                                    "parágrafo3",
                                ]
                                "pageNumber": null
                            }
                         }'
                        ],
                        [
                            'inline_data' => [
                                'mime_type' => $file->getMimeType(),
                                'data' => $imageData,
                            ]
                        ]
                    ]
                ]]
            ]);

            if ($response->successful()) {
                return $response->json();
            } else {
                throw new Exception("Gemini API Error: " . $response->status() . " - " . $response->body());
            }
        } catch (Exception $e) {
             report($e);
             Log::error($e->getMessage());
             return response()->json('Error fetching API', 500);
        }
    }

}

