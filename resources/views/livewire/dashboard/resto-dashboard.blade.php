<div>
    <div class="relative px-8 py-6 bg-yellow-500/60 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold mb-2">Resto</h1>
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

            <a href="{{ route('dashboard.resto.menu-pos') }}"
                class="flex items-center justify-center rounded-2xl shadow-lg h-40 bg-orange-500 hover:bg-orange-600 transform hover:scale-105 transition duration-300">
                <span class="text-white text-lg font-semibold">
                    Order
                </span>
            </a>

            <a href="{{ route('dashboard.resto.orders') }}"
                class="flex items-center justify-center rounded-2xl shadow-lg h-40 bg-blue-500 hover:bg-blue-600 transform hover:scale-105 transition duration-300">
                <span class="text-white text-lg font-semibold">
                    List Order
                </span>
            </a>

            <a href="{{ route('dashboard.resto.chef') }}"
                class="flex items-center justify-center rounded-2xl shadow-lg h-40 bg-red-600 hover:bg-red-700 transform hover:scale-105 transition duration-300">
                <span class="text-white text-lg font-semibold">
                    Kitchen / Chef
                </span>
            </a>

            <a href="{{ route('dashboard.resto.cashier') }}"
                class="flex items-center justify-center rounded-2xl shadow-lg h-40 bg-emerald-500 hover:bg-emerald-600 transform hover:scale-105 transition duration-300">
                <span class="text-white text-lg font-semibold">
                    Kasir
                </span>
            </a>

            <a href="{{ route('dashboard.resto.employee-lunch') }}"
                class="flex items-center justify-center rounded-2xl shadow-lg h-40 bg-amber-500 hover:bg-amber-600 transform hover:scale-105 transition duration-300">
                <span class="text-white text-lg font-semibold">
                    Makan Siang Karyawan
                </span>
            </a>

            <a href="{{ route('dashboard.resto.employee-lunch.report') }}"
                class="flex items-center justify-center rounded-2xl shadow-lg h-40 bg-purple-500 hover:bg-purple-600 transform hover:scale-105 transition duration-300">
                <span class="text-white text-lg font-semibold">
                    Riwayat Makan Siang
                </span>
            </a>

            <!-- Card Master -->
            {{-- <a href="{{ route('dashboard.clinic') }}" --}}
            <a href="{{ route('dashboard.resto.master') }}"
                class="flex items-center justify-center rounded-2xl shadow-lg h-40 bg-green-500 hover:bg-green-600 transform hover:scale-105 transition duration-300">

                <span class="text-white text-lg font-semibold">
                    Master Data
                </span>
            </a>

                <!-- Card Master -->
            {{-- <a href="{{ route('dashboard.clinic') }}" --}}
            <a href="{{ route('dashboard.resto.core-stock') }}"
                class="flex items-center justify-center rounded-2xl shadow-lg h-40 bg-green-500 hover:bg-green-600 transform hover:scale-105 transition duration-300">

                <span class="text-white text-lg font-semibold">
                    Inventory
                </span>
            </a>

               <!-- Card Master -->
            {{-- <a href="{{ route('dashboard.clinic') }}" --}}
            <a href="{{ route('dashboard.resto.master-movement') }}"
                class="flex items-center justify-center rounded-2xl shadow-lg h-40 bg-green-500 hover:bg-green-600 transform hover:scale-105 transition duration-300">

                <span class="text-white text-lg font-semibold">
                    Kelola Stok
                </span>
            </a>

               <!-- Card Master -->
            {{-- <a href="{{ route('dashboard.clinic') }}" --}}
            <a href="{{ route('dashboard.resto.resep') }}"
                class="flex items-center justify-center rounded-2xl shadow-lg h-40 bg-green-500 hover:bg-green-600 transform hover:scale-105 transition duration-300">

                <span class="text-white text-lg font-semibold">
                    Recipe & Production
                </span>
            </a>

               <!-- Card Master -->
            {{-- <a href="{{ route('dashboard.clinic') }}" --}}
            <a href="{{ route('dashboard.resto.master') }}"
                class="flex items-center justify-center rounded-2xl shadow-lg h-40 bg-green-500 hover:bg-green-600 transform hover:scale-105 transition duration-300">

                <span class="text-white text-lg font-semibold">
                Costing & Finance Hooks
                </span>
            </a>

            <a href="{{ route('dashboard.resto.procurement') }}"
                class="flex items-center justify-center rounded-2xl shadow-lg h-40 bg-teal-500 hover:bg-teal-600 transform hover:scale-105 transition duration-300">
                <span class="text-white text-lg font-semibold">
                    Procurement
                </span>
            </a>

              <a href="{{ route('dashboard.resto.menu') }}"
                class="flex items-center justify-center rounded-2xl shadow-lg h-40 bg-green-500 hover:bg-green-600 transform hover:scale-105 transition duration-300">

                <span class="text-white text-lg font-semibold">
                Master Menu
                </span>
            </a>
        </div>
    </div>
</div>
