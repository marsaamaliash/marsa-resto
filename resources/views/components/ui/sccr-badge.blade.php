@props([
    'type' => 'default', // default, success, warning, danger, info
])

@php
    $colors = [
        'default' => 'bg-gray-200 text-gray-800',
        'success' => 'bg-green-100 text-green-800',
        'warning' => 'bg-yellow-100 text-yellow-800',
        'danger' => 'bg-red-100 text-red-800',
        'info' => 'bg-blue-100 text-blue-800',
    ];
@endphp

<span {{ $attributes->merge([
    'class' => "px-2 py-1 rounded text-xs font-semibold {$colors[$type]}",
]) }}>
    {{ $slot }}
</span>
