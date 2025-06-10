<?php

namespace App\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
                            'text' => 'Transcreva o conteúdo textual da imagem. A imagem representa uma página de livro ou revista.
Concentre-se **exclusivamente no conteúdo principal da página**, ignorando elementos das margens, rodapés ou cabeçalhos que não sejam o título do livro/capítulo e imagens.

O resultado deve ser um objeto JSON formatado rigorosamente conforme o exemplo fornecido.
Para cada página detectada, o JSON deve conter um objeto "page" com as seguintes chaves:

- **"headerTitle"**: (string ou null) O título do livro, capítulo ou seção que aparece no cabeçalho da página (se existir).
- **"title"**: (string ou null) O título principal da página (se houver).
- **"subtitle"**: (string ou null) O subtítulo da página (se houver).
- **"pageNumber"**: (inteiro ou null) O número da página (se detectável).
- **"paragraphs"**: (array de strings) Uma lista de parágrafos. Cada string no array deve representar um parágrafo completo.

**Instruções detalhadas para "paragraphs":**

1.  **Preservação da Estrutura**: Mantenha a estrutura original do texto o máximo possível, respeitando a separação lógica dos parágrafos.
2.  **Remoção de Caracteres Especiais**: **Remova estritamente** quaisquer caracteres de nova linha (`\n`) e tabulação (`\t`) do texto dos parágrafos. Eles não devem aparecer no JSON final. Esse tipo de será lidada na separação por parágrafos.
3.  **Diálogos/Entrevistas**: Se houver diálogos ou entrevistas com nomes de falantes (ex: "Nome do Falante: Texto"), o nome do falante deve ser tratado como um novo parágrafo.
4.  **Conteúdo Faltante**: Se alguma das informações solicitadas (título, subtítulo, etc.) não for encontrada na imagem, seu valor correspondente no JSON deve ser **null**.

**Estrutura do JSON (obrigatório):**

Se a imagem contiver múltiplas páginas, o JSON deve ser um array de objetos "page". Se contiver apenas uma página, será um único objeto "page".

**Exemplo de JSON esperado:**

```json
  {
    "page": {
      "headerTitle": "Introdução à Inteligência Artificial",
      "title": "A Evolução das Máquinas Pensantes",
      "subtitle": "Desde a Lógica Simbólica à Aprendizagem Profunda",
      "paragraphs": [
        "A inteligência artificial (IA) tem sido um campo de estudo fascinante e em constante evolução, buscando replicar e aprimorar capacidades cognitivas humanas em sistemas computacionais.",
        "Desde os primórdios da lógica simbólica, que tentava codificar o conhecimento de forma explícita, até as abordagens modernas de aprendizado de máquina, que permitem aos sistemas aprender com dados, a IA tem percorrido um longo caminho.",
        "O impacto da IA é vasto e crescente, influenciando setores como saúde, finanças e transporte, e prometendo transformar radicalmente a forma como interagimos com a tecnologia."
      ],
      "pageNumber": 15
    }, {
     "headerTitle": "Introdução à Inteligência Artificial",
      "title": null,
      "subtitle": "Desafios Atuais e Futuros",
      "paragraphs": [
        "Apesar dos avanços notáveis, a IA enfrenta desafios significativos, como a interpretabilidade de modelos complexos e a ética no uso de algoritmos preditivos.",
        "A garantia de que os sistemas de IA sejam justos, transparentes e responsáveis é crucial para a sua aceitação e integração na sociedade.",
        "Pesquisas futuras se concentram em áreas como a IA explicável (XAI) e o aprendizado federado, buscando superar essas barreiras e expandir as fronteiras do que é possível."
      ],
      "pageNumber": 16
  },
'
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

