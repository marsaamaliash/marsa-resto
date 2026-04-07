<div class="space-y-4">

    {{-- NIM --}}
    <div>
        <label class="block font-medium">Student ID (NIM)</label>
        <input type="text" wire:model.defer="nim" class="w-full border-gray-300 rounded p-2">
        @error('nim')
            <span class="text-red-600 text-sm">{{ $message }}</span>
        @enderror
    </div>

    {{-- Full Name --}}
    <div>
        <label class="block font-medium">Full Name</label>
        <input type="text" wire:model.defer="nama_lengkap" class="w-full border-gray-300 rounded p-2">
        @error('nama_lengkap')
            <span class="text-red-600 text-sm">{{ $message }}</span>
        @enderror
    </div>

    {{-- Gender + Religion --}}
    <div class="grid grid-cols-3 gap-4">

        {{-- Gender --}}
        <div>
            <label class="block font-medium">Gender</label>
            <select wire:model.defer="jenis_kelamin" class="w-full border-gray-300 rounded p-2">
                <option value="Laki-laki">Male</option>
                <option value="Perempuan">Female</option>
            </select>
            @error('jenis_kelamin')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        {{-- Religion --}}
        <div>
            <label class="block font-medium">Religion</label>
            <select wire:model.defer="agama" class="w-full border-gray-300 rounded p-2">
                <option value="Islam">Islam</option>
                <option value="Kristen">Christian</option>
                <option value="Hindu">Hindu</option>
                <option value="Buddha">Buddha</option>
                <option value="Konghuchu">Konghuchu</option>
                <option value="Kepercayaan">Indigenous</option>
                <option value="Tidak Punya">None</option>
            </select>
            @error('agama')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        {{-- Blood Type --}}
        <div>
            <label class="block font-medium">Blood Type</label>
            <select wire:model.defer="gol_darah" class="w-full border-gray-300 rounded p-2">
                <option value="A">A</option>
                <option value="B">B</option>
                <option value="AB">AB</option>
                <option value="O">O</option>
                <option value="TIDAK TAHU">Unknown</option>
            </select>
        </div>

    </div>

    {{-- Birth --}}
    <div class="grid grid-cols-2 gap-4">

        <div>
            <label class="block font-medium">Place of Birth</label>
            <input type="text" wire:model.defer="tempat_lahir" class="w-full border-gray-300 rounded p-2">
        </div>

        <div>
            <label class="block font-medium">Date of Birth</label>
            <input type="date" wire:model.defer="tanggal_lahir" class="w-full border-gray-300 rounded p-2">
        </div>

    </div>

    {{-- NIK + NISN --}}
    <div class="grid grid-cols-2 gap-4">

        <div>
            <label class="block font-medium">National ID (No. eKTP)</label>
            <input type="text" wire:model.defer="no_ektp" class="w-full border-gray-300 rounded p-2">
        </div>

        <div>
            <label class="block font-medium">NISN</label>
            <input type="text" wire:model.defer="nisn" class="w-full border-gray-300 rounded p-2">
        </div>

    </div>

</div>
