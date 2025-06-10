<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Digitalized Document - Page {{ $pageData['pageNumber'] ?? '' }}</title>
    <style>
        /* Estilos CSS para o PDF */
        body {
            font-family: 'Courier New', Courier, monospace;
            white-space: pre-wrap;
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

@foreach($pages as $page)

    @if(!empty($page['pageData']['headerTitle']))
        <h1>{!! $page['pageData']['headerTitle'] !!}</h1>
    @endif
    @if(!empty($page['pageData']['title']))
        <h2>{!! $page['pageData']['title'] !!}</h2>
    @endif
    @if(!empty($page['pageData']['subtitle']))
        <h3>{!! $page['pageData']['subtitle'] !!}</h3>
    @endif

    @if(isset($page['pageData']['paragraphs']) && is_array($page['pageData']['paragraphs']))
        @foreach($page['pageData']['paragraphs'] as $paragraph)
            <p>{!! nl2br(e(str_replace(["\\t"], "\t", $paragraph))) !!}</p>
        @endforeach
    @endif

    @if(!empty($page['pageData']['pageNumber']))
        <div class="page-number">Page: {!! $page['pageData']['pageNumber'] !!}</div>
    @endif
@endforeach
</body>
</html>
