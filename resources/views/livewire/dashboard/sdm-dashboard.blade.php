<div>

    {{-- ================= HEADER ================= --}}
    <div class="relative px-8 py-6 bg-yellow-500/60 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold mb-2">Holding HQ - SDM</h1>
                <p class="text-lg text-gray-800">Silakan pilih modul SDM yang ingin diakses</p>
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
        <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-4 gap-4 justify-center">

            <!-- HR -->
            <a href="{{ route('dashboard.hr') }}"
                class="relative rounded-2xl overflow-hidden shadow-lg transform hover:scale-105 transition duration-300 w-60 h-40">
                <img src="{{ asset('images/tb-sdm-hr.png') }}" alt="HR"
                    class="absolute inset-0 w-full h-full object-cover" />
                <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-10 transition"></div>
            </a>

            <!-- GA -->
            {{-- <a href="{{ route('hq.sdm.ga') }}" --}}
            <a href="{{ route('dashboard.rt') }}"
                class="relative rounded-2xl overflow-hidden shadow-lg transform hover:scale-105 transition duration-300 w-60 h-40">
                <img src="{{ asset('images/tb-sdm-ga.png') }}" alt="GA"
                    class="absolute inset-0 w-full h-full object-cover" />
                <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-10 transition"></div>
            </a>

            <!-- IR -->
            {{-- <a href="{{ route('hq.sdm.ir') }}" --}}
            <a href="{{ route('holdings.hq.sdm.hr.absensi.index') }}"
                class="relative rounded-2xl overflow-hidden shadow-lg transform hover:scale-105 transition duration-300 w-60 h-40">
                <img src="{{ asset('images/tb-sdm-doc.png') }}" alt="IR"
                    class="absolute inset-0 w-full h-full object-cover" />
                <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-10 transition"></div>
            </a>

            <!-- Project -->
            {{-- <a href="{{ route('hq.sdm.projects') }}" --}}
            {{-- <a href="{{ route('dashboard.hr') }}"
                class="relative rounded-2xl overflow-hidden shadow-lg transform hover:scale-105 transition duration-300 w-60 h-40">
                <img src="{{ asset('images/bg-project.png') }}" alt="Project"
                    class="absolute inset-0 w-full h-full object-cover" />
                <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-10 transition"></div>
            </a> --}}

        </div>
    </div>
</div>
