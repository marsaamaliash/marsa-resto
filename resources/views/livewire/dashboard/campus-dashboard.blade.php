<div>
    <div
        class="relative px-8 py-12 bg-gradient-to-r from-yellow-500 to-emerald-700 rounded-b-3xl shadow-lg overflow-hidden">
        <h1 class="text-3xl md:text-4xl font-bold mb-2">Agung Putra University<br>Futuristic Green Campus</h1>
    </div>

    <div class="fixed top-0 opacity-30">
        <img src="{{ asset('images/bg-gedung.jpg') }}" alt="Background" class="w-full h-200 object-scale-down">
    </div>

    <div class="max-w-7xl mx-auto px-6 lg:px-8 py-4 relative z-10">
        <x-ui.sccr-breadcrumb :items="[
            ['label' => 'Main Dashboard', 'url' => route('dashboard')],
            ['label' => 'Campus', 'url' => route('dashboard.campus')],
            // ['label' => 'LMS', 'url' => route('dashboard.lms')],
            // ['label' => 'Room', 'url' => route('dashboard.hr')],
        ]" />
    </div>

    <div class="max-w-7xl mx-auto px-6 lg:px-8 py-10">
        <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-4 gap-2 justify-center">

            <!-- LMS -->
            <a href="{{ route('dashboard.lms-main') }}"
                class="relative rounded-2xl overflow-hidden shadow-lg transform hover:scale-105 transition duration-300 w-60 h-40">
                <img src="{{ asset('images/tb-campus-lms.png') }}" alt="LMS"
                    class="absolute inset-0 w-full h-full object-cover" />
                <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-10 transition"></div>
            </a>

            <!-- Kepegawaian -->
            {{-- <a href="{{ route('dashboard.production') }}" --}}
            <a href="#" {{-- <a href="{{ route('dashboard.lms-dashboard') }}" --}}
                class="relative rounded-2xl overflow-hidden shadow-lg transform hover:scale-105 transition duration-300 w-60 h-40">
                <img src="{{ asset('images/tb-campus-kepegawaian.png') }}" alt="Kepegawaian"
                    class="absolute inset-0 w-full h-full object-cover" />
                <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-10 transition"></div>
            </a>

            <!-- Siakad -->
            <a href="{{ route('dashboard.siakad-dashboard') }}"
                class="relative rounded-2xl overflow-hidden shadow-lg transform hover:scale-105 transition duration-300 w-60 h-40">
                <img src="{{ asset('images/tb-campus-siakad.png') }}" alt="Siakad"
                    class="absolute inset-0 w-full h-full object-cover" />
                <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-10 transition"></div>
            </a>

            <!-- Asset -->
            <a href="{{ route('dashboard.finance') }}"
                class="relative rounded-2xl overflow-hidden shadow-lg transform hover:scale-105 transition duration-300 w-60 h-40">
                <img src="{{ asset('images/tb-campus-asset.png') }}" alt="Asset"
                    class="absolute inset-0 w-full h-full object-cover" />
                <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-10 transition"></div>
            </a>

        </div>
    </div>
</div>
