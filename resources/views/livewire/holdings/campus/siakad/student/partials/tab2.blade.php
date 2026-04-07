<div class="space-y-4">

    {{-- Enrollment Year --}}
    <div>
        <label class="block font-medium">Enrollment Year</label>
        <input type="text" wire:model.defer="tahun_masuk" class="w-full border-gray-300 rounded p-2" placeholder="2025">
        @error('tahun_masuk')
            <span class="text-red-600 text-sm">{{ $message }}</span>
        @enderror
    </div>

    {{-- Faculty & Study Program --}}
    <div class="grid grid-cols-2 gap-4">

        <div>
            <label class="block font-medium">Faculty</label>
            <select wire:model.defer="fakultas_id" class="w-full border-gray-300 rounded p-2">
                <option value="">-- Choose Faculty --</option>
                @foreach ($faculties as $faculty)
                    <option value="{{ $faculty->id }}">{{ $faculty->fakultas_name }}</option>
                @endforeach
            </select>
            @error('fakultas_id')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label class="block font-medium">Study Program</label>
            <select wire:model.defer="prodi_id" class="w-full border-gray-300 rounded p-2">
                <option value="">-- Choose Program --</option>
                @foreach ($programs as $program)
                    <option value="{{ $program->id }}">{{ $program->prodi_name }}</option>
                @endforeach
            </select>
            @error('prodi_id')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

    </div>

    {{-- Class & Level --}}
    <div class="grid grid-cols-2 gap-4">

        <div>
            <label class="block font-medium">Class</label>
            <select wire:model.defer="kelas_id" class="w-full border-gray-300 rounded p-2">
                <option value="">-- Choose Class --</option>
                @foreach ($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->kelas_name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block font-medium">Degree Level</label>
            <select wire:model.defer="jenjang" class="w-full border-gray-300 rounded p-2">
                <option value="D3">D3</option>
                <option value="S1">S1</option>
                <option value="S2">S2</option>
                <option value="S3">S3</option>
            </select>
        </div>

    </div>

    {{-- Student Status --}}
    <div>
        <label class="block font-medium">Student Status</label>
        <select wire:model.defer="student_status" class="w-full border-gray-300 rounded p-2">
            <option value="active">Active</option>
            <option value="leave">Leave</option>
            <option value="dropout">Drop Out</option>
            <option value="graduated">Graduated</option>
        </select>
    </div>

    {{-- Previous School --}}
    <div>
        <label class="block font-medium">Previous School</label>
        <input type="text" wire:model.defer="asal_sekolah" class="w-full border-gray-300 rounded p-2">
    </div>

</div>
