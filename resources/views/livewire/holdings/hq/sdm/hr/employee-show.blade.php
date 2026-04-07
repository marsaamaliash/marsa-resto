<x-ui.sccr-card class="max-w-6xl mx-auto p-4">
    <div class="space-y-2">
        {{-- Header --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800">
                    Detail Karyawan: <br>
                    <span class="font-semibold text-blue-500">{{ $employee->gelar_depan }}
                        {{ $employee->nama }}{{ $employee->gelar_belakang ? ', ' . $employee->gelar_belakang : '' }}</span>
                </h2>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                @if (!empty($asOverlay))
                    <x-ui.sccr-button type="button" variant="secondary"
                        wire:click="$dispatch('emp-employee-overlay-close')">
                        Kembali
                    </x-ui.sccr-button>

                    @permission('EMP_UPDATE')
                        <x-ui.sccr-button variant="warning"
                            wire:click="$dispatch('emp-employee-open-edit', { nip: '{{ $employee->nip }}' })">
                            Edit Data
                        </x-ui.sccr-button>
                    @endpermission
                @else
                    <a href="{{ route('holdings.hq.sdm.hr.employee-table') }}">
                        <x-ui.sccr-button variant="secondary">Kembali</x-ui.sccr-button>
                    </a>
                @endif
            </div>
        </div>

        {{-- Informasi Utama --}}
        <div class="grid grid-cols-3 gap-4">
            {{-- Kolom Kiri (2/3) --}}
            <div class="col-span-2 bg-white p-4 border rounded shadow max-h-[600px] overflow-y-auto">

                <div class="flex text-xs p-1 ">
                    <span class="w-36 font-semibold">NIP</span>
                    <span class="mr-1">:</span>
                    <span>{{ $employee->nip }}</span>
                </div>

                <div class="flex text-xs p-1 ">
                    <span class="w-36 font-semibold">Nama</span>
                    <span class="mr-1">:</span>
                    <span>{{ $employee->gelar_depan }}
                        {{ $employee->nama }}{{ $employee->gelar_belakang ? ', ' . $employee->gelar_belakang : '' }}</span>
                </div>

                <div class="flex text-xs p-1 ">
                    <span class="w-36 font-semibold">Holding</span>
                    <span class="mr-1">:</span>
                    <span>{{ $employee->holding->name ?? '-' }}</span>
                </div>

                <div class="flex text-xs p-1 ">
                    <span class="w-36 font-semibold">Departemen</span>
                    <span class="mr-1">:</span>
                    <span>{{ $employee->department->name ?? '-' }}</span>
                </div>

                <div class="flex text-xs p-1 ">
                    <span class="w-36 font-semibold">Divisi</span>
                    <span class="mr-1">:</span>
                    <span>{{ $employee->division->name ?? '-' }}</span>
                </div>

                <div class="flex text-xs p-1 ">
                    <span class="w-36 font-semibold">Posisi</span>
                    <span class="mr-1">:</span>
                    <span>{{ $employee->position->title ?? '-' }}</span>
                </div>

                {{-- ✅ FIX: Job Title pakai jobTitleMaster (belongsTo) --}}
                <div class="flex text-xs p-1 ">
                    <span class="w-36 font-semibold">Job Title</span>
                    <span class="mr-1">:</span>
                    <span>{{ $employee->jobTitleMaster->name ?? ($employee->job_title ?? '-') }}</span>
                </div>

                <div class="flex text-xs p-1 ">
                    <span class="w-36 font-semibold">Tanggal Masuk</span>
                    <span class="mr-1">:</span>
                    <span>{{ $employee->tanggal_join ? \Carbon\Carbon::parse($employee->tanggal_join)->format('d-m-Y') : '-' }}</span>
                </div>

                <div class="flex text-xs p-1 ">
                    <span class="w-36 font-semibold">Status</span>
                    <span class="mr-1">:</span>
                    <span>{{ $employee->employee_status ?? '-' }}</span>
                </div>
            </div>

            {{-- Kolom Kanan (1/3) --}}
            <div class="col-span-1 bg-white p-4 border rounded shadow flex items-center justify-center ">
                <div>
                    <x-shared.sccr-person-photo type="employee" :key="(string) $employee->nip" :gender="$employee->jenis_kelamin" size="lg" />
                </div>
            </div>
        </div>

        <hr>

        {{-- Informasi Tambahan --}}
        <div class="grid grid-cols-3 gap-4">
            {{-- Kolom Kiri (2/3) --}}
            <div class="col-span-2 bg-white p-4 border rounded shadow max-h-[600px] overflow-y-auto">
                <div class="flex text-xs p-1 ">
                    <span class="w-36 font-semibold">Alamat Asal</span>
                    <span class="mr-1">:</span>
                    <span>{{ $employee->alamat_asal }}</span>
                </div>
                <div class="flex text-xs p-1 ">
                    <span class="w-36 font-semibold">Kota Asal</span>
                    <span class="mr-1">:</span>
                    <span>{{ $employee->kota_asal }}</span>
                </div>
                <div class="flex text-xs p-1 ">
                    <span class="w-36 font-semibold">Alamat Domisili</span>
                    <span class="mr-1">:</span>
                    <span>{{ $employee->alamat_domisili }}</span>
                </div>
                <div class="flex text-xs p-1 ">
                    <span class="w-36 font-semibold">Kota Domisili</span>
                    <span class="mr-1">:</span>
                    <span>{{ $employee->kota_domisili }}</span>
                </div>
                <div class="flex text-xs p-1 ">
                    <span class="w-36 font-semibold">Jenis Kelamin</span>
                    <span class="mr-1">:</span>
                    <span>{{ $employee->jenis_kelamin }}</span>
                </div>
                <div class="flex text-xs p-1 ">
                    <span class="w-36 font-semibold">Status Perkawinan</span>
                    <span class="mr-1">:</span>
                    <span>{{ $employee->status_perkawinan }}</span>
                </div>
                <div class="flex text-xs p-1 ">
                    <span class="w-36 font-semibold">Agama</span>
                    <span class="mr-1">:</span>
                    <span>{{ $employee->agama }}</span>
                </div>
                <div class="flex text-xs p-1 ">
                    <span class="w-36 font-semibold">Tempat & Tanggal Lahir</span>
                    <span class="mr-1">:</span>
                    <span>{{ $employee->tempat_lahir }},
                        {{ $employee->tanggal_lahir ? \Carbon\Carbon::parse($employee->tanggal_lahir)->format('d-m-Y') : '-' }}</span>
                </div>
                <div class="flex text-xs p-1 ">
                    <span class="w-36 font-semibold">Pendidikan</span>
                    <span class="mr-1">:</span>
                    <span>{{ $employee->pendidikan }}</span>
                </div>
            </div>

            {{-- Kolom Kanan (1/3) --}}
            <div class="col-span-1 bg-white p-4 border rounded shadow">
                <div class="flex text-xs p-1 ">
                    <span class="w-32 font-semibold">Email</span>
                    <span class="mr-1">:</span>
                    <span>{{ $employee->email }}</span>
                </div>
                <div class="flex text-xs p-1 ">
                    <span class="w-32 font-semibold">No HP</span>
                    <span class="mr-1">:</span>
                    <span>{{ $employee->no_hp }}</span>
                </div>
                <div class="flex text-xs p-1 ">
                    <span class="w-32 font-semibold">No eKTP</span>
                    <span class="mr-1">:</span>
                    <span>{{ $employee->no_ektp ?? '-' }}</span>
                </div>
                <div class="flex text-xs p-1 ">
                    <span class="w-32 font-semibold">KIS</span>
                    <span class="mr-1">:</span>
                    <span>{{ $employee->kis ?? '-' }}</span>
                </div>
                <div class="flex text-xs p-1 ">
                    <span class="w-32 font-semibold">BPJS TK</span>
                    <span class="mr-1">:</span>
                    <span>{{ $employee->bpjs_tk ?? '-' }}</span>
                </div>
                <div class="flex text-xs p-1 ">
                    <span class="w-32 font-semibold">No Rekening</span>
                    <span class="mr-1">:</span>
                    <span>{{ $employee->no_rekening ?? '-' }}</span>
                </div>
                <div class="flex text-xs p-1 ">
                    <span class="w-32 font-semibold">Nama Bank</span>
                    <span class="mr-1">:</span>
                    <span>{{ $employee->nama_bank ?? '-' }}</span>
                </div>
                <div class="flex text-xs p-1 ">
                    <span class="w-32 font-semibold">Pemilik Rekening</span>
                    <span class="mr-1">:</span>
                    <span>{{ $employee->pemilik_rekening ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>
</x-ui.sccr-card>
