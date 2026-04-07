<header class="bg-white shadow z-50">
    <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
        <h1 class="text-lg font-bold text-gray-700">SCCR System</h1>

        <div class="relative">
            <button class="text-sm font-medium text-gray-700 hover:text-emerald-600">
                {{ auth()->user()->name }}
            </button>
            {{-- Tambahkan dropdown interaktif jika perlu --}}
        </div>
    </div>
</header>
