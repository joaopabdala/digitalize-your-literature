@extends('layouts.main-layout')

@section('content')
    <div class="flex justify-center items-center min-h-screen bg-gray-100 relative">
        <form id="upload-form" action="{{route('digitalize')}}" method="POST" enctype="multipart/form-data"
              class="bg-white p-8 rounded-lg shadow-md w-full max-w-md space-y-6">
            @csrf
            @method('post')

            <h2 class="text-2xl font-semibold text-center text-gray-800">Upload a File</h2>

            <div>
                <label for="file" class="block text-sm font-medium text-gray-700 mb-1">Choose file</label>
                <input multiple name="file[]" type="file" id="file"
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       required>
            </div>

            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded transition duration-200">
                Submit
            </button>
        </form>

        @if (session('success'))
            <div
                id="success-alert"
                {{-- Classes de Posicionamento para o Canto Superior Direito --}}
                class="fixed top-5 right-5 z-[500]
               {{-- Estilo Visual --}}
               bg-[#E6FFEE] border border-[#A7FFC9] text-[#008A38]
               p-4 rounded-lg my-4 shadow-xl max-w-lg w-11/12 md:w-auto"
                role="alert"

            >
                <div class="flex items-start">
                    <div class="flex-shrink-0 mr-3 mt-1">
                        <svg class="w-6 h-6 text-[#008A38] transform rotate-45" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </div>

                    <div class="flex-grow">
                        <p class="font-bold text-lg text-[#008A38]">Avaliação enviada!</p>

                        <p class="text-base mt-1">
                            {{ session('success') }}
                        </p>

                        <p class="text-base mt-1">
                            Acompanhe o músico, contrate baixando o app e nos siga no Instagram.
                        </p>
                    </div>

                    <span
                        class="flex-shrink-0 ml-4 cursor-pointer"
                        onclick="document.getElementById('success-alert').style.display='none'"
                    >
                <svg class="fill-current h-6 w-6 text-[#008A38] opacity-75 hover:opacity-100" role="button"
                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path
                        d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.697l-2.651 3.152a1.2 1.2 0 1 1-1.697-1.697l3.152-2.651-3.152-2.651a1.2 1.2 0 1 1 1.697-1.697l2.651 3.152 2.651-3.152a1.2 1.2 0 0 1 1.697 1.697l-3.152 2.651 3.152 2.651a1.2 1.2 0 0 1 0 1.697z"/></svg>
            </span>
                </div>
            </div>
        @endif

        @if (session('error') || $errors->any())
            <div
                id="error-alert"
                {{-- Classes de Posicionamento (Mesmo do Sucesso) --}}
                class="fixed top-5 right-5 z-[500]
               {{-- Estilo Visual (Cores de Erro) --}}
               bg-red-100 border border-red-400 text-red-700
               p-4 rounded-lg my-4 shadow-xl max-w-lg w-11/12 md:w-auto"
                role="alert"
            >
                <div class="flex items-start">
                    <div class="flex-shrink-0 mr-3 mt-1">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                             xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.39 17c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>

                    <div class="flex-grow">


                        <p class="font-bold text-lg text-red-800">
                            @if ($errors->any())
                                Corrija os seguintes erros:
                            @else
                                Ops! Algo deu errado.
                            @endif
                        </p>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <p>Por favor, corrija os seguintes erros:</p>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li style="color: red">{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <p class="text-base mt-1">
                            {{ session('error') }}
                        </p>

                    </div>

                    <span
                        class="flex-shrink-0 ml-4 cursor-pointer"
                        onclick="document.getElementById('error-alert').style.display='none'"
                    >
                <svg class="fill-current h-6 w-6 text-red-700 opacity-75 hover:opacity-100" role="button"
                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path
                        d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.697l-2.651 3.152a1.2 1.2 0 1 1-1.697-1.697l3.152-2.651-3.152-2.651a1.2 1.2 0 1 1 1.697-1.697l2.651 3.152 2.651-3.152a1.2 1.2 0 0 1 1.697 1.697l-3.152 2.651 3.152 2.651a1.2 1.2 0 0 1 0 1.697z"/></svg>
            </span>
                </div>
            </div>
        @endif




        <!-- Loading Overlay -->
        <div id="loading-overlay"
             class="hidden fixed inset-0 bg-white bg-opacity-80 flex items-center justify-center z-50">
            <div class="text-xl font-semibold text-blue-700 animate-pulse">
                Processando arquivo, por favor aguarde...
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.getElementById('upload-form').addEventListener('submit', function () {
            document.getElementById('loading-overlay').classList.remove('hidden');
        });
        document.addEventListener('DOMContentLoaded', function () {

            FilePond.setOptions({
                allowMultiple: true,
                server: {
                    url: '/upload',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                }
            });

            const inputElement = document.getElementById('file');
            const pond = FilePond.create(inputElement);
        });
    </script>
@endsection

