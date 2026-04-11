<div>
    <div class="relative px-8 py-6 bg-yellow-500/60 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold mb-2">Master Resto</h1>
                <p class="text-lg text-gray-800">Silakan pilih modul Resto yang ingin diakses</p>
            </div>
        </div>

        <div class="mt-4 flex justify-between items-center text-sm">
            <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
        </div>
    </div>

    <div class="fixed top-0 left-0 w-full h-full z-0 pointer-events-none">
        <img src="{{ asset('images/bg-gedung.jpg') }}" alt="Background" class="w-full h-full object-cover opacity-30">
    </div>

    <div class="max-w-7xl mx-auto px-6 lg:px-8 py-10">
        <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-5 gap-6">

            <!-- Card Master -->
            {{-- <a href="{{ route('dashboard.clinic') }}" --}}
            <a href="{{ route('dashboard.resto.stock-location') }}"
                class="flex items-center justify-center rounded-2xl shadow-lg h-40 bg-green-500 hover:bg-green-600 transform hover:scale-105 transition duration-300">

                <span class="text-white text-lg font-semibold">
                    Stok
                </span>
            </a>

            {{-- <a href="{{ route('dashboard.resto.stock-item') }}"
                class="flex items-center justify-center rounded-2xl shadow-lg h-40 bg-green-500 hover:bg-green-600 transform hover:scale-105 transition duration-300">

                <span class="text-white text-lg font-semibold">
                    Stok Barang
                </span>
            </a> --}}

            <a href="{{ route('dashboard.resto.stock-minimal') }}"
                class="flex items-center justify-center rounded-2xl shadow-lg h-40 bg-green-500 hover:bg-green-600 transform hover:scale-105 transition duration-300">
                <span class="text-white text-lg font-semibold">
                    Stok Kritis
                </span>
            </a>

            <a href="{{ route('dashboard.resto.stock-mutation') }}"
                class="flex items-center justify-center rounded-2xl shadow-lg h-40 bg-green-500 hover:bg-green-600 transform hover:scale-105 transition duration-300">

                <span class="text-white text-lg font-semibold">
                    Log Stok Movement
                </span>
            </a>

            <a href="{{ route('dashboard.resto.stock-request') }}"
                class="flex items-center justify-center rounded-2xl shadow-lg h-40 bg-green-500 hover:bg-green-600 transform hover:scale-105 transition duration-300">

                <span class="text-white text-lg font-semibold">
                    Log Aktivitas Movement
                </span>
            </a>
        </div>
    </div>
</div>
