<div class="grid grid-cols-1 md:grid-cols-2 gap-4">

    <x-ui.sccr-input name="nip" label="NIP" wire:model="nip" :required="true"
        placeholder="Contoh: 20180205 1 001" />

    <x-ui.sccr-input name="gelar_depan" label="Gelar Depan" wire:model="gelar_depan"
        placeholder="Contoh: Prof. DR. Ir. dr." />
    <x-ui.sccr-input name="nama" label="Nama Lengkap" wire:model="nama" :required="true"
        placeholder="Nama lengkap" />
    <x-ui.sccr-input name="gelar_belakang" label="Gelar Belakang" wire:model="gelar_belakang"
        placeholder="Contoh: S.Kom M.Biomed M.Biotech" />

    <x-ui.sccr-select name="holding_id" label="Holding" wire:model="holding_id" :required="true" :options="$holdings->pluck('name', 'id')->toArray()" />

    <x-ui.sccr-select name="department_id" label="Departemen" wire:model="department_id" :required="true"
        :options="$departments->pluck('name', 'id')->toArray()" />

    <x-ui.sccr-select name="division_id" label="Division" wire:model="division_id" :required="true"
        :options="$divisions->pluck('name', 'id')->toArray()" />

    <x-ui.sccr-select name="position_id" label="Posisi" wire:model="position_id" :required="true" :options="$positions->pluck('title', 'id')->toArray()" />

    <x-ui.sccr-select name="job_title_id" label="Job Title" wire:model="job_title_id" :required="true"
        :options="$job_titles->pluck('name', 'id')->toArray()" />

    <x-ui.sccr-date name="tanggal_join" label="Tanggal Masuk" wire:model="tanggal_join" :required="true" />

    <x-ui.sccr-select name="employee_status" label="Status Karyawan" wire:model="employee_status" :required="true"
        :options="['PKWT' => 'PKWT', 'Karyawan Tetap' => 'Karyawan Tetap', 'RESIGN' => 'RESIGN']" />
</div>
