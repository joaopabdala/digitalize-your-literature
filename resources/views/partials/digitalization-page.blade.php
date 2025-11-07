<div data-page-content class="border-b-2 pb-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

        <div class="md:col-start-1">
            <h3 class="text-xl font-semibold mb-4 text-gray-700">Scanned Image</h3>
            @if(isset($pageData['imageUrl']))
                <img src="{{ $pageData['imageUrl'] }}" alt="Scanned Image"
                     class="rounded-lg shadow-md w-full h-auto">
            @else
                <p class="text-gray-600">No image to display.</p>
            @endif
        </div>

        <div class="md:col-start-2">
            <h3 class="text-xl font-semibold mb-4 text-gray-700">Extracted Text</h3>

            @foreach($pageData['pageData'] as $page)
                <div class="prose max-w-none overflow-auto max-h-[600px] mb-8">
                    @if(isset($page))
                        {{-- Conteúdo da Página --}}
                        @if(!empty($page['headerTitle']))
                            <h1 class="text-4xl font-extrabold mb-4">{!! $page['headerTitle'] !!}</h1>
                        @endif
                        @if(!empty($page['title']))
                            <h2 class="text-3xl font-bold mb-3">{!! $page['title'] !!}</h2>
                        @endif
                        @if(!empty($page['subtitle']))
                            <h3 class="text-xl font-semibold text-gray-600 mb-6">{!! $page['subtitle'] !!}</h3>
                        @endif
                        @if(!empty($page['paragraphs']) && is_array($page['paragraphs']))
                            @foreach($page['paragraphs'] as $paragraph)
                                <p class="mb-3 leading-relaxed">{!! str_replace('\t', '&nbsp;&nbsp;&nbsp;&nbsp;', $paragraph) !!}</p>
                            @endforeach
                        @endif
                        @if(!empty($page['pageNumber']))
                            <p class="text-sm text-right mt-6">Page: {!! $page['pageNumber'] !!}</p>
                        @endif
                        {{-- Fim do Conteúdo da Página --}}
                    @else
                        <p class="text-red-500">No document data to display.</p>
                    @endif
                </div>
            @endforeach
        </div>

    </div>
    <div class="flex justify-end gap-x-2 mt-4">
        <button onclick="copyPlainText('plainTextContent-{{ $pageData['digitalization_id'] }}')"
                class="cursor-pointer bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg shadow-md transition duration-200 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                <path
                        d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/>
            </svg>
            Copy Clean Text
        </button>
        <textarea id="plainTextContent-{{ $pageData['digitalization_id'] }}"
                  class="hidden">{{ $pageData['plainText'] ?? '' }}</textarea>
        @auth
            <form method="POST"
                  action="{{ route('reDigitalize', ['digitalization' => $pageData['digitalization_id']]) }}">
                @csrf
                <button
                        class="cursor-pointer bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg shadow-md transition duration-200 flex items-center">
                    Re-process
                </button>
            </form>
        @endauth
    </div>
</div>
