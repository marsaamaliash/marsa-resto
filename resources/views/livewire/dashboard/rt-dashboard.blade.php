<div>

    {{-- ================= HEADER ================= --}}
    <div class="relative px-8 py-6 bg-yellow-500/60 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold mb-2">Holding HQ - SDM - Rumah Tangga</h1>
                <p class="text-lg text-gray-800">Silakan pilih modul RT yang ingin diakses</p>
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

            {{-- ===================== INVENTARIS (MODULE 01005) ===================== --}}
            @module('01005')
                {{-- Transaksi Inventaris --}}
                <a href="{{ route('holdings.hq.sdm.rt.inventaris.inventaris-table') }}"
                    class="relative rounded-2xl overflow-hidden shadow-lg transform hover:scale-105 transition duration-300 w-60 h-40">
                    <img src="{{ asset('images/tb-sdm-ga-inv.png') }}" alt="Inventaris"
                        class="absolute inset-0 w-full h-full object-cover" />
                    <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-10 transition"></div>
                </a>

                {{-- Outstanding Delete Inventaris (Transaksi) --}}
                @permission('INV_DELETE_APPROVE')
                    <a href="{{ route('holdings.hq.sdm.rt.inventaris.inventaris-delete-outstanding') }}"
                        class="relative rounded-2xl overflow-hidden shadow-lg transform hover:scale-105 transition duration-300 w-60 h-40">
                        <img src="{{ asset('images/tb-sdm-ga-inv-del.png') }}" alt="Delete Outstanding"
                            class="absolute inset-0 w-full h-full object-cover" />
                        <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-10 transition"></div>
                    </a>
                @endpermission

                {{-- ===================== MASTER (GROUP) ===================== --}}
                {{-- Card "Master" masuk ke Master Index (listing: holding/lokasi/ruangan/jenis) --}}
                @if (auth()->user()?->hasPermission('INV_MASTER_LOKASI_VIEW') ||
                        auth()->user()?->hasPermission('INV_MASTER_RUANGAN_VIEW') ||
                        auth()->user()?->hasPermission('INV_MASTER_JENIS_VIEW') ||
                        auth()->user()?->hasPermission('INV_MASTER_HOLDING_VIEW'))
                    <a href="{{ route('holdings.hq.sdm.rt.inventaris.master.index') }}"
                        class="relative rounded-2xl overflow-hidden shadow-lg transform hover:scale-105 transition duration-300 w-60 h-40">
                        <img src="{{ asset('images/tb-master.png') }}"
                            onerror="this.src='{{ asset('images/tb-sdm-ga-inv.png') }}'" alt="Master Inventaris"
                            class="absolute inset-0 w-full h-full object-cover" />
                        <div class="absolute inset-0 bg-black/30"></div>
                        <div class="absolute bottom-3 left-3 right-3">
                            <div class="text-white font-black text-sm tracking-wide">MASTER INVENTARIS</div>
                            <div class="text-white/90 text-[11px]">Truth Pages (CRUD + Approval)</div>
                        </div>
                    </a>
                @endif

                {{-- ===================== MASTER LOKASI (TRUTH PAGE) ===================== --}}
                @permission('INV_MASTER_LOKASI_VIEW')
                    <a href="{{ route('holdings.hq.sdm.rt.inventaris.master.lokasi.table') }}"
                        class="relative rounded-2xl overflow-hidden shadow-lg transform hover:scale-105 transition duration-300 w-60 h-40">
                        <img src="{{ asset('images/tb-sdm-ga-inv-master-lokasi.png') }}"
                            onerror="this.src='{{ asset('images/tb-master.png') }}'" alt="Master Lokasi"
                            class="absolute inset-0 w-full h-full object-cover" />
                        <div class="absolute inset-0 bg-black/25"></div>
                        <div class="absolute bottom-3 left-3 right-3">
                            <div class="text-white font-black text-sm tracking-wide">MASTER LOKASI</div>
                            <div class="text-white/90 text-[11px]">Table + Overlay CRUD</div>
                        </div>
                    </a>
                @endpermission

                {{-- Outstanding Approval Master Lokasi --}}
                @permission('INV_MASTER_LOKASI_DELETE_APPROVE')
                    <a href="{{ route('holdings.hq.sdm.rt.inventaris.master.lokasi.delete-outstanding') }}"
                        class="relative rounded-2xl overflow-hidden shadow-lg transform hover:scale-105 transition duration-300 w-60 h-40">
                        <img src="{{ asset('images/tb-sdm-ga-inv-master-lokasi-outstanding.png') }}"
                            onerror="this.src='{{ asset('images/tb-sdm-ga-inv-del.png') }}'" alt="Outstanding Master Lokasi"
                            class="absolute inset-0 w-full h-full object-cover" />
                        <div class="absolute inset-0 bg-black/25"></div>
                        <div class="absolute bottom-3 left-3 right-3">
                            <div class="text-white font-black text-sm tracking-wide">OUTSTANDING MASTER LOKASI</div>
                            <div class="text-white/90 text-[11px]">Approve / Reject Request Delete</div>
                        </div>
                    </a>
                @endpermission

                {{-- ===== NEXT (tinggal clone) =====
            @permission('INV_MASTER_RUANGAN_VIEW')
                <a href="{{ route('holdings.hq.sdm.rt.inventaris.master.ruangan.table') }}" ...>MASTER RUANGAN</a>
            @endpermission

            @permission('INV_MASTER_JENIS_VIEW')
                <a href="{{ route('holdings.hq.sdm.rt.inventaris.master.jenis.table') }}" ...>MASTER JENIS</a>
            @endpermission
            --}}
            @endmodule
        </div>
    </div>

</div>
