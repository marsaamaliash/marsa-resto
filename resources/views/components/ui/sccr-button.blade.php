@props([
    'type' => 'button',
    'variant' => 'primary', // primary, secondary, danger, success, info, warning, icon, icon-circle
])

@php
    // Base untuk button normal (tetap seperti sekarang)
    $baseNormal =
        'px-1 h-[38px] inline-flex items-center justify-center gap-1 rounded text-sm font-medium focus:outline-none transition-colors';

    // Base untuk icon-only (tidak maksa padding & height)
    $baseIcon = 'inline-flex items-center justify-center p-0 focus:outline-none transition-transform transition-colors';

    // Base icon circle (buat tombol tambah bulat solid)
    $baseIconCircle =
        'inline-flex items-center justify-center rounded-full p-0 focus:outline-none transition-transform transition-colors shadow';

    $colors = [
        'primary' => 'bg-blue-600 text-white hover:bg-blue-700',
        'secondary' => 'bg-gray-200 text-gray-800 hover:bg-gray-300',
        'danger' => 'bg-red-600 text-white hover:bg-red-700',
        'success' => 'bg-green-600 text-white hover:bg-green-700',
        'info' => 'bg-cyan-600 text-white hover:bg-cyan-700',
        'warning' => 'bg-amber-500 text-white hover:bg-amber-600',

        // Untuk icon-only biasanya transparan, warna diatur lewat class tambahan (text-red-600 dst)
        'icon' => 'bg-transparent',

        // Untuk icon-circle biar tetap bisa "solid"
        'icon-circle' => 'bg-gray-200 text-white hover:bg-gray-800',
    ];

    $isIcon = in_array($variant, ['icon', 'icon-circle'], true);

    $base = match ($variant) {
        'icon' => $baseIcon,
        'icon-circle' => $baseIconCircle,
        default => $baseNormal,
    };

    $colorClass = $colors[$variant] ?? $colors['primary'];
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => "{$base} {$colorClass}"]) }}>
    {{ $slot }}
</button>
