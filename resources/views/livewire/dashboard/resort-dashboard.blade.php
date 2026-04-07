<div>
    <div
        class="relative px-8 py-12 bg-gradient-to-r from-yellow-500 to-emerald-700 rounded-b-3xl shadow-lg overflow-hidden">
        <h1 class="text-3xl md:text-4xl font-bold mb-2">Karenina Agung Resort<br>berisi tag</h1>
    </div>

    <div class="fixed top-0 opacity-30">
        <img src="{{ asset('images/bg-resort.png') }}" alt="Background" class="w-full h-200 object-scale-down">
    </div>

    <div class="max-w-7xl mx-auto px-6 lg:px-8 py-4 relative z-10">
        <x-ui.sccr-breadcrumb :items="[
            ['label' => 'Main Dashboard', 'url' => route('dashboard')],
            ['label' => 'Resort', 'url' => route('dashboard.resort')],
            // ['label' => 'SDM', 'url' => route('dashboard.sdm')],
            // ['label' => 'HR', 'url' => route('dashboard.hr')],
        ]" />
    </div>

    <div class="max-w-7xl mx-auto px-6 lg:px-8 py-10">
        <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-4 gap-2 justify-center">

            <!-- SDM -->
            <a href="{{ route('dashboard.sdm') }}"
                class="relative rounded-2xl overflow-hidden shadow-lg transform hover:scale-105 transition duration-300 w-60 h-40">
                <img src="{{ asset('images/tb-sdm.png') }}" alt="SDM"
                    class="absolute inset-0 w-full h-full object-cover" />
                <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-10 transition"></div>
            </a>

            <!-- Production -->
            {{-- <a href="{{ route('dashboard.production') }}" --}}
            <a href="{{ route('dashboard.sdm') }}"
                class="relative rounded-2xl overflow-hidden shadow-lg transform hover:scale-105 transition duration-300 w-60 h-40">
                <img src="{{ asset('images/tb-production.png') }}" alt="Production"
                    class="absolute inset-0 w-full h-full object-cover" />
                <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-10 transition"></div>
            </a>

            <!-- Marketing -->
            <a href="#"
                class="relative rounded-2xl overflow-hidden shadow-lg transform hover:scale-105 transition duration-300 w-60 h-40">
                <img src="{{ asset('images/tb-marketing.png') }}" alt="Marketing"
                    class="absolute inset-0 w-full h-full object-cover" />
                <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-10 transition"></div>
            </a>

            <!-- Finance -->
            <a href="{{ route('dashboard.finance') }}"
                class="relative rounded-2xl overflow-hidden shadow-lg transform hover:scale-105 transition duration-300 w-60 h-40">
                <img src="{{ asset('images/tb-finance.png') }}" alt="Finance"
                    class="absolute inset-0 w-full h-full object-cover" />
                <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-10 transition"></div>
            </a>

        </div>
    </div>
</div>
