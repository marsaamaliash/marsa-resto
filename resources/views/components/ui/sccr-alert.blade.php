@props([
    'type' => 'info', // info, success, warning, danger
    'message' => '',
])

@php
    $colors = [
        'info' => 'bg-blue-50 text-blue-700 border-blue-300',
        'success' => 'bg-green-50 text-green-700 border-green-300',
        'warning' => 'bg-yellow-50 text-yellow-700 border-yellow-300',
        'danger' => 'bg-red-50 text-red-700 border-red-300',
    ];
@endphp

<div {{ $attributes->merge([
    'class' => "border-l-4 p-4 rounded-md {$colors[$type]}",
]) }}>
    {{ $message }}
</div>
