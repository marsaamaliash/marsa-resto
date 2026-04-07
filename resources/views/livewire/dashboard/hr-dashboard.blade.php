<div>
    {{-- ================= HEADER ================= --}}
    <div class="relative px-8 py-6 bg-yellow-500/60 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold mb-2">Holding HQ - SDM - HR</h1>
                <p class="text-lg text-gray-800">Silakan pilih modul HR yang ingin diakses</p>
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
        <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-5 gap-6 justify-center">

            @module('01001')
                @forelse ($tiles as $t)
                    <a wire:navigate href="{{ route($t['route']) }}"
                        class="relative rounded-2xl overflow-hidden shadow-lg transform hover:scale-105 transition duration-300 w-60 h-40">

                        @if (!empty($t['img']))
                            <img src="{{ asset($t['img']) }}" alt="{{ $t['label'] }}"
                                class="absolute inset-0 w-full h-full object-cover" />
                        @else
                            <div class="absolute inset-0 w-full h-full bg-gradient-to-br from-slate-800 to-slate-600"></div>
                        @endif

                        <div class="absolute inset-0 bg-black/10"></div>

                        <div class="absolute bottom-0 left-0 right-0 p-3">
                            <div
                                class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/80 text-slate-900 text-xs font-bold">
                                <span>{{ $t['icon'] }}</span>
                                <span class="truncate">{{ $t['label'] }}</span>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full text-center text-gray-600 italic py-10">
                        Tidak ada menu HR yang bisa diakses.
                    </div>
                @endforelse
            @endmodule

        </div>
    </div>
</div>
