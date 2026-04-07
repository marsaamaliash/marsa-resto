<x-ui.sccr-card transparent wire:key="emp-employee-table" class="h-full min-h-0 flex flex-col">

    {{-- ================= HEADER ================= --}}
    <div class="relative px-8 py-6 bg-yellow-500/60 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">Employee Data👨‍🔬</h1>
                <p class="text-green-100 text-sm">
                    Kelola data karyawan
                </p>
            </div>
        </div>

        <div class="mt-4 flex justify-between items-center text-sm">
            <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
            <div class="text-white">
                Menampilkan <span class="font-bold text-black">{{ $rows->total() }}</span> data 📦
            </div>
        </div>
    </div>


    {{-- ================= FILTERS & ACTIONS ================= --}}
    <div class="px-4 pt-3 pb-1">
        <div class="flex flex-wrap items-center justify-between gap-1">

            <form wire:submit.prevent="applyFilter" class="flex flex-wrap items-center gap-1 flex-grow">
                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">
                        Cari NIP / Nama / Holding / Departemen / Divisi / Posisi
                    </span>
                    <div class="relative top-3 w-40">
                        <x-ui.sccr-input name="search" wire:model="search" placeholder="Ketik lalu enter..."
                            class="ext-sm h-10 px-1" />
                    </div>
                </div>

                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Holding</span>
                    <div class="relative top-3 w-40">
                        <x-ui.sccr-select name="filterHolding" wire:model.live="filterHolding" :options="$holdingOptions"
                            class="ext-sm h-10 px-1" />
                    </div>
                </div>

                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Position</span>
                    <div class="relative top-3 w-40">
                        <x-ui.sccr-select name="filterPosition" wire:model.live="filterPosition" :options="$positionOptions"
                            class="ext-sm h-10 px-1" />
                    </div>
                </div>

                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Status</span>
                    <div class="relative top-3 w-40">
                        <x-ui.sccr-select name="filterStatus" wire:model.live="filterStatus" :options="$statusOptions"
                            class="ext-sm h-10 px-1" />
                    </div>
                </div>

                <div class="relative top-1">
                    <span class="absolute -top-6 left-1 text-[10px] font-bold text-black uppercase">Join
                        Date</span>
                    <div class="relative top-1 w-30">
                        <input type="date" wire:model.live="filterJoinDate"
                            class="border-gray-300 rounded-md text-sm h-10 px-1" />
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-1">
                    <x-ui.sccr-button type="submit" variant="primary"
                        class="relative top-2 bg-gray-900 text-gray-100 hover:bg-gray-400">
                        <x-ui.sccr-icon name="cari" :size="20" />
                        Cari
                    </x-ui.sccr-button>

                    <x-ui.sccr-button type="button" wire:click="clearFilters"
                        class="relative top-2 bg-gray-800 text-gray-100 hover:bg-gray-400">
                        <x-ui.sccr-icon name="clear" :size="20" />
                        Clear
                    </x-ui.sccr-button>

                    <x-ui.sccr-button type="button" wire:click="exportFiltered" variant="success" {{-- class="bg-emerald-600/70 hover:bg-emerald-700 text-white"> --}}
                        class="relative top-2 bg-gray-600 text-gray-100 hover:bg-gray-400">
                        <x-ui.sccr-icon name="exportfiltered" :size="20" />
                        Export Filtered
                    </x-ui.sccr-button>

                    <x-ui.sccr-button type="button" wire:click="exportSelected" variant="info" {{-- class="bg-blue-600/70 hover:bg-blue-700 text-white" :disabled="count($selectedInventaris) === 0"> --}}
                        class="relative top-2 bg-gray-500 text-gray-900 hover:bg-gray-400" :disabled="count($selected) === 0">
                        <x-ui.sccr-icon name="exportselected" :size="20" />
                        Export Selected ({{ count($selected) }})
                    </x-ui.sccr-button>

                    @permission('EMP_DELETE')
                        <x-ui.sccr-button type="button" wire:click="openDeleteRequestSelected" variant="danger"
                            class="relative top-2 bg-red-600/70 hover:bg-red-700 text-white"
                            title="Ajukan hapus untuk item terpilih">
                            <span class="inline-flex items-center gap-2">
                                <x-ui.sccr-icon name="trash" :size="18" />
                                Request Delete
                            </span>
                        </x-ui.sccr-button>
                    @endpermission
                </div>
            </form>

            {{-- Right: perpage --}}
            <div class="flex items-end gap-1 ml-auto">
                <div class="relative top-0">
                    <span class="absolute -top-4 left-1 text-[10px] font-bold text-black uppercase">Show 📚</span>
                    <select wire:model.live="perPage" class="border-gray-300 rounded-md text-sm">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= TABLE (SCROLL AREA) ================= --}}
    <div class="flex-1 min-h-0 px-4 pb-2">
        <div class="h-full min-h-0 rounded-xl shadow border bg-white overflow-hidden flex flex-col">

            {{-- TABLE SCROLLER --}}
            <div class="flex-1 min-h-0 overflow-auto">
                <table class="min-w-full divide-y divide-gray-900">
                    <thead class="bg-gray-700/80 text-white sticky top-0 z-10">
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

                            <th wire:click="sortBy('nip')" class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                                NIP {!! $sortField === 'nip' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            <th wire:click="sortBy('nama')"
                                class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                                Nama {!! $sortField === 'nama' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            <th wire:click="sortBy('position_title')"
                                class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                                Position {!! $sortField === 'position_title' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            <th wire:click="sortBy('employee_status')"
                                class="px-4 py-3 text-center text-xs font-bold cursor-pointer">
                                Status {!! $sortField === 'employee_status' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            <th wire:click="sortBy('tanggal_join')"
                                class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                                Join Date {!! $sortField === 'tanggal_join' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            <th class="px-4 py-3 text-left text-xs font-bold min-w-[60px]">
                                Work Period
                            </th>

                            <th class="px-4 py-3 text-center text-xs font-bold">
                                <div class="flex items-center justify-center gap-2">
                                    <span>Aksi</span>

                                    @if ($canCreate && $canWrite)
                                        <x-ui.sccr-button type="button" variant="icon-circle"
                                            wire:click="openCreate" class="w-8 h-8 hover:scale-105"
                                            title="Tambah Employee">
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
                                $key = (string) ($r->nip ?? '');
                                $join = $r->tanggal_join
                                    ? \Illuminate\Support\Carbon::parse($r->tanggal_join)->format('d M Y')
                                    : '-';

                                $namaFull = trim(
                                    ((string) ($r->gelar_depan ?? '')) .
                                        ' ' .
                                        ((string) ($r->nama ?? '')) .
                                        ((string) ($r->gelar_belakang ? ', ' . $r->gelar_belakang : '')),
                                );

                                $wp = null;
                                if (!empty($r->tanggal_join)) {
                                    $from = \Illuminate\Support\Carbon::parse($r->tanggal_join)->startOfDay();
                                    $to = now()->startOfDay();
                                    $di = $from->diff($to);
                                    $wp = ['y' => (int) $di->y, 'm' => (int) $di->m, 'd' => (int) $di->d];
                                }
                            @endphp

                            <tr class="hover:bg-gray-200 transition"
                                wire:key="emp-row-{{ $key !== '' ? md5($key) : $loop->index }}">
                                <td class="px-4 py-2 text-center">
                                    <input type="checkbox" value="{{ $key }}" wire:model.live="selected"
                                        class="rounded border-gray-300">
                                </td>

                                <td class="px-4 py-2 text-xs">
                                    <div class="font-semibold">{{ $r->holding_name ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-2 text-xs">
                                    <div class="font-semibold">{{ $r->department_name ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-2 text-xs">
                                    <div class="font-semibold">{{ $r->division_name ?? '-' }}</div>
                                </td>

                                <td class="px-4 py-2 font-mono text-xs font-semibold">{{ $r->nip ?? '-' }}</td>

                                <td class="px-4 py-2 text-sm">
                                    {{ $namaFull !== '' ? $namaFull : '-' }}
                                    @if (!empty($r->job_title_name))
                                        <div class="text-[11px] text-gray-600">{{ $r->job_title_name }}</div>
                                    @endif
                                </td>

                                <td class="px-4 py-2 text-sm">{{ $r->position_title ?? '-' }}</td>

                                <td class="px-4 py-2 text-center">
                                    <span
                                        class="px-2 py-1 rounded-full text-xs font-bold
                                        {{ $r->employee_status === 'Karyawan Tetap' ? 'bg-green-200 text-green-900' : '' }}
                                        {{ $r->employee_status === 'PKWT' ? 'bg-yellow-200 text-yellow-900' : '' }}
                                        {{ $r->employee_status === 'RESIGN' ? 'bg-red-200 text-red-900' : '' }}">
                                        {{ $r->employee_status !== '' ? strtoupper($r->employee_status) : '-' }}
                                    </span>
                                </td>

                                <td class="px-4 py-2 text-xs text-gray-700">{{ $join }}</td>

                                <td class="px-4 py-2 text-[11px] align-top">
                                    @if ($wp)
                                        @php
                                            $yy = str_pad((string) ($wp['y'] ?? 0), 3, ' ', STR_PAD_LEFT);
                                            $mm = str_pad((string) ($wp['m'] ?? 0), 3, ' ', STR_PAD_LEFT);
                                            $dd = str_pad((string) ($wp['d'] ?? 0), 3, ' ', STR_PAD_LEFT);
                                        @endphp
                                        <pre class="m-0 p-0 min-w-[60px] leading-tight font-mono text-gray-700 whitespace-pre text-left">{{ $yy }} Year
{{ $mm }} Month
{{ $dd }} Day</pre>
                                    @else
                                        -
                                    @endif
                                </td>

                                <td class="px-4 py-2 text-center">
                                    <div class="flex justify-center gap-3">
                                        <x-ui.sccr-button type="button" variant="icon"
                                            wire:click="openShow('{{ $key }}')"
                                            class="text-gray-700 hover:scale-125" title="Detail">
                                            <x-ui.sccr-icon name="eye" :size="20" />
                                        </x-ui.sccr-button>

                                        @if ($canUpdate)
                                            <x-ui.sccr-button type="button" variant="icon"
                                                wire:click="openEdit('{{ $key }}')"
                                                class="text-blue-600 hover:scale-125" title="Edit">
                                                <x-ui.sccr-icon name="edit" :size="20" />
                                            </x-ui.sccr-button>
                                        @endif

                                        @if ($canDelete)
                                            <x-ui.sccr-button type="button" variant="icon"
                                                wire:click="openDeleteRequestSingle('{{ $key }}')"
                                                class="text-red-600 hover:scale-125"
                                                title="Request Delete (Approval)">
                                                <x-ui.sccr-icon name="trash" :size="20" />
                                            </x-ui.sccr-button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="py-10 text-center text-gray-400 italic">Data tidak ditemukan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- MODULE FOOTER (pagination) FIX --}}
            <div
                class="flex-none px-6 py-3 border-t bg-white flex flex-col md:flex-row justify-between items-center gap-3">
                <div class="text-sm text-gray-600 flex items-center">
                    <span class="font-bold text-gray-800 mr-1">{{ count($selected) }}</span> item dipilih

                    @if ($canDelete && count($selected) > 0)
                        <x-ui.sccr-button type="button" variant="danger" wire:click="openDeleteRequestSelected"
                            class="ml-4 h-[30px] px-3 text-xs bg-red-50 text-red-600 hover:bg-red-100 border border-red-200">
                            <span class="inline-flex items-center gap-2">
                                <x-ui.sccr-icon name="trash" :size="16" />
                                Request Delete Terpilih
                            </span>
                        </x-ui.sccr-button>
                    @endif
                </div>

                <div>
                    {{ $rows->links() }}
                </div>
            </div>

        </div>
    </div>

    {{-- ================= SOFT DELETE REQUEST MODAL (WAJIB agar tidak "diam") ================= --}}
    @if (!empty($showConfirmModal))
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Konfirmasi Hapus (Approval)</h3>
                        <p class="text-xs text-gray-500 mt-1">
                            Data tidak langsung dihapus. Permintaan akan masuk ke antrian approval Manager/Head.
                        </p>
                    </div>

                    <x-ui.sccr-button type="button" variant="icon" wire:click="cancelDeleteRequest"
                        class="text-gray-500 hover:text-gray-800" title="Tutup">
                        <span class="text-xl leading-none">×</span>
                    </x-ui.sccr-button>
                </div>

                <div class="mt-4 p-3 rounded-lg bg-yellow-50 border border-yellow-200 text-yellow-900 text-xs">
                    <div class="font-semibold mb-1">⚠️ Perhatian</div>
                    <ul class="list-disc ml-5 space-y-1">
                        <li>Status akan menjadi <b>pending_delete</b> setelah request dikirim.</li>
                        <li>Item akan hilang dari daftar aktif sampai approval diputuskan.</li>
                    </ul>
                </div>

                <div class="mt-4">
                    <label class="text-sm font-bold text-gray-700">Alasan Hapus</label>
                    <textarea wire:model.live="deleteReason" class="w-full border-gray-300 rounded-lg text-sm mt-1" rows="3"
                        placeholder="Contoh: duplikasi data / salah input / resign / dll"></textarea>
                    <div class="text-[11px] text-gray-500 mt-1">Maks 255 karakter.</div>
                </div>

                <div class="mt-4 text-xs text-gray-700">
                    @if (!empty($isBulkDelete))
                        <div>Target: <b>{{ count($selected) }}</b> item terpilih</div>
                    @else
                        <div>Target: <b>{{ $confirmingKey ?? '-' }}</b></div>
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

    {{-- ================= TOAST ================= --}}
    <x-ui.sccr-toast :show="$toast['show']" :type="$toast['type']" :message="$toast['message']" />

    {{-- ================= OVERLAYS ================= --}}
    @if ($overlayMode === 'create')
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="closeOverlay"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center px-6">
            <div class="w-full max-w-5xl bg-white rounded-2xl shadow-2xl relative">
                <x-ui.sccr-button type="button" variant="icon" wire:click="closeOverlay"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500" title="Tutup">
                    <span class="text-xl leading-none">✕</span>
                </x-ui.sccr-button>

                <livewire:holdings.hq.sdm.hr.employee-create wire:key="emp-employee-create" />
            </div>
        </div>
    @endif

    @if ($overlayMode === 'show' && $overlayKey)
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="closeOverlay"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center px-6">
            <div class="w-full max-w-5xl bg-white rounded-2xl shadow-2xl relative">
                <x-ui.sccr-button type="button" variant="icon" wire:click="closeOverlay"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500" title="Tutup">
                    <span class="text-xl leading-none">✕</span>
                </x-ui.sccr-button>

                <livewire:holdings.hq.sdm.hr.employee-show :nip="$overlayKey" :asOverlay="true"
                    wire:key="emp-employee-show-{{ md5($overlayKey) }}" />
            </div>
        </div>
    @endif

    @if ($overlayMode === 'edit' && $overlayKey)
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="closeOverlay"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center px-6">
            <div class="w-full max-w-5xl bg-white rounded-2xl shadow-2xl relative">
                <x-ui.sccr-button type="button" variant="icon" wire:click="closeOverlay"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500" title="Tutup">
                    <span class="text-xl leading-none">✕</span>
                </x-ui.sccr-button>

                <livewire:holdings.hq.sdm.hr.employee-edit :nip="$overlayKey" :asOverlay="true"
                    wire:key="emp-employee-edit-{{ md5($overlayKey) }}" />
            </div>
        </div>
    @endif

</x-ui.sccr-card>
