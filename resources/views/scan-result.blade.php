@extends('layouts.main-layout')

@section('content')
    <div class="flex flex-col items-center min-h-screen bg-gray-100 py-10 px-4">
        <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-4xl space-y-6">
            <h2 class="text-3xl font-bold text-center text-gray-800 mb-6">Digitization Result</h2>
            <div id="processing-status"
                 class="p-4 border-b-2 mb-6"
                 style="{{ $isComplete ? 'display: none;' : '' }}">

                <h3 class="text-xl font-semibold mb-3 text-blue-700" id="status-message">
                    Processing started. Please wait...
                </h3>

                <div id="main-progress-bar" class="w-full bg-gray-300 rounded-full h-8 overflow-hidden">
                    <div id="progress-indicator"
                         class="bg-blue-600 h-8 text-xs font-medium text-white text-center p-0.5 leading-none rounded-full transition-all duration-500"
                         style="width: 0%">
                        0% (0/{{ $totalFiles }} Pages)
                    </div>
                </div>
            </div>
            <div id="result-content">
                @foreach($pages as $page)
                    @include('partials.digitalization-page', ['pageData' => $page] )
                @endforeach
            </div>
            <div class="flex justify-center mb-6 gap-x-4">

                @auth
                    <a href="{{ route('digitalize.pdf', ['digitalizationBatch' => $digitalizationBatch->id]) }}"
                       class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg shadow-md transition duration-200 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                             xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H5a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2z"></path>
                        </svg>
                        Download as PDF
                    </a>

                    <form action="{{route('digitalize.destroy', ['digitalizationBatch' => $digitalizationBatch->id])}}"
                          method="post">
                        @csrf
                        @method('delete')
                        <button
                            type="submit"
                            class="bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-6 rounded-lg shadow-md transition duration-200 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H5a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2z"></path>
                            </svg>
                            Delete
                        </button>
                    </form>
                @endauth
            </div>

            {{$pages->links()}}

            <div class="text-center">
                <a href="/" class="text-blue-600 hover:underline">Go Back to Digitization</a>
            </div>
        </div>
    </div>

    <script>
        function copyPlainText(inputTextId) {
            const plainTextContent = document.getElementById(inputTextId).textContent;
            const tempTextArea = document.createElement('textarea');
            tempTextArea.value = plainTextContent;
            document.body.appendChild(tempTextArea);

            tempTextArea.select();
            document.execCommand('copy');

            document.body.removeChild(tempTextArea);

            alert('Text copied to clipboard!');
        }
    </script>
@endsection
@section('scripts')
    <script type="module">
        const channelIdentifier = '{{ $digitalizationBatch->id }}'; // Use o ID do batch
        const totalPages = {{ $totalFiles ?? 1 }};
        const processedCountInitial = {{ $processedPages ?? 0 }};
        const isComplete = {{ $isComplete ? 'true' : 'false' }};

        // Elementos DOM
        const statusDiv = document.getElementById('processing-status');
        const resultsDiv = document.getElementById('result-content');
        const progressBar = document.getElementById('progress-indicator');
        const statusMessage = document.getElementById('status-message');

        let processedCount = processedCountInitial;

        // Se não estiver completo, inicia o monitoramento
        if (!isComplete) {
            console.log('⏳ Iniciando escuta de eventos...');

            // Garante que a barra de progresso inicial seja exibida
            if (processedCountInitial > 0) {
                const initialPercent = Math.round((processedCountInitial / totalPages) * 100);
                progressBar.style.width = initialPercent + '%';
                progressBar.innerText = `${initialPercent}% (${processedCountInitial}/${totalPages} Páginas)`;
            }

            // Conecta ao canal do Echo
            window.Echo.channel(`digitalization-status.${channelIdentifier}`)
                .listen('.PageProcessedEvent', (e) => {

                    // Atualiza o contador
                    processedCount = e.processedCount;
                    const total = e.totalImages;
                    const percentage = e.percentage || Math.round((processedCount / total) * 100);


                    // Atualiza a barra de progresso
                    progressBar.style.width = percentage + '%';
                    progressBar.innerText = `${percentage}% (${processedCount}/${total} Páginas)`;
                    statusMessage.innerText = `Processando página ${processedCount} de ${total}...`;
                    // Carrega a página processada via AJAX
                    fetch(`${window.location.pathname}?page_id=${e.pageId}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                        .then(response => response.text())
                        .then(html => {
                            // Parse o HTML retornado
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const newPageContent = doc.querySelector('[data-page-content]');

                            if (newPageContent) {
                                // Adiciona a nova página ao conteúdo existente
                                const resultContent = document.getElementById('result-content');

                                // Se for a primeira página, limpa o "aguardando"
                                if (processedCount === 1) {
                                    resultContent.innerHTML = '';
                                    resultContent.style.display = 'block';
                                }

                                // Adiciona a nova página com animação
                                newPageContent.style.opacity = '0';
                                newPageContent.style.transform = 'translateY(20px)';
                                resultContent.appendChild(newPageContent);

                                // Anima a entrada
                                setTimeout(() => {
                                    newPageContent.style.transition = 'all 0.5s ease';
                                    newPageContent.style.opacity = '1';
                                    newPageContent.style.transform = 'translateY(0)';
                                }, 10);

                            }
                        })
                        .catch(error => {
                            console.error('❌ Erro ao carregar página:', error);
                        });

                    // Verifica se completou
                    if (processedCount >= total) {
                        console.log('✅ Processamento completo!');
                        statusMessage.innerText = '✅ Processamento Concluído!';
                        progressBar.classList.remove('bg-blue-600');
                        progressBar.classList.add('bg-green-600');

                        // Esconde a barra de progresso após 3 segundos
                        setTimeout(() => {
                            statusDiv.style.display = 'none';
                        }, 3000);
                    }
                })
                .subscribed(() => {
                })
                .error((error) => {
                    console.error('❌ Erro no WebSocket:', error);
                });

            // Monitor de conexão do Echo
            window.Echo.connector.pusher.connection.bind('connected', () => {
            });

            window.Echo.connector.pusher.connection.bind('disconnected', () => {
            });

        } else {
            console.log('✅ Processamento já está completo. Não há necessidade de monitorar.');
        }
    </script>
@endsection
