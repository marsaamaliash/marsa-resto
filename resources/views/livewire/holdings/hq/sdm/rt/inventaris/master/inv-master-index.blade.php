{{-- <div class="w-full"> --}}
<x-ui.sccr-card transparent wire:key="inv-master-index">

    {{-- Header --}}
    <div class="relative px-8 py-6 bg-green-600/80 rounded-b-3xl shadow overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">Master Inventaris</h1>
                <p class="text-green-100 text-sm">
                    Truth pages master data inventaris (ERP-style)
                </p>
            </div>
        </div>

        <div class="mt-4 flex justify-between items-center text-sm">
            <x-ui.sccr-breadcrumb :items="[
                ['label' => 'Main Dashboard', 'route' => 'dashboard'],
                ['label' => 'Holding HQ', 'route' => 'dashboard.hq'],
                ['label' => 'SDM', 'route' => 'dashboard.sdm'],
                ['label' => 'Rumah Tangga', 'route' => 'dashboard.rt'],
                ['label' => 'Master Inventaris', 'route' => null],
            ]" />
        </div>
    </div>

    {{-- Grid cards --}}
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">

            @foreach ($cards as $c)
                @permission($c['perm'])
                    <a href="{{ route($c['route']) }}"
                        class="relative rounded-2xl overflow-hidden shadow-lg transform hover:scale-105 transition duration-300 w-full h-40">
                        <img src="{{ asset($c['img']) }}" onerror="this.src='{{ asset($c['fallback']) }}'"
                            alt="{{ $c['title'] }}" class="absolute inset-0 w-full h-full object-cover" />

                        <div class="absolute inset-0 bg-black/35"></div>

                        <div class="absolute bottom-3 left-3 right-3">
                            <div class="text-white font-black text-sm tracking-wide">{{ $c['title'] }}</div>
                            <div class="text-white/90 text-[11px]">{{ $c['desc'] }}</div>
                        </div>
                    </a>
                @endpermission
            @endforeach

        </div>
    </div>

</x-ui.sccr-card>
{{-- </div> --}}
