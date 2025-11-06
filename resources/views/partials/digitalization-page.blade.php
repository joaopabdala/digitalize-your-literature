<div data-page-content class="border-b-2 pb-6 mb-6">
    <div class="flex flex-wrap -mx-4">

        <div class="w-full md:w-1/2 px-4 mb-6 md:mb-0">
            <h3 class="text-xl font-semibold mb-4 text-gray-700">Scanned Image</h3>
            @if(isset($pageData['imageUrl']))
                <img src="{{ $pageData['imageUrl'] }}" alt="Scanned Image"
                     class="rounded-lg shadow-md max-w-full h-auto">
            @else
                <p class="text-gray-600">No image to display.</p>
            @endif
        </div>

        <div class="w-full md:w-1/2 px-4">
            <h3 class="text-xl font-semibold mb-4 text-gray-700">Extracted Text</h3>
            <div class="prose max-w-none overflow-auto max-h-[600px]">
                @if(isset($pageData['pageData']))
                    @if(!empty($pageData['pageData']['headerTitle']))
                        <h1 class="text-4xl font-extrabold mb-4">{!! $pageData['pageData']['headerTitle'] !!}</h1>
                    @endif
                    @if(!empty($pageData['pageData']['title']))
                        <h2 class="text-3xl font-bold mb-3">{!! $pageData['pageData']['title'] !!}</h2>
                    @endif
                    @if(!empty($pageData['pageData']['subtitle']))
                        <h3 class="text-xl font-semibold text-gray-600 mb-6">{!! $pageData['pageData']['subtitle'] !!}</h3>
                    @endif
                    @if(!empty($pageData['pageData']['paragraphs']) && is_array($pageData['pageData']['paragraphs']))
                        @foreach($pageData['pageData']['paragraphs'] as $paragraph)
                            <p class="mb-3 leading-relaxed">{!! str_replace('\t', '&nbsp;&nbsp;&nbsp;&nbsp;', $paragraph) !!}</p>
                        @endforeach
                    @endif
                    @if(!empty($pageData['pageData']['pageNumber']))
                        <p class="text-sm text-right mt-6">Page: {!! $pageData['pageData']['pageNumber'] !!}</p>
                    @endif
                @else
                    <p class="text-red-500">No document data to display.</p>
                @endif
            </div>
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
