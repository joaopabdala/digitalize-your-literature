@extends('layouts.main-layout')

@section('content')
    <div class="min-h-screen bg-gray-100 py-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <h1 class="text-4xl font-extrabold text-gray-900 mb-8 text-center">My Digitalizations</h1>

            @if($digitalizationBatches->isEmpty())
                <div class="bg-white p-8 rounded-lg shadow-md text-center">
                    <p class="text-xl text-gray-600 mb-4">You don't have any digitalizations yet.</p>
                    <a href="{{route('index')}}"
                       class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Digitize a new document
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    @foreach($digitalizationBatches as $digitalizationBatch)
                        <div
                            class="bg-white rounded-lg shadow-lg overflow-hidden transform transition-transform duration-200 hover:scale-105 hover:shadow-xl">
                            <a href="{{ route('dashboard.digitalizationBatch', ['digitalizationBatch' => $digitalizationBatch->id]) }}"
                               class="block">
                                <div
                                    class="relative h-48 w-full bg-gray-200 flex items-center justify-center overflow-hidden">
                                    {{-- Checks if the image exists and displays it --}}
                                    @if($digitalizationBatch->getImageUrlCover())
                                        {{-- Uses Storage::url() to get the public URL of the image stored in storage --}}
                                        <img src="{{ Storage::url($digitalizationBatch->getImageUrlCover()) }}"
                                             alt="Digitized Document - {{ $digitalizationBatch->title ?? 'Untitled' }}"
                                             class="object-cover w-full h-full">
                                    @else
                                        {{-- Placeholder if no image is available --}}
                                        <svg class="h-24 w-24 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2zM4 18h16V6H4v12z"></path>
                                            <path d="M15 10c0 1.1-.9 2-2 2s-2-.9-2-2 .9-2 2-2 2 .9 2 2z"></path>
                                            <path d="M17 17H7v-2.25c0-1.1.9-2 2-2h4c1.1 0 2 .9 2 2V17z"></path>
                                        </svg>
                                    @endif
                                </div>
                                <div class="p-5">
                                    <h3 class="text-xl font-semibold text-gray-800 mb-2 truncate">
                                        {{ $digitalizationBatch->title ?? ($digitalizationBatch->original_file_path ? pathinfo($digitalizationBatch->original_file_path, PATHINFO_FILENAME) : 'Untitled Document') }}
                                    </h3>
                                    <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                                        {{-- You can add a description or a text snippet here if available --}}
                                        @if($digitalizationBatch->created_at)
                                            Digitized on: {{ $digitalizationBatch->created_at->format('d/m/Y H:i') }}
                                        @else
                                            No description available.
                                        @endif
                                    </p>
                                    <span class="text-blue-600 hover:text-blue-800 text-sm font-medium">View details &rarr;</span>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
                <div class="mt-8">
                    {{$digitalizationBatches->links()}}
                </div>
            @endif
        </div>
    </div>
@endsection

