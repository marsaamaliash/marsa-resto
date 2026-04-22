<div>
    <div class="relative px-8 py-6 bg-yellow-500/60 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold mb-2">Recipe</h1>
                <p class="text-lg text-gray-800">Silakan Select modul Resto yang ingin diakses</p>
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
            <a href="{{ route('dashboard.resto.konVersion-Unit') }}"
                class="flex items-center justify-center rounded-2xl shadow-lg h-40 bg-green-500 hover:bg-green-600 transform hover:scale-105 transition duration-300">

                <span class="text-white text-lg font-semibold">
                    KonVersion Unit
                </span>
            </a>

            <a href="{{ route('dashboard.resto.repack') }}"
                class="flex items-center justify-center rounded-2xl shadow-lg h-40 bg-green-500 hover:bg-green-600 transform hover:scale-105 transition duration-300">

                <span class="text-white text-lg font-semibold">
                    Repack
                </span>
            </a>

             <a href="{{ route('dashboard.resto.Recipe-Menu') }}"
                class="flex items-center justify-center rounded-2xl shadow-lg h-40 bg-green-500 hover:bg-green-600 transform hover:scale-105 transition duration-300">

                <span class="text-white text-lg font-semibold">
                    Menu Recipe
                </span>
            </a>

             <a href="{{ route('dashboard.resto.Recipe.recipe') }}"
                class="flex items-center justify-center rounded-2xl shadow-lg h-40 bg-purple-500 hover:bg-purple-600 transform hover:scale-105 transition duration-300">

                <span class="text-white text-lg font-semibold">
                    Recipe / BOM
                </span>
            </a>

             <a href="{{ route('dashboard.resto.Recipe.production') }}"
                class="flex items-center justify-center rounded-2xl shadow-lg h-40 bg-emerald-500 hover:bg-emerald-600 transform hover:scale-105 transition duration-300">

                <span class="text-white text-lg font-semibold">
                    Production Order
                </span>
            </a>

        </div>
    </div>
</div>
