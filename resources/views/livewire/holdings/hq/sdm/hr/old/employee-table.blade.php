{{-- <x-sccr-card wire:key="employee-table"> --}}
<x-ui.sccr-card wire:key="employee-table" transparent>

    {{-- Header --}}
    {{-- <div class="relative py-4 bg-yellow-400/80 backdrop-blur-md rounded-b-3xl shadow-lg text-black z-10"> --}}
    {{-- <div class="relative px-8 py-6 bg-yellow-400/70 rounded-b-3xl shadow-md z-10"> --}}
    <div class="relative px-8 py-1 bg-yellow-500/60 rounded-b-3xl shadow-lg overflow-hidden">

        <div class="flex justify-between items-center px-4">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold">Employee Data</h1>
                <p class="text-lg">Kelola data karyawan</p>
            </div>

            <div class="flex items-center space-x-3">
                {{-- <a href="{{ route('holdings.hq.sdm.hr.employee-create') }}">
                    <x-sccr-button variant="primary">Tambah Data</x-sccr-button>
                </a> --}}

                <x-sccr-button wire:click="openCreateModal" variant="primary">
                    Tambah Data
                </x-sccr-button>
                <x-sccr-button wire:click="openCreateAddModal" variant="primary">
                    Quick Add
                </x-sccr-button>
            </div>
        </div>

        <div class="px-4 flex justify-between items-center">
            <div class="text-sm">
                <x-ui.sccr-breadcrumb :items="[
                    ['label' => 'Main Dashboard', 'url' => route('dashboard')],
                    ['label' => 'Holding HQ', 'url' => route('dashboard.hq')],
                    ['label' => 'SDM', 'url' => route('dashboard.sdm')],
                    ['label' => 'HR', 'url' => route('dashboard.hr')],
                    ['label' => 'Employee', 'url' => route('holdings.hq.sdm.hr.employee-table')],
                ]" />
            </div>

            <div class="text-sm">
                Menampilkan <span class="font-semibold"> {{ $employees->total() }} </span> data karyawan
            </div>
        </div>
    </div>

    {{-- Success/Error Messages --}}
    @if (session()->has('message'))
        <div class="mx-4 mt-4 p-3 bg-green-100 text-green-700 rounded">
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mx-4 mt-4 p-3 bg-red-100 text-red-700 rounded">
            {{ session('error') }}
        </div>
    @endif

    <form wire:submit.prevent="applyFilter" class="flex flex-wrap items-center justify-between px-4 py-2 gap-3">

        {{-- LEFT: Search + filters --}}
        <div class="flex flex-wrap items-center gap-3 flex-1">

            <x-ui.sccr-input name="search" wire:model.blur="search" placeholder="Cari Nama, NIP, Departemen"
                class="w-64" />

            <x-ui.sccr-select name="holding" wire:model.defer="holding" :options="$holdingOptions" class="w-40" />

            <x-ui.sccr-select name="position" wire:model.defer="position" :options="$positions" class="w-40" />

            <x-ui.sccr-date name="tanggal_join" wire:model.defer="tanggal_join" class="w-40"
                placeholder="dd/mm/yyyy" />

            <x-ui.sccr-button type="submit" variant="primary">Cari</x-ui.sccr-button>

            <x-ui.sccr-button type="button" wire:click="clearFilters" variant="secondary">
                Clear Query
            </x-ui.sccr-button>

            <x-ui.sccr-button type="button" wire:click="exportFiltered" variant="success"
                class="bg-green-600 hover:bg-green-700 text-white">
                📊 Export Filtered
            </x-ui.sccr-button>

            <x-ui.sccr-button type="button" wire:click="exportSelected" variant="info"
                class="bg-blue-600 hover:bg-blue-700 text-white" :disabled="count($selectedEmployees) === 0">
                📥 Export Selected ({{ count($selectedEmployees) }})
            </x-ui.sccr-button>
        </div>

        {{-- RIGHT: Per halaman --}}
        <div class="flex flex-col items-start w-20">
            <span class="text-sm pb-1">Show Data:</span>
            <x-ui.sccr-select name="perPage" wire:model="perPage" :options="[10 => '10', 25 => '25', 50 => '50', 100 => '100']" class="w-full text-sm py-1" />
        </div>

    </form>

    {{-- Table --}}
    <div class="overflow-x-auto bg-white shadow-md rounded-lg mx-4">
        {{-- <table class="min-w-full divide-y divide-gray-200"> --}}
        <table class="min-w-full divide-y">
            <thead class="bg-gray-50">
                <tr>
                    {{-- Checkbox Column --}}
                    <th class="px-4 py-2 text-center w-12">
                        <input type="checkbox" wire:model.live="selectAll" aria-label="Select all on page"
                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    </th>

                    {{-- Sortable: NIP --}}
                    <th wire:click="sortBy('nip')" class="px-4 py-2 text-left cursor-pointer hover:bg-gray-100">
                        <div class="flex items-center gap-2">
                            <span>NIP</span>
                            @if ($sortField === 'nip')
                                <span class="text-xs">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                            @endif
                        </div>
                    </th>

                    {{-- Sortable: Nama --}}
                    <th wire:click="sortBy('nama')" class="px-4 py-2 text-left cursor-pointer hover:bg-gray-100">
                        <div class="flex items-center gap-2">
                            <span>Nama</span>
                            @if ($sortField === 'nama')
                                <span class="text-xs">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                            @endif
                        </div>
                    </th>

                    {{-- Sortable: Holding --}}
                    <th wire:click="sortBy('holding_id')" class="px-4 py-2 text-left cursor-pointer hover:bg-gray-100">
                        <div class="flex items-center gap-2">
                            <span>Holding</span>
                            @if ($sortField === 'holding_id')
                                <span class="text-xs">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                            @endif
                        </div>
                    </th>

                    {{-- Sortable: Posisi --}}
                    <th wire:click="sortBy('position_id')" class="px-4 py-2 text-left cursor-pointer hover:bg-gray-100">
                        <div class="flex items-center gap-2">
                            <span>Posisi</span>
                            @if ($sortField === 'position_id')
                                <span class="text-xs">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                            @endif
                        </div>
                    </th>

                    {{-- Sortable: Tanggal Join --}}
                    <th wire:click="sortBy('tanggal_join')"
                        class="px-4 py-2 text-left cursor-pointer hover:bg-gray-100">
                        <div class="flex items-center gap-2">
                            <span>Tanggal Join</span>
                            @if ($sortField === 'tanggal_join')
                                <span class="text-xs">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                            @endif
                        </div>
                    </th>

                    {{-- Non-sortable: Aksi --}}
                    <th class="px-4 py-2 text-center">Aksi</th>
                </tr>
            </thead>
            {{-- <tbody class="divide-y divide-gray-200"> --}}
            <tbody class="divide-y ">
                @forelse($employees as $employee)
                    <tr wire:key="employee-{{ $employee->nip }}" class="hover:bg-gray-50">
                        <td class="px-4 py-2 text-center">
                            <input type="checkbox" value="{{ (string) $employee->nip }}"
                                wire:model.live="selectedEmployees" aria-label="Select {{ $employee->nama }}"
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        </td>
                        <td class="px-4 py-2">{{ $employee->nip }}</td>
                        <td class="px-4 py-2">{{ $employee->gelar_depan }}
                            {{ $employee->nama }}{{ $employee->gelar_belakang ? ', ' . $employee->gelar_belakang : '' }}
                        </td>
                        <td class="px-4 py-2">{{ optional($employee->holding)->name ?? '-' }}</td>
                        <td class="px-4 py-2">{{ optional($employee->position)->title ?? '-' }}</td>
                        <td class="px-4 py-2">
                            {{ $employee->tanggal_join ? \Carbon\Carbon::parse($employee->tanggal_join)->format('d-m-Y') : '-' }}
                        </td>
                        <td class="px-4 py-2 text-center">
                            <button wire:click.prevent="openModal('{{ $employee->nip }}','detail')" title="Detail"
                                class="text-blue-600 hover:text-blue-800">👁️</button>
                            <button wire:click.prevent="openModal('{{ $employee->nip }}','edit')" title="Edit"
                                class="ml-2 text-yellow-600 hover:text-yellow-800">✏️</button>
                            <button
                                onclick="confirm('Yakin ingin menghapus karyawan ini?') || event.stopImmediatePropagation()"
                                wire:click="deleteSingle('{{ $employee->nip }}')" title="Delete"
                                class="ml-2 text-red-600 hover:text-red-800">🗑️</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-4 text-center text-gray-500">Tidak ada data ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Selected count + pagination --}}
    <div class="mt-4 flex items-center justify-between px-4 pb-4  text-black">
        <div class="text-sm text-black">
            <span class="font-semibold">{{ count($selectedEmployees) }}</span> karyawan dipilih
            @if (count($selectedEmployees) > 0)
                <button type="button" wire:click="deleteSelected"
                    onclick="confirm('Hapus semua terpilih?') || event.stopImmediatePropagation()"
                    class="ml-3 text-sm text-red-600 hover:text-red-800 font-medium">🗑️ Hapus Terpilih</button>
            @endif
        </div>
        <div>{{ $employees->links() }}</div>
    </div>


    {{-- Modal Detail/Edit --}}
    @if ($showModal && $selectedEmployee)
        <x-ui.sccr-modal :show="$showModal" maxWidth="3xl">
            @if ($modalMode === 'detail')
                @include('livewire.holdings.hq.sdm.hr.employee-show', ['employee' => $selectedEmployee])
            @else
                @livewire('holdings.hq.sdm.hr.employee-edit', ['nip' => $selectedEmployee->nip], key('edit-' . $selectedEmployee->nip))
            @endif
        </x-ui.sccr-modal>
    @endif

    {{-- Modal Tambah --}}
    @if ($showCreateModal)
        <x-ui.sccr-modal :show="$showCreateModal" title="Tambah Data Karyawan" maxWidth="2xl">
            @livewire('holdings.hq.sdm.hr.employee-create', [], key('create-' . now()->timestamp))
        </x-ui.sccr-modal>
    @endif

    {{-- Modal Quick Add --}}
    @if ($showCreateAddModal)
        <x-ui.sccr-modal :show="$showCreateAddModal" title="Quick Add Karyawan" maxWidth="2xl">
            @livewire('holdings.hq.sdm.hr.employee-create-quick', [], key('quick-create-' . now()->timestamp))
        </x-ui.sccr-modal>
    @endif
</x-ui.sccr-card>
