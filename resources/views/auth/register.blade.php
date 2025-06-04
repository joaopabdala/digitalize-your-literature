@extends('layouts.main-layout')

@section('content')
    <div class="flex justify-center items-center min-h-screen">

        <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
            <h2 class="text-2xl font-bold text-center mb-6">Create an Account</h2>

            {{-- Error block --}}
        @include('layouts.erros-any-block')

            <form action="/register" method="POST">
                @csrf
                @method('post')
                <div class="mb-4">
                    <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Name:</label>
                    <input type="text" id="name" name="name"
                           value="{{ old('name') }}"
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                           required>
                </div>
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email:</label>
                    <input type="email" id="email" name="email"
                           value="{{ old('email') }}"
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                           required>
                </div>
                <div class="mb-4">
                    <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password:</label>
                    <input type="password" id="password" name="password"
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline"
                           required>
                </div>
                <div class="mb-6">
                    <label for="password_confirmation" class="block text-gray-700 text-sm font-bold mb-2">Confirm Password:</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline"
                           required>
                </div>
                <div class="flex items-center justify-between">
                    <button type="submit"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">
                        Register
                    </button>
                </div>
                <div class="mt-4 text-center">
                    <p class="text-sm">Already have an account? <a href="login.html"
                                                                   class="text-blue-500 hover:text-blue-700 font-bold">Log In</a></p>
                </div>
            </form>
        </div>
    </div>
@endsection
