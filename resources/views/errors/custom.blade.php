@extends('layouts.main-layout')

@section('content')
    <div class="container mx-auto px-4 py-12">
        <div class="max-w-2xl mx-auto bg-white p-8 rounded-xl shadow-md">
            <h2 class="text-2xl font-bold text-red-600 mb-4">Ops, algo deu errado!</h2>
            <p class="text-lg text-gray-700">{{ $message }}</p>
            <a href="{{ url()->previous() }}"
               class="inline-block mt-6 px-6 py-2 bg-red-500 text-white rounded hover:bg-red-600 transition">
                Voltar
            </a>
        </div>
    </div>
@endsection
