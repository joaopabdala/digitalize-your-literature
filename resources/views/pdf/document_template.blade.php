<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Documento Digitalizado - Página {{ $pageData['pageNumber'] ?? '' }}</title>
    <style>
        /* Estilos CSS para o PDF */
        body {
            font-family: 'DejaVu Sans', sans-serif; /* Uma fonte que suporte caracteres UTF-8 */
            margin: 2cm;
            font-size: 10pt;
        }
        h1 { font-size: 20pt; text-align: center; margin-bottom: 0.5em; color: #333; }
        h2 { font-size: 16pt; text-align: center; margin-bottom: 0.3em; color: #444; }
        h3 { font-size: 12pt; text-align: center; color: #666; margin-bottom: 1em; }
        p {
            font-size: 10pt;
            line-height: 1.5;
            margin-bottom: 0.8em;
            text-align: justify;
            /* Para preservar tabulações e quebras de linha que vêm do JSON */
            white-space: pre-wrap;
        }
        .page-number { font-size: 8pt; text-align: right; margin-top: 2em; color: #999; }
    </style>
</head>
<body>
@if(!empty($pageData['headerTitle']))
    <h1>{{ htmlspecialchars($pageData['headerTitle']) }}</h1>
@endif
@if(!empty($pageData['title']))
    <h2>{{ htmlspecialchars($pageData['title']) }}</h2>
@endif
@if(!empty($pageData['subtitle']))
    <h3>{{ htmlspecialchars($pageData['subtitle']) }}</h3>
@endif

@if(isset($pageData['paragraphs']) && is_array($pageData['paragraphs']))
    @foreach($pageData['paragraphs'] as $paragraph)
        {{-- Aqui usamos htmlspecialchars para segurança, pois o texto vem do JSON. --}}
        {{-- str_replace('\t', "\t", ...) é para garantir que tabulações sejam tabulações reais no texto,
             e white-space: pre-wrap no CSS cuida da renderização. --}}
        <p>{{ htmlspecialchars(str_replace('\t', "\t", $paragraph)) }}</p>
    @endforeach
@endif

@if(!empty($pageData['pageNumber']))
    <div class="page-number">Página: {{ htmlspecialchars($pageData['pageNumber']) }}</div>
@endif
</body>
</html>
