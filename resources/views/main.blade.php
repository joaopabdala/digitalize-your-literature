@extends('layouts.main-layout')

@section('content')
    <div class="flex justify-center items-center min-h-screen bg-gray-100">
        <form action="/digitalize" method="POST" enctype="multipart/form-data"
              class="bg-white p-8 rounded-lg shadow-md w-full max-w-md space-y-6">
            @csrf
            @method('post')

            <h2 class="text-2xl font-semibold text-center text-gray-800">Upload a File</h2>

            <div>
                <label for="file" class="block text-sm font-medium text-gray-700 mb-1">Choose file</label>
                <input name="file" type="file" id="file"
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
            </div>

            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded transition duration-200">
                Submit
            </button>
        </form>
    </div>

@endsection
