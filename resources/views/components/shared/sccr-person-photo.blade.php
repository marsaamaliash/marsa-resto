@props([
    'type', // employee | lecturer | student | patient
    'key', // nip | nidn | nim | patient_id
    'gender' => null,
    'size' => 'md', // sm | md | lg
])

@php
    $sizeClass = match ($size) {
        'sm' => 'w-10 h-10',
        'lg' => 'w-40 h-40',
        default => 'w-24 h-24',
    };

    $basePath = match ($type) {
        'employee' => 'photo/employee',
        'lecturer' => 'photo/lecturer',
        'student' => 'photo/student',
        'patient' => 'photo/patient',
        default => 'photo/unknown',
    };

    $fallback = $gender === 'Laki-laki' ? asset('photo/man.png') : asset('photo/woman.png');

    $src = asset("{$basePath}/{$key}.png");
@endphp

<img src="{{ $src }}" alt="Person Photo" class="rounded-full object-cover shadow bg-white {{ $sizeClass }}"
    onerror="this.onerror=null;this.src='{{ $fallback }}';" />
