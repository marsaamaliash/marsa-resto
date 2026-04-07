<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <x-ui.sccr-input name="employee_code" label="Employee Code" wire:model="employee_code" />

    <x-ui.sccr-text-area name="alamat_asal" label="Alamat Asal" wire:model="alamat_asal" rows="3" />

    <x-ui.sccr-input name="kota_asal" label="Kota Asal" wire:model="kota_asal" />

    <x-ui.sccr-text-area name="alamat_domisili" label="Alamat Domisili" wire:model="alamat_domisili" rows="3" />

    <x-ui.sccr-input name="kota_domisili" label="Kota Domisili" wire:model="kota_domisili" />

    <x-ui.sccr-select name="jenis_kelamin" label="Jenis Kelamin" wire:model="jenis_kelamin" :options="['Laki-laki' => 'Laki-laki', 'Perempuan' => 'Perempuan']" />

    <x-ui.sccr-select name="status_perkawinan" label="Status Perkawinan" wire:model="status_perkawinan"
        :options="[
            'Menikah' => 'Menikah',
            'Belum Menikah' => 'Belum Menikah',
            'Cerai Hidup' => 'Cerai Hidup',
            'Cerai Mati' => 'Cerai Mati',
        ]" />

    <x-ui.sccr-select name="agama" label="Agama" wire:model="agama" :options="[
        'Islam' => 'Islam',
        'Kristen' => 'Kristen',
        'Hindu' => 'Hindu',
        'Buddha' => 'Buddha',
        'Konghuchu' => 'Konghuchu',
        'Kepercayaan' => 'Kepercayaan',
        'Tidak Punya' => 'Tidak Punya',
    ]" />

    <x-ui.sccr-select name="gol_darah" label="Gol Darah" wire:model="gol_darah" :options="[
        'A' => 'A',
        'B' => 'B',
        'AB' => 'AB',
        'O' => 'O',
        'TIDAK TAHU' => 'TIDAK TAHU',
    ]" />

    <x-ui.sccr-input name="tempat_lahir" label="Tempat Lahir" wire:model="tempat_lahir" />

    {{-- Konsisten pakai date component --}}
    <x-ui.sccr-date name="tanggal_lahir" label="Tanggal Lahir" wire:model="tanggal_lahir" />

    <x-ui.sccr-input name="pendidikan" label="Pendidikan Terakhir" wire:model="pendidikan" />
    <x-ui.sccr-input name="jurusan" label="Jurusan Terakhir" wire:model="jurusan" />
</div>
