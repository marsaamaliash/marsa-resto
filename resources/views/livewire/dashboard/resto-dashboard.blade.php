
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
            {{-- ==================== POS ==================== --}}
            <div class="col-span-full">
                <h2 class="text-xl font-bold text-gray-800 border-b-2 border-orange-500 pb-2 mb-4">POS</h2>
            </div>
            <a href="{{ route('dashboard.resto.menu-pos') }}"
                class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-orange-500 hover:bg-orange-600 transform hover:scale-105 transition duration-300">
                <span class="text-3xl mb-1"></span>
                <span class="text-white text-sm font-semibold">Order</span>
            </a>
            <a href="{{ route('dashboard.resto.orders') }}"
                class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-orange-500 hover:bg-orange-600 transform hover:scale-105 transition duration-300">
                <span class="text-3xl mb-1">📋</span>
                <span class="text-white text-sm font-semibold">List Order</span>
            </a>
            <a href="{{ route('dashboard.resto.chef') }}"
                class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-orange-500 hover:bg-orange-600 transform hover:scale-105 transition duration-300">
                <span class="text-3xl mb-1">👨‍🍳</span>
                <span class="text-white text-sm font-semibold">Kitchen Display</span>
            </a>
            <a href="{{ route('dashboard.resto.cashier') }}"
                class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-orange-500 hover:bg-orange-600 transform hover:scale-105 transition duration-300">
                <span class="text-3xl mb-1">💰</span>
                <span class="text-white text-sm font-semibold">Kasir</span>
            </a>
            <div class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-gray-300 opacity-60 cursor-not-allowed">
                <span class="text-3xl mb-1">📅</span>
                <span class="text-gray-600 text-sm font-semibold">Reservasi</span>
                <span class="text-gray-500 text-xs mt-1">Segera Hadir</span>
            </div>
            <div class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-gray-300 opacity-60 cursor-not-allowed">
                <span class="text-3xl mb-1">🔒</span>
                <span class="text-gray-600 text-sm font-semibold">Daily Closing</span>
                <span class="text-gray-500 text-xs mt-1">Segera Hadir</span>
            </div>
            {{-- ==================== DASHBOARD ==================== --}}
            <div class="col-span-full mt-6">
                <h2 class="text-xl font-bold text-gray-800 border-b-2 border-blue-500 pb-2 mb-4">Dashboard</h2>
            </div>
            <div class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-gray-300 opacity-60 cursor-not-allowed">
                <span class="text-3xl mb-1"></span>
                <span class="text-gray-600 text-sm font-semibold">Ringkasan Penjualan</span>
                <span class="text-gray-500 text-xs mt-1">Segera Hadir</span>
            </div>
            <div class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-gray-300 opacity-60 cursor-not-allowed">
                <span class="text-3xl mb-1">📈</span>
                <span class="text-gray-600 text-sm font-semibold">Penjualan per Kategori</span>
                <span class="text-gray-500 text-xs mt-1">Segera Hadir</span>
            </div>
            <div class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-gray-300 opacity-60 cursor-not-allowed">
                <span class="text-3xl mb-1">🏆</span>
                <span class="text-gray-600 text-sm font-semibold">Kategori Terlaris</span>
                <span class="text-gray-500 text-xs mt-1">Segera Hadir</span>
            </div>
            <div class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-gray-300 opacity-60 cursor-not-allowed">
                <span class="text-3xl mb-1">⚠️</span>
                <span class="text-gray-600 text-sm font-semibold">Stock Alert</span>
                <span class="text-gray-500 text-xs mt-1">Segera Hadir</span>
            </div>
            {{-- ==================== MASTER DATA ==================== --}}
            <div class="col-span-full mt-6">
                <h2 class="text-xl font-bold text-gray-800 border-b-2 border-green-500 pb-2 mb-4">Master Data</h2>
            </div>
            <a href="{{ route('dashboard.resto.item') }}"
                class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-green-500 hover:bg-green-600 transform hover:scale-105 transition duration-300">
                <span class="text-3xl mb-1">🥬</span>
                <span class="text-white text-sm font-semibold">Bahan Baku</span>
            </a>
            <a href="{{ route('dashboard.resto.kategori') }}"
                class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-green-500 hover:bg-green-600 transform hover:scale-105 transition duration-300">
                <span class="text-3xl mb-1">🏷️</span>
                <span class="text-white text-sm font-semibold">Kategori</span>
            </a>
            <a href="{{ route('dashboard.resto.satuan') }}"
                class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-green-500 hover:bg-green-600 transform hover:scale-105 transition duration-300">
                <span class="text-3xl mb-1">📏</span>
                <span class="text-white text-sm font-semibold">Satuan</span>
            </a>
            <a href="{{ route('dashboard.resto.konversi-satuan') }}"
                class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-green-500 hover:bg-green-600 transform hover:scale-105 transition duration-300">
                <span class="text-3xl mb-1">🔄</span>
                <span class="text-white text-sm font-semibold">Konversi Satuan</span>
            </a>
            <a href="{{ route('dashboard.resto.vendor') }}"
                class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-green-500 hover:bg-green-600 transform hover:scale-105 transition duration-300">
                <span class="text-3xl mb-1">🏭</span>
                <span class="text-white text-sm font-semibold">Vendor</span>
            </a>
            <a href="{{ route('dashboard.resto.lokasi') }}"
                class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-green-500 hover:bg-green-600 transform hover:scale-105 transition duration-300">
                <span class="text-3xl mb-1">📍</span>
                <span class="text-white text-sm font-semibold">Lokasi</span>
            </a>
            <a href="{{ route('dashboard.resto.meja') }}"
                class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-green-500 hover:bg-green-600 transform hover:scale-105 transition duration-300">
                <span class="text-3xl mb-1">🪑</span>
                <span class="text-white text-sm font-semibold">Manajemen Meja</span>
            </a>
            <div class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-gray-300 opacity-60 cursor-not-allowed">
                <span class="text-3xl mb-1">👥</span>
                <span class="text-gray-600 text-sm font-semibold">Customer</span>
                <span class="text-gray-500 text-xs mt-1">Segera Hadir</span>
            </div>
            {{-- ==================== INVENTORY ==================== --}}
            <div class="col-span-full mt-6">
                <h2 class="text-xl font-bold text-gray-800 border-b-2 border-green-600 pb-2 mb-4">Inventory</h2>
            </div>
            <a href="{{ route('dashboard.resto.core-stock') }}"
                class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-green-600 hover:bg-green-700 transform hover:scale-105 transition duration-300">
                <span class="text-3xl mb-1">📦</span>
                <span class="text-white text-sm font-semibold">Stock</span>
            </a>
            <a href="{{ route('dashboard.resto.stock-minimal') }}"
                class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-green-600 hover:bg-green-700 transform hover:scale-105 transition duration-300">
                <span class="text-3xl mb-1">🔴</span>
                <span class="text-white text-sm font-semibold">Stock Kritis</span>
            </a>
            <a href="{{ route('dashboard.resto.movement-internal') }}"
                class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-green-600 hover:bg-green-700 transform hover:scale-105 transition duration-300">
                <span class="text-3xl mb-1">🚚</span>
                <span class="text-white text-sm font-semibold">Stock Movement</span>
            </a>
            <div class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-gray-300 opacity-60 cursor-not-allowed">
                <span class="text-3xl mb-1">📝</span>
                <span class="text-gray-600 text-sm font-semibold">Stock Opname</span>
                <span class="text-gray-500 text-xs mt-1">Segera Hadir</span>
            </div>
            <div class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-gray-300 opacity-60 cursor-not-allowed">
                <span class="text-3xl mb-1">🗑️</span>
                <span class="text-gray-600 text-sm font-semibold">Waste</span>
                <span class="text-gray-500 text-xs mt-1">Segera Hadir</span>
            </div>
            {{-- ==================== PROCUREMENT ==================== --}}
            <div class="col-span-full mt-6">
                <h2 class="text-xl font-bold text-gray-800 border-b-2 border-teal-500 pb-2 mb-4">Procurement</h2>
            </div>
            <a href="{{ route('dashboard.resto.purchase-request') }}"
                class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-teal-500 hover:bg-teal-600 transform hover:scale-105 transition duration-300">
                <span class="text-3xl mb-1">📄</span>
                <span class="text-white text-sm font-semibold">PR</span>
            </a>
            <a href="{{ route('dashboard.resto.purchase-order') }}"
                class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-teal-500 hover:bg-teal-600 transform hover:scale-105 transition duration-300">
                <span class="text-3xl mb-1">📋</span>
                <span class="text-white text-sm font-semibold">PO</span>
            </a>
            <div class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-gray-300 opacity-60 cursor-not-allowed">
                <span class="text-3xl mb-1">📥</span>
                <span class="text-gray-600 text-sm font-semibold">Goods Receipt</span>
                <span class="text-gray-500 text-xs mt-1">Segera Hadir</span>
            </div>
            <a href="{{ route('dashboard.resto.direct-order') }}"
                class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-teal-500 hover:bg-teal-600 transform hover:scale-105 transition duration-300">
                <span class="text-3xl mb-1"></span>
                <span class="text-white text-sm font-semibold">DO</span>
            </a>
            <div class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-gray-300 opacity-60 cursor-not-allowed">
                <span class="text-3xl mb-1">💳</span>
                <span class="text-gray-600 text-sm font-semibold">Invoice Vendor</span>
                <span class="text-gray-500 text-xs mt-1">Segera Hadir</span>
            </div>
            {{-- ==================== RECIPE & PRODUCTION ==================== --}}
            <div class="col-span-full mt-6">
                <h2 class="text-xl font-bold text-gray-800 border-b-2 border-green-500 pb-2 mb-4">Recipe & Production</h2>
            </div>
            <a href="{{ route('dashboard.resto.resep-menu') }}"
                class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-green-500 hover:bg-green-600 transform hover:scale-105 transition duration-300">
                <span class="text-3xl mb-1">📖</span>
                <span class="text-white text-sm font-semibold">Resep Menu</span>
            </a>
            <a href="{{ route('dashboard.resto.resep.recipe') }}"
                class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-green-500 hover:bg-green-600 transform hover:scale-105 transition duration-300">
                <span class="text-3xl mb-1">🧪</span>
                <span class="text-white text-sm font-semibold">Resep Semi-Finished</span>
            </a>
            <div class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-gray-300 opacity-60 cursor-not-allowed">
                <span class="text-3xl mb-1">⚙️</span>
                <span class="text-gray-600 text-sm font-semibold">Additional Condition</span>
                <span class="text-gray-500 text-xs mt-1">Segera Hadir</span>
            </div>
            {{-- ==================== DAFTAR MENU ==================== --}}
            <div class="col-span-full mt-6">
                <h2 class="text-xl font-bold text-gray-800 border-b-2 border-green-500 pb-2 mb-4">Daftar Menu</h2>
            </div>
            <a href="{{ route('dashboard.resto.menu') }}"
                class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-green-500 hover:bg-green-600 transform hover:scale-105 transition duration-300">
                <span class="text-3xl mb-1"></span>
                <span class="text-white text-sm font-semibold">HJP</span>
            </a>
            {{-- ==================== LAPORAN ==================== --}}
            <div class="col-span-full mt-6">
                <h2 class="text-xl font-bold text-gray-800 border-b-2 border-purple-500 pb-2 mb-4">Laporan</h2>
            </div>
            <div class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-gray-300 opacity-60 cursor-not-allowed">
                <span class="text-3xl mb-1">📅</span>
                <span class="text-gray-600 text-sm font-semibold">Rekap Harian</span>
                <span class="text-gray-500 text-xs mt-1">Segera Hadir</span>
            </div>
            <div class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-gray-300 opacity-60 cursor-not-allowed">
                <span class="text-3xl mb-1">📊</span>
                <span class="text-gray-600 text-sm font-semibold">Laporan Penjualan</span>
                <span class="text-gray-500 text-xs mt-1">Segera Hadir</span>
            </div>
            <div class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-gray-300 opacity-60 cursor-not-allowed">
                <span class="text-3xl mb-1">📦</span>
                <span class="text-gray-600 text-sm font-semibold">Laporan Stock</span>
                <span class="text-gray-500 text-xs mt-1">Segera Hadir</span>
            </div>
            <div class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-gray-300 opacity-60 cursor-not-allowed">
                <span class="text-3xl mb-1">🗑️</span>
                <span class="text-gray-600 text-sm font-semibold">Laporan Waste</span>
                <span class="text-gray-500 text-xs mt-1">Segera Hadir</span>
            </div>
            <div class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-gray-300 opacity-60 cursor-not-allowed">
                <span class="text-3xl mb-1">💰</span>
                <span class="text-gray-600 text-sm font-semibold">Laporan Profit</span>
                <span class="text-gray-500 text-xs mt-1">Segera Hadir</span>
            </div>
            <div class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-gray-300 opacity-60 cursor-not-allowed">
                <span class="text-3xl mb-1">🏦</span>
                <span class="text-gray-600 text-sm font-semibold">Laporan Keuangan</span>
                <span class="text-gray-500 text-xs mt-1">Segera Hadir</span>
            </div>
            {{-- ==================== SETTING ==================== --}}
            <div class="col-span-full mt-6">
                <h2 class="text-xl font-bold text-gray-800 border-b-2 border-gray-500 pb-2 mb-4">Setting</h2>
            </div>
            <div class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-gray-300 opacity-60 cursor-not-allowed">
                <span class="text-3xl mb-1">👤</span>
                <span class="text-gray-600 text-sm font-semibold">User Management</span>
                <span class="text-gray-500 text-xs mt-1">Segera Hadir</span>
            </div>
            <div class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-gray-300 opacity-60 cursor-not-allowed">
                <span class="text-3xl mb-1">🔑</span>
                <span class="text-gray-600 text-sm font-semibold">Role</span>
                <span class="text-gray-500 text-xs mt-1">Segera Hadir</span>
            </div>
            {{-- ==================== LAINNYA (Tile Lama) ==================== --}}
            <div class="col-span-full mt-6">
                <h2 class="text-xl font-bold text-gray-800 border-b-2 border-amber-500 pb-2 mb-4">Lainnya</h2>
            </div>
            <a href="{{ route('dashboard.resto.employee-lunch') }}"
                class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-amber-500 hover:bg-amber-600 transform hover:scale-105 transition duration-300">
                <span class="text-3xl mb-1">🍱</span>
                <span class="text-white text-sm font-semibold">Makan Siang Karyawan</span>
            </a>
            <a href="{{ route('dashboard.resto.employee-lunch.report') }}"
                class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-purple-500 hover:bg-purple-600 transform hover:scale-105 transition duration-300">
                <span class="text-3xl mb-1"></span>
                <span class="text-white text-sm font-semibold">Riwayat Makan Siang</span>
            </a>
            <a href="{{ route('dashboard.resto.master') }}"
                class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-green-500 hover:bg-green-600 transform hover:scale-105 transition duration-300">
                <span class="text-3xl mb-1">💲</span>
                <span class="text-white text-sm font-semibold">Costing & Finance Hooks</span>
            </a>
            <a href="{{ route('dashboard.resto.menu') }}"
                class="flex flex-col items-center justify-center rounded-2xl shadow-lg h-36 bg-green-500 hover:bg-green-600 transform hover:scale-105 transition duration-300">
                <span class="text-3xl mb-1">📋</span>
                <span class="text-white text-sm font-semibold">Master Menu</span>
            </a>
        </div>
    </div>
</div>
