<x-ui.sccr-card transparent wire:key="fin-master-account-table">

    {{-- ================= HEADER ================= --}}
    <div class="relative px-8 py-6 bg-gray-700/80 rounded-b-3xl shadow overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">Master Chart of Account 📚</h1>
                {{-- <p class="text-green-100 text-sm">Truth Master Page — CRUD + Request Delete (Approval)</p> --}}
            </div>
        </div>

        <div class="mt-4 flex justify-between items-center text-sm">
            <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
            <div class="text-white">
                Menampilkan <span class="font-bold text-green-200">{{ $rows->total() }}</span> data
            </div>
        </div>
    </div>

    {{-- ================= FILTERS & ACTIONS ================= --}}
    <div class="px-4 pt-8 pb-2">
        <div class="flex flex-wrap items-center justify-between gap-3">

            <form wire:submit.prevent="applyFilter" class="flex flex-wrap items-center gap-3 flex-grow">
                <div class="relative top-1">
                    <span class="absolute -top-5 left-1 text-[10px] font-bold text-green-700 uppercase">
                        Cari Holding / CoA Code / Nama / Tipe
                    </span>
                    <x-ui.sccr-input name="search" wire:model="search" placeholder="Ketik lalu enter..."
                        class="w-64" />
                </div>

                <div class="relative top-1">
                    <span class="absolute -top-5 left-1 text-[10px] font-bold text-green-700 uppercase">Holding</span>
                    <x-ui.sccr-select name="filterHolding" wire:model.live="filterHolding" :options="$holdingOptions"
                        class="w-44" />
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <x-ui.sccr-button type="submit" variant="primary"
                        class="bg-blue-600/70 text-blue-700 hover:bg-blue-400">
                        <x-ui.sccr-icon name="cari" :size="20" />
                        Cari
                    </x-ui.sccr-button>

                    <x-ui.sccr-button type="button" wire:click="clearFilters"
                        class="bg-gray-600/70 text-gray-700 hover:bg-gray-400">
                        <x-ui.sccr-icon name="clear" :size="20" />
                        Clear
                    </x-ui.sccr-button>

                    <x-ui.sccr-button type="button" wire:click="exportFiltered" variant="success"
                        class="bg-emerald-600/70 hover:bg-emerald-700 text-white">
                        <x-ui.sccr-icon name="exportfiltered" :size="20" />Export Filtered
                    </x-ui.sccr-button>

                    <x-ui.sccr-button type="button" wire:click="exportSelected" variant="info"
                        class="bg-blue-600/70 hover:bg-blue-700 text-white" :disabled="count($selected) === 0">
                        <x-ui.sccr-icon name="exportselected" :size="20" />
                        Export Selected ({{ count($selected) }})
                    </x-ui.sccr-button>

                    @if ($canDelete)
                        <x-ui.sccr-button type="button" wire:click="openDeleteRequestSelected" variant="danger"
                            class="bg-red-600/70 hover:bg-red-700 text-white" title="Ajukan hapus untuk item terpilih"
                            :disabled="count($selected) === 0">
                            <span class="inline-flex items-center gap-2">
                                <x-ui.sccr-icon name="trash" :size="18" />
                                Request Delete ({{ count($selected) }})
                            </span>
                        </x-ui.sccr-button>
                    @endif
                </div>
            </form>

            {{-- Right: perpage --}}
            <div class="flex items-end gap-3 ml-auto">
                <div class="relative top-1">
                    <span class="absolute -top-5 left-1 text-[10px] font-bold text-green-700 uppercase">Show:</span>
                    <select wire:model.live="perPage" class="border-gray-300 rounded-md text-sm">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>

        </div>
    </div>

    {{-- ================= TABLE ================= --}}
    <div class="mx-6 rounded-xl shadow border overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-700/80 text-white">
                <tr>
                    <th class="px-4 py-3 text-center w-10">
                        <input type="checkbox" wire:model.live="selectAll" class="rounded border-gray-300">
                    </th>

                    <th wire:click="sortBy('holding_name')"
                        class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                        Holding {!! $sortField === 'holding_name' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                    </th>

                    <th wire:click="sortBy('department_name')"
                        class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                        Department {!! $sortField === 'department_name' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                    </th>

                    <th wire:click="sortBy('division_name')"
                        class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                        Division {!! $sortField === 'division_name' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                    </th>

                    <th wire:click="sortBy('code')" class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                        CoA Code {!! $sortField === 'code' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                    </th>

                    <th wire:click="sortBy('name')" class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                        Nama Akun {!! $sortField === 'name' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                    </th>

                    <th wire:click="sortBy('type')" class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                        Tipe {!! $sortField === 'type' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                    </th>

                    <th wire:click="sortBy('is_active')" class="px-4 py-3 text-center text-xs font-bold cursor-pointer">
                        Active {!! $sortField === 'is_active' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                    </th>

                    <th wire:click="sortBy('status')" class="px-4 py-3 text-center text-xs font-bold cursor-pointer">
                        Status {!! $sortField === 'status' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                    </th>

                    <th wire:click="sortBy('requested_at')"
                        class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                        Requested {!! $sortField === 'requested_at' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                    </th>

                    <th class="px-4 py-3 text-center text-xs font-bold">
                        <div class="flex items-center justify-center gap-2">
                            <span>Aksi</span>

                            @if ($canCreate && $canWrite)
                                <x-ui.sccr-button type="button" variant="icon-circle" wire:click="openCreate"
                                    class="w-8 h-8 hover:scale-105" title="Tambah Master CoA">
                                    <x-ui.sccr-icon name="plus" :size="18" />
                                </x-ui.sccr-button>
                            @endif
                        </div>
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 bg-gray-100">
                @forelse ($rows as $r)
                    @php
                        $key = (string) $r->id; // Wajib pakai id
                        $active = (int) ($r->is_active ?? 0) === 1;
                        $status = (string) ($r->status ?? '');
                    @endphp

                    <tr class="hover:bg-gray-200 transition" wire:key="fin-account-row-{{ $key }}">
                        <td class="px-4 py-2 text-center">
                            <input type="checkbox" value="{{ $key }}" wire:model.live="selected"
                                class="rounded border-gray-300">
                        </td>

                        <td class="px-4 py-2 text-xs">
                            <div class="font-semibold">{{ $r->holding_name ?? '-' }}</div>
                            {{-- <div class="text-gray-500">Holding ID: {{ $r->holding_id ?? '-' }}</div> --}}
                        </td>

                        <td class="px-4 py-2 text-xs">
                            <div class="font-semibold">{{ $r->department_name ?? '-' }}</div>
                            {{-- <div class="text-gray-500">Dept ID: {{ $r->department_id ?? '-' }}</div> --}}
                        </td>

                        <td class="px-4 py-2 text-xs">
                            <div class="font-semibold">{{ $r->division_name ?? '-' }}</div>
                            {{-- <div class="text-gray-500">Div ID: {{ $r->division_id ?? '-' }}</div> --}}
                        </td>

                        <td class="px-4 py-2 font-mono text-sm font-semibold">
                            {{ $r->code ?? '-' }}
                        </td>

                        <td class="px-4 py-2 text-sm">
                            {{ $r->name ?? '-' }}
                        </td>

                        <td class="px-4 py-2 text-sm">
                            {{ $r->type ?? '-' }}
                        </td>

                        <td class="px-4 py-2 text-center">
                            <span
                                class="px-2 py-1 rounded-full text-xs font-bold {{ $active ? 'bg-green-200 text-green-900' : 'bg-gray-300 text-gray-700' }}">
                                {{ $active ? 'ACTIVE' : 'INACTIVE' }}
                            </span>
                        </td>

                        <td class="px-4 py-2 text-center">
                            <span
                                class="px-2 py-1 rounded-full text-xs font-bold
                            {{ $status === 'approved' ? 'bg-emerald-200 text-emerald-900' : '' }}
                            {{ $status === 'pending' ? 'bg-yellow-200 text-yellow-900' : '' }}
                            {{ $status === 'rejected' ? 'bg-red-200 text-red-900' : '' }}
                            {{ $status !== 'approved' && $status !== 'pending' && $status !== 'rejected' ? 'bg-gray-200 text-gray-800' : '' }}
                        ">
                                {{ $status !== '' ? strtoupper($status) : '-' }}
                            </span>
                        </td>

                        <td class="px-4 py-2 text-xs text-gray-700">
                            {{ $r->requested_at ? \Illuminate\Support\Carbon::parse($r->requested_at)->format('d M Y H:i') : '-' }}
                        </td>

                        <td class="px-4 py-2 text-center">
                            <div class="flex justify-center gap-3">
                                <x-ui.sccr-button type="button" variant="icon"
                                    wire:click="openShow(@js($key))"
                                    class="text-gray-700 hover:scale-125" title="Detail">
                                    <x-ui.sccr-icon name="eye" :size="20" />
                                </x-ui.sccr-button>

                                @if ($canUpdate)
                                    <x-ui.sccr-button type="button" variant="icon"
                                        wire:click="openEdit(@js($key))"
                                        class="text-blue-600 hover:scale-125" title="Edit">
                                        <x-ui.sccr-icon name="edit" :size="20" />
                                    </x-ui.sccr-button>
                                @endif

                                @if ($canDelete)
                                    <x-ui.sccr-button type="button" variant="icon"
                                        wire:click="openDeleteRequestSingle(@js($key))"
                                        class="text-red-600 hover:scale-125" title="Request Delete (Approval)">
                                        <x-ui.sccr-icon name="trash" :size="20" />
                                    </x-ui.sccr-button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="py-10 text-center text-gray-400 italic">
                            Data tidak ditemukan
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>


        {{-- ================= DELETE REQUEST MODAL ================= --}}
        @if ($showConfirmModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
                <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Konfirmasi Hapus (Approval)</h3>
                            <p class="text-xs text-gray-500 mt-1">
                                Data tidak langsung dihapus. Permintaan masuk ke antrian approval.
                            </p>
                        </div>

                        <x-ui.sccr-button type="button" variant="icon" wire:click="cancelDeleteRequest"
                            class="text-gray-500 hover:text-gray-800" title="Tutup">
                            <span class="text-xl leading-none">×</span>
                        </x-ui.sccr-button>
                    </div>

                    <div class="mt-4">
                        <label class="text-sm font-bold text-gray-700">Alasan Hapus</label>
                        <textarea wire:model.live="deleteReason" class="w-full border-gray-300 rounded-lg text-sm mt-1" rows="3"
                            maxlength="255" placeholder="Contoh: salah input / duplikasi / tidak dipakai"></textarea>
                        <div class="text-[11px] text-gray-500 mt-1">Maks 255 karakter.</div>
                    </div>

                    <div class="mt-4 text-xs text-gray-700">
                        @if ($isBulkDelete)
                            <div>Target: <b>{{ count($selected) }}</b> item terpilih</div>
                        @else
                            <div>Target ID: <b>{{ $confirmingKey }}</b></div>
                        @endif
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <x-ui.sccr-button type="button" variant="secondary" wire:click="cancelDeleteRequest">
                            Batal
                        </x-ui.sccr-button>

                        <x-ui.sccr-button type="button" variant="danger" wire:click="submitDeleteRequest">
                            Kirim Permintaan Hapus
                        </x-ui.sccr-button>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- ================= FOOTER ================= --}}
    <div class="px-6 py-4 flex flex-col md:flex-row justify-between items-center gap-3">
        <div class="text-sm text-gray-600 flex items-center">
            <span class="font-bold text-gray-800 mr-1">{{ count($selected) }}</span> item dipilih
        </div>

        <div>
            {{ $rows->links() }}
        </div>
    </div>

    {{-- ================= TOAST ================= --}}
    {{-- Hindari microtime() kalau bisa (key jadi selalu berubah). Idealnya pakai $toast['key'] dari component --}}
    <x-ui.sccr-toast :show="$toast['show']" :type="$toast['type']" :message="$toast['message']" />

    {{-- ================= OVERLAYS ================= --}}
    @if ($overlayMode === 'create')
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="closeOverlay"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center px-6">
            <div class="w-full max-w-4xl bg-white rounded-2xl shadow-2xl relative">
                <x-ui.sccr-button type="button" variant="icon" wire:click="closeOverlay"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500" title="Tutup">
                    <span class="text-xl leading-none">✕</span>
                </x-ui.sccr-button>

                <livewire:holdings.hq.finance.master.fin-master-account-create wire:key="fin-master-account-create" />
            </div>
        </div>
    @endif

    @if ($overlayMode === 'show' && $overlayKey)
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="closeOverlay"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center px-6">
            <div class="w-full max-w-4xl bg-white rounded-2xl shadow-2xl relative">
                <x-ui.sccr-button type="button" variant="icon" wire:click="closeOverlay"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500" title="Tutup">
                    <span class="text-xl leading-none">✕</span>
                </x-ui.sccr-button>

                <livewire:holdings.hq.finance.master.fin-master-account-show :rowKey="$overlayKey"
                    wire:key="fin-master-account-show-{{ $overlayKey }}" />
            </div>
        </div>
    @endif

    @if ($overlayMode === 'edit' && $overlayKey)
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="closeOverlay"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center px-6">
            <div class="w-full max-w-4xl bg-white rounded-2xl shadow-2xl relative">
                <x-ui.sccr-button type="button" variant="icon" wire:click="closeOverlay"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500" title="Tutup">
                    <span class="text-xl leading-none">✕</span>
                </x-ui.sccr-button>

                <livewire:holdings.hq.finance.master.fin-master-account-edit :rowKey="$overlayKey"
                    wire:key="fin-master-account-edit-{{ $overlayKey }}" />
            </div>
        </div>
    @endif

</x-ui.sccr-card>
