<x-sccr-card wire:key="students-table" transparent>

    {{-- Header --}}
    <div class="relative px-8 py-1 bg-blue-500/60 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-center px-4">

            <div>
                <h1 class="text-3xl md:text-4xl font-bold">Student Data</h1>
                <p class="text-lg">Kelola data mahasiswa</p>
            </div>

            <div class="flex items-center space-x-3">
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
                <x-sccr-breadcrumb :items="[
                    ['label' => 'Main Dashboard', 'url' => route('dashboard')],
                    ['label' => 'Campus', 'url' => route('dashboard.campus')],
                    ['label' => 'SIAKAD', 'url' => route('dashboard.siakad')],
                    ['label' => 'Students', 'url' => route('holdings.campus.siakad.student')],
                ]" />
            </div>

            <div class="text-sm">
                Menampilkan <span class="font-semibold">{{ $students->total() }}</span> data mahasiswa
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
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


    {{-- Search + Filters --}}
    <form wire:submit.prevent="applyFilter" class="flex flex-wrap items-center justify-between px-4 py-2 gap-3">

        <div class="flex flex-wrap items-center gap-3 flex-1">

            <x-sccr-input name="search" wire:model.blur="search" placeholder="Cari Nama, NIM, Prodi, Fakultas"
                class="w-64" />

            {{-- Filter Fakultas --}}
            <x-sccr-select name="fakultas_id" wire:model.defer="fakultas_id" :options="$faculties" label="Fakultas"
                class="w-40" />

            {{-- Filter Prodi --}}
            <x-sccr-select name="prodi_id" wire:model.defer="prodi_id" :options="$studyPrograms" label="Prodi"
                class="w-40" />

            {{-- Tahun Masuk --}}
            <x-sccr-select name="tahun_masuk" wire:model.defer="tahun_masuk" :options="$entryYearOptions" label="Tahun Masuk"
                class="w-32" />

            {{-- Status --}}
            <x-sccr-select name="student_status" wire:model.defer="student_status" :options="$statusOptions" class="w-32" />

            <x-sccr-button type="submit" variant="primary">Cari</x-sccr-button>

            <x-sccr-button type="button" wire:click="clearFilters" variant="secondary">
                Clear Query
            </x-sccr-button>

            <x-sccr-button type="button" wire:click="exportFiltered" variant="success"
                class="bg-green-600 hover:bg-green-700 text-white">
                📊 Export Filtered
            </x-sccr-button>

            <x-sccr-button type="button" wire:click="exportSelected" variant="info"
                class="bg-blue-600 hover:bg-blue-700 text-white" :disabled="count($selectedStudents) === 0">
                📥 Export Selected ({{ count($selectedStudents) }})
            </x-sccr-button>

        </div>

        {{-- Per halaman --}}
        <div class="flex flex-col items-start w-20">
            <span class="text-sm pb-1">Show Data:</span>
            <x-sccr-select name="perPage" wire:model="perPage" :options="[10 => '10', 25 => '25', 50 => '50', 100 => '100']" class="w-full text-sm py-1" />
        </div>

    </form>


    {{-- Table --}}
    <div class="overflow-x-auto bg-white shadow-md rounded-lg mx-4">

        <table class="min-w-full divide-y">
            <thead class="bg-gray-50">
                <tr>

                    {{-- Checkbox --}}
                    <th class="px-4 py-2 text-center w-12">
                        <input type="checkbox" wire:model.live="selectAll"
                            class="w-4 h-4 text-blue-600 border-gray-300 rounded">
                    </th>

                    {{-- NIM --}}
                    <th wire:click="sortBy('nim')" class="px-4 py-2 text-left cursor-pointer hover:bg-gray-100">
                        <div class="flex items-center gap-2">
                            <span>NIM</span>
                            @if ($sortField === 'nim')
                                <span class="text-xs">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                            @endif
                        </div>
                    </th>

                    {{-- Nama --}}
                    <th wire:click="sortBy('nama_lengkap')" class="px-4 py-2 cursor-pointer hover:bg-gray-100">
                        <div class="flex items-center gap-2">
                            <span>Nama</span>
                            @if ($sortField === 'nama_lengkap')
                                <span class="text-xs">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                            @endif
                        </div>
                    </th>

                    {{-- Prodi --}}
                    <th wire:click="sortBy('prodi_id')" class="px-4 py-2 cursor-pointer hover:bg-gray-100">
                        <div class="flex items-center gap-2">
                            <span>Program Studi</span>
                            @if ($sortField === 'prodi_id')
                                <span class="text-xs">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                            @endif
                        </div>
                    </th>

                    {{-- Tahun Masuk --}}
                    <th wire:click="sortBy('tahun_masuk')" class="px-4 py-2 cursor-pointer hover:bg-gray-100">
                        <div class="flex items-center gap-2">
                            <span>Tahun Masuk</span>
                            @if ($sortField === 'tahun_masuk')
                                <span class="text-xs">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                            @endif
                        </div>
                    </th>

                    {{-- Status --}}
                    <th class="px-4 py-2 text-left">Status</th>

                    {{-- Aksi --}}
                    <th class="px-4 py-2 text-center">Aksi</th>

                </tr>
            </thead>

            <tbody class="divide-y">
                @forelse($students as $student)
                    <tr wire:key="student-{{ $student->id }}" class="hover:bg-gray-50">

                        <td class="px-4 py-2 text-center">
                            <input type="checkbox" value="{{ (string) $student->id }}"
                                wire:model.live="selectedStudents"
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded">
                        </td>

                        <td class="px-4 py-2">{{ $student->nim }}</td>

                        <td class="px-4 py-2">{{ $student->nama_lengkap }}</td>

                        <td class="px-4 py-2">{{ optional($student->prodi)->program_name ?? '-' }}</td>

                        <td class="px-4 py-2">{{ $student->tahun_masuk }}</td>

                        <td class="px-4 py-2">{{ ucfirst($student->student_status) }}</td>

                        <td class="px-4 py-2 text-center">

                            <button wire:click.prevent="openModal({{ $student->id }},'detail')"
                                class="text-blue-600 hover:text-blue-800">👁️</button>

                            <button wire:click.prevent="openModal({{ $student->id }},'edit')"
                                class="ml-2 text-yellow-600 hover:text-yellow-800">✏️</button>

                            <button
                                onclick="confirm('Yakin ingin menghapus mahasiswa ini?') || event.stopImmediatePropagation()"
                                wire:click="deleteSingle({{ $student->id }})"
                                class="ml-2 text-red-600 hover:text-red-800">🗑️</button>

                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-4 text-center text-gray-500">
                            Tidak ada data ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>


    {{-- Selected + Pagination --}}
    <div class="mt-4 flex items-center justify-between px-4 pb-4 text-black">
        <div class="text-sm">
            <span class="font-semibold">{{ count($selectedStudents) }}</span> mahasiswa dipilih
            @if (count($selectedStudents) > 0)
                <button wire:click="deleteSelected"
                    onclick="confirm('Hapus semua terpilih?') || event.stopImmediatePropagation()"
                    class="ml-3 text-sm text-red-600 hover:text-red-800 font-medium">🗑️ Hapus Terpilih</button>
            @endif
        </div>

        <div>{{ $students->links() }}</div>
    </div>


    {{-- Modal Detail/Edit --}}
    @if ($showModal && $selectedStudent)
        <x-sccr-modal :show="$showModal" maxWidth="3xl">
            @if ($modalMode === 'detail')
                @include('livewire.holdings.campus.siakad.student.student-show', [
                    'student' => $selectedStudent,
                ])
            @else
                @livewire('holdings.campus.siakad.student.student-edit', ['id' => $selectedStudent->id], key('edit-' . $selectedStudent->id))
            @endif
        </x-sccr-modal>
    @endif


    {{-- Modal Create --}}
    @if ($showCreateModal)
        <x-sccr-modal :show="$showCreateModal" title="Tambah Data Mahasiswa" maxWidth="2xl">
            @livewire('holdings.campus.siakad.student.student-create', [], key('create-' . now()->timestamp))
        </x-sccr-modal>
    @endif

    {{-- Modal Quick Add --}}
    @if ($showCreateAddModal)
        <x-sccr-modal :show="$showCreateAddModal" title="Quick Add Mahasiswa" maxWidth="2xl">
            @livewire('holdings.campus.siakad.student.students-create-quick', [], key('quick-create-' . now()->timestamp))
        </x-sccr-modal>
    @endif

</x-sccr-card>
