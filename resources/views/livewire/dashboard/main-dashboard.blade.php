<div>
    <div
        class="relative px-8 py-12 bg-gradient-to-r from-yellow-500 to-emerald-700 rounded-b-3xl shadow-lg overflow-hidden">
        <h1 class="text-3xl md:text-4xl font-bold mb-2">Welcome to <br>
            the Single Sign-On Integration System
        </h1>
    </div>

    <div class="fixed top-0 left-0 w-full h-full z-0 pointer-events-none">
        <img src="{{ asset('images/bg-gedung.jpg') }}" alt="Background" class="w-full h-full object-cover opacity-30">
    </div>

    <div class="max-w-7xl mx-auto px-6 lg:px-8 py-10">

        <div class="flex justify-center mb-8">
            <a href="{{ route('dashboard.hq') }}" {{-- <a href="{{ route('dashboard.hq') }}" --}}
                class="relative rounded-2xl overflow-hidden shadow-lg transform hover:scale-105 transition duration-300 w-60">
                <img src="{{ asset('images/tb-sccr.png') }}" alt="HQ"
                    class="w-full h-40 object-scale-down bg-white">
            </a>

            @module('00000')
                <!-- Card User Access -->
                <div class="flex items-end gap-1 ml-auto">
                    <a href="{{ route('dashboard.sso') }}"
                        class="relative rounded-2xl overflow-hidden shadow-lg transform hover:scale-105 transition duration-300">
                        <img src="{{ asset('images/tb-access.png') }}" alt="User Access"
                            class="w-full h-40 object-scale-down bg-white">
                    </a>
                </div>
            @endmodule
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-5 gap-6">

            <!-- Card Clinic -->
            {{-- <a href="{{ route('dashboard.clinic') }}" --}}
            <a href="{{ route('dashboard.hq') }}"
                class="relative rounded-2xl overflow-hidden shadow-lg transform hover:scale-105 transition duration-300">
                <img src="{{ asset('images/tb-clinic.png') }}" alt="Clinic"
                    class="w-full h-40 object-scale-down bg-white">
            </a>

            <!-- Card Resort -->
            {{-- <a href="{{ route('dashboard.hq') }}" --}}
            <a href="{{ route('dashboard.resort') }}"
                class="relative rounded-2xl overflow-hidden shadow-lg transform hover:scale-105 transition duration-300">
                <img src="{{ asset('images/tb-resort.png') }}" alt="Resort"
                    class="w-full h-40 object-scale-down bg-white">
            </a>

            <!-- Card Resto -->
            <a href="{{ route('dashboard.resto') }}"
            <a href="{{ route('dashboard.hq') }}"
                class="relative rounded-2xl overflow-hidden shadow-lg transform hover:scale-105 transition duration-300">
                <img src="{{ asset('images/tb-resto.png') }}" alt="Resto"
                    class="w-full h-40 object-scale-down bg-white">
            </a>

            <!-- Card Farm -->
            {{-- <a href="{{ route('dashboard.farm') }}" --}}
            <a href="{{ route('dashboard.hq') }}"
                class="relative rounded-2xl overflow-hidden shadow-lg transform hover:scale-105 transition duration-300">
                <img src="{{ asset('images/tb-farm.png') }}" alt="Resto"
                    class="w-full h-40 object-scale-down bg-white">
            </a>

            <!-- Card Campus -->
            <a href="{{ route('dashboard.campus') }}" {{-- <a href="{{ route('dashboard.hq') }}" --}}
                class="relative rounded-2xl overflow-hidden shadow-lg transform hover:scale-105 transition duration-300">
                <img src="{{ asset('images/tb-campus.png') }}" alt="Resto"
                    class="w-full h-40 object-scale-down bg-white">
            </a>


        </div>
    </div>
</div>
