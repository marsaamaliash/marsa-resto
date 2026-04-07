<div>

    {{-- ================= HEADER ================= --}}
    <div class="relative px-8 py-6 bg-green-600/60 rounded-b-3xl shadow overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold mb-2">Holding HQ - FINANCE 💰</h1>
                <p class="text-lg text-gray-800">Silakan pilih modul FINANCE yang ingin diakses</p>
            </div>
        </div>

        <div class="mt-4 flex justify-between items-center text-sm">
            <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
        </div>
    </div>

    <div class="fixed top-0 left-0 w-full h-full z-0 pointer-events-none">
        <img src="{{ asset('images/bg-gedung.jpg') }}" alt="Background" class="w-full h-full object-cover opacity-30">
    </div>

    <div class="max-w-7xl mx-auto px-6 lg:px-8 py-4">
        <div class="grid grid-cols-2 md:grid-cols-5 lg:grid-cols-5 gap-4 justify-center">

            <!-- Accounting -->
            <a href="{{ route('dashboard.hr') }}"
                class="relative rounded-2xl overflow-hidden shadow-lg transform hover:scale-105 transition duration-300 w-60 h-40">
                <img src="{{ asset('images/tb-finance-accounting.png') }}" alt="Accounting"
                    class="absolute inset-0 w-full h-full object-cover" />
                <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-10 transition"></div>
            </a>

            <!-- Budgeting -->
            {{-- <a href="{{ route('hq.sdm.ga') }}" --}}
            <a href="{{ route('dashboard.hr') }}"
                class="relative rounded-2xl overflow-hidden shadow-lg transform hover:scale-105 transition duration-300 w-60 h-40">
                <img src="{{ asset('images/tb-finance-budgeting.png') }}" alt="Budgeting"
                    class="absolute inset-0 w-full h-full object-cover" />
                <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-10 transition"></div>
            </a>

            <!-- Cash Management -->
            {{-- <a href="{{ route('hq.sdm.ir') }}" --}}
            <a href="{{ route('dashboard.hr') }}"
                class="relative rounded-2xl overflow-hidden shadow-lg transform hover:scale-105 transition duration-300 w-60 h-40">
                <img src="{{ asset('images/tb-finance-cash.png') }}" alt="Cash Management"
                    class="absolute inset-0 w-full h-full object-cover" />
                <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-10 transition"></div>
            </a>

            <!-- Financial Analysis -->
            {{-- <a href="{{ route('hq.sdm.projects') }}" --}}
            <a href="{{ route('dashboard.hr') }}"
                class="relative rounded-2xl overflow-hidden shadow-lg transform hover:scale-105 transition duration-300 w-60 h-40">
                <img src="{{ asset('images/tb-finance-analysis.png') }}" alt="Financial Analysis"
                    class="absolute inset-0 w-full h-full object-cover" />
                <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-10 transition"></div>
            </a>

            <!-- Financial Reporting -->
            {{-- <a href="{{ route('hq.sdm.projects') }}" --}}
            <a href="{{ route('dashboard.hr') }}"
                class="relative rounded-2xl overflow-hidden shadow-lg transform hover:scale-105 transition duration-300 w-60 h-40">
                <img src="{{ asset('images/tb-finance-reporting.png') }}" alt="Financial Reporting"
                    class="absolute inset-0 w-full h-full object-cover" />
                <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-10 transition"></div>
            </a>

            <!-- Inventory -->
            {{-- <a href="{{ route('hq.sdm.projects') }}" --}}
            <a href="{{ route('dashboard.hr') }}"
                class="relative rounded-2xl overflow-hidden shadow-lg transform hover:scale-105 transition duration-300 w-60 h-40">
                <img src="{{ asset('images/tb-finance-inventory.png') }}" alt="Inventory"
                    class="absolute inset-0 w-full h-full object-cover" />
                <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-10 transition"></div>
            </a>

            <!-- Payroll -->
            {{-- <a href="{{ route('hq.sdm.projects') }}" --}}
            <a href="{{ route('dashboard.hr') }}"
                class="relative rounded-2xl overflow-hidden shadow-lg transform hover:scale-105 transition duration-300 w-60 h-40">
                <img src="{{ asset('images/tb-finance-payroll.png') }}" alt="Payroll"
                    class="absolute inset-0 w-full h-full object-cover" />
                <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-10 transition"></div>
            </a>

            <!-- Purchasing -->
            {{-- <a href="{{ route('hq.sdm.projects') }}" --}}
            <a href="{{ route('dashboard.hr') }}"
                class="relative rounded-2xl overflow-hidden shadow-lg transform hover:scale-105 transition duration-300 w-60 h-40">
                <img src="{{ asset('images/tb-finance-purchasing.png') }}" alt="Purchasing"
                    class="absolute inset-0 w-full h-full object-cover" />
                <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-10 transition"></div>
            </a>

            <!-- Sales -->
            {{-- <a href="{{ route('hq.sdm.projects') }}" --}}
            <a href="{{ route('dashboard.hr') }}"
                class="relative rounded-2xl overflow-hidden shadow-lg transform hover:scale-105 transition duration-300 w-60 h-40">
                <img src="{{ asset('images/tb-finance-sales.png') }}" alt="Sales"
                    class="absolute inset-0 w-full h-full object-cover" />
                <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-10 transition"></div>
            </a>

            <!-- Tax -->
            {{-- <a href="{{ route('hq.sdm.projects') }}" --}}
            <a href="{{ route('dashboard.hr') }}"
                class="relative rounded-2xl overflow-hidden shadow-lg transform hover:scale-105 transition duration-300 w-60 h-40">
                <img src="{{ asset('images/tb-finance-tax.png') }}" alt="Tax"
                    class="absolute inset-0 w-full h-full object-cover" />
                <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-10 transition"></div>
            </a>

            {{-- ===================== MASTER (TRUTH PAGE) ===================== --}}
            {{-- @permission('FIN_MASTER_ACCOUNT_VIEW') --}}
            <a href="{{ route('holdings.hq.finance.master.account.table') }}"
                class="relative rounded-2xl overflow-hidden shadow-lg transform hover:scale-105 transition duration-300 w-60 h-40">
                <img src="{{ asset('images/tb-master.png') }}"
                    onerror="this.src='{{ asset('images/tb-master.png') }}'" alt="Master Account"
                    class="absolute inset-0 w-full h-full object-cover" />
                <div class="absolute inset-0 bg-black/25"></div>
                <div class="absolute bottom-3 left-3 right-3">
                    <div class="text-white font-black text-sm tracking-wide">MASTER ACCOUNT</div>
                    <div class="text-white/90 text-[11px]">Table + Overlay CRUD</div>
                </div>
            </a>
            {{-- @endpermission --}}

        </div>
    </div>
</div>
