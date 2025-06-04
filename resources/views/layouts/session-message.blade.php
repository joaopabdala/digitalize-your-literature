@if (session('message'))
    <div
        x-data="{ show: true }"
        x-show="show"
        class="fixed top-8 left-1/2 transform -translate-x-1/2 w-1/2
               px-4 py-3 rounded shadow z-50
               {{ session('type') === 'error' ? 'bg-red-100 border border-red-400 text-red-700' : '' }}
               {{ session('type') === 'success' ? 'bg-green-100 border border-green-400 text-green-700' : '' }}
               {{ session('type') === 'warning' ? 'bg-yellow-100 border border-yellow-400 text-yellow-700' : '' }}
               {{ session('type') === 'info' ? 'bg-blue-100 border border-blue-400 text-blue-700' : '' }}">
        <div class="flex justify-between items-center">
            <p class="text-sm">{{ session('message') }}</p>
            <button @click="show = false" class="ml-4 text-lg font-bold focus:outline-none">&times;</button>
        </div>
    </div>
@endif
