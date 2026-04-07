@props([
    'label',
    'value',
    'icon' => null,
    'color' => 'blue', // blue, green, red, yellow
])

@php
    $bg = [
        'blue' => 'bg-blue-100 text-blue-800',
        'green' => 'bg-green-100 text-green-800',
        'red' => 'bg-red-100 text-red-800',
        'yellow' => 'bg-yellow-100 text-yellow-800',
    ];
@endphp

<div class="p-4 bg-white rounded shadow flex items-center justify-between">
    <div>
        <p class="text-sm text-gray-500">{{ $label }}</p>
        <p class="text-xl font-semibold text-gray-800">{{ $value }}</p>
    </div>
    @if ($icon)
        <div class="p-2 rounded-full {{ $bg[$color] }}">
            {!! $icon !!}
        </div>
    @endif
</div>
