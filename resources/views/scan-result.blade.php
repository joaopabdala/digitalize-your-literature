@extends('layouts.main-layout')

@section('content')
    <div class="flex flex-col items-center min-h-screen bg-gray-100 py-10 px-4">
        <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-4xl space-y-6">
            <h2 class="text-3xl font-bold text-center text-gray-800 mb-6">Digitization Result</h2>

            <div class="flex flex-wrap -mx-4">
                <div class="w-full md:w-1/2 px-4 mb-6 md:mb-0">
                    <h3 class="text-xl font-semibold mb-4 text-gray-700">Scanned Image</h3>
                    @if(isset($imageUrl))
                        <img src="{{ $imageUrl }}" alt="Scanned Image" class="rounded-lg shadow-md max-w-full h-auto">
                    @else
                        <p class="text-gray-600">No image to display.</p>
                    @endif
                </div>

                <div class="w-full md:w-1/2 px-4">
                    <h3 class="text-xl font-semibold mb-4 text-gray-700">Extracted Text</h3>
                    <div id="formattedContent" class="prose max-w-none overflow-auto max-h-[600px]">
                        @if(isset($pageData))
                            @if(!empty($pageData['headerTitle']))
                                <h1 class="text-4xl font-extrabold mb-4">{!! $pageData['headerTitle'] !!}</h1>
                            @endif
                            @if(!empty($pageData['title']))
                                <h2 class="text-3xl font-bold mb-3">{!! $pageData['title'] !!}</h2>
                            @endif
                            @if(!empty($pageData['subtitle']))
                                <h3 class="text-xl font-semibold text-gray-600 mb-6">{!! $pageData['subtitle'] !!}</h3>
                            @endif
                            @if(!empty($pageData['paragraphs']) && is_array($pageData['paragraphs']))
                                @foreach($pageData['paragraphs'] as $paragraph)
                                    <p class="mb-3 leading-relaxed">{!! str_replace('\t', '&nbsp;&nbsp;&nbsp;&nbsp;', $paragraph) !!}</p>
                                @endforeach
                            @endif
                            @if(!empty($pageData['pageNumber']))
                                <p class="text-sm text-right mt-6">Page: {!! $pageData['pageNumber'] !!}</p>
                            @endif
                        @else
                            <p class="text-red-500">No document data to display.</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Copy Button --}}
            <div class="flex justify-center mb-6">
                <button id="copyToClipboard"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg shadow-md transition duration-200 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 0 00-2 2v12a2 0 002 2h10a2 0 002-2V7a2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    Copy Clean Text
                </button>
            </div>

            @auth
                <form method="POST" action="{{ route('digitalize.pdf') }}">
                    @csrf
                    <input type="hidden" name="digitization" value="{{ $digitizationId }}">
                    <button
                        type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg shadow-md transition duration-200 flex items-center">
                        Download as PDF
                    </button>
                </form>
            @endauth

            {{-- Hidden area for plain text copy --}}
            <textarea id="plainTextContent" class="hidden">{{ $plainText ?? '' }}</textarea>

            {{-- Back Button --}}
            <div class="text-center">
                <a href="/" class="text-blue-600 hover:underline">Go Back to Digitization</a>
            </div>
        </div>
        @dump($pageData)
    </div>

    <script>
        document.getElementById('copyToClipboard').addEventListener('click', function() {
            const plainTextContent = document.getElementById('plainTextContent').value;

            const tempTextArea = document.createElement('textarea');
            tempTextArea.value = plainTextContent;
            document.body.appendChild(tempTextArea);

            tempTextArea.select();
            document.execCommand('copy'); // Using execCommand for wider compatibility

            document.body.removeChild(tempTextArea);

            alert('Text copied to clipboard!');
        });
    </script>
@endsection
