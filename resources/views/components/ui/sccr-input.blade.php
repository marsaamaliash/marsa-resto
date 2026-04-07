@props([
    'name' => null,
    'label' => null,
    'type' => 'text',
    'required' => false,
    'value' => null,
])

@php
    // Auto-detect name dari attribute jika tidak diisi
    $wireModel =
        $attributes->get('wire:model') ??
        ($attributes->get('wire:model.live') ??
            ($attributes->get('wire:model.defer') ??
                ($attributes->get('wire:model.lazy') ??
                    ($attributes->get('wire:model.debounce.400ms') ??
                        ($attributes->get('wire:model.debounce.300ms') ??
                            $attributes->get('wire:model.debounce.500ms'))))));

    $resolvedName = $name ?? ($attributes->get('name') ?? $wireModel);

    // Untuk id: bikin aman untuk HTML (ganti titik jadi underscore, dll)
    $resolvedId =
        $attributes->get('id') ??
        ($resolvedName ? preg_replace('/[^A-Za-z0-9\-_:.]/', '_', (string) $resolvedName) : null);

    // Kalau file input, jangan set value (browser block).
    $isFile = $type === 'file';

    // old() hanya aman kalau resolvedName ada
    $inputValue = $resolvedName ? old($resolvedName, $value) : $value;
@endphp

<div class="mb-4">
    @if ($label)
        <label @if ($resolvedId) for="{{ $resolvedId }}" @endif
            class="block text-sm font-medium text-gray-700">
            {{ $label }}
        </label>
    @endif

    <input @if ($resolvedId) id="{{ $resolvedId }}" @endif
        @if ($resolvedName) name="{{ $resolvedName }}" @endif type="{{ $type }}"
        {{ $required ? 'required' : '' }} @if (!$isFile && !is_null($inputValue)) value="{{ $inputValue }}" @endif
        {{ $attributes->merge(['class' => 'mt-1 block w-full border-gray-300 rounded-md shadow-sm']) }}>

    @if ($resolvedName)
        @error($resolvedName)
            <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror
    @endif
</div>
