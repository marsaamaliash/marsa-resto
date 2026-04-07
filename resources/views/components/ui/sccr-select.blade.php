@props([
    'name' => null,
    'label' => null,
    'options' => [],
    'value' => null,
])

@php
    // Auto-detect name dari wire:model* jika tidak diset
    $wireModel =
        $attributes->get('wire:model') ??
        ($attributes->get('wire:model.live') ??
            ($attributes->get('wire:model.defer') ??
                ($attributes->get('wire:model.lazy') ??
                    ($attributes->get('wire:model.debounce.400ms') ??
                        ($attributes->get('wire:model.debounce.300ms') ??
                            $attributes->get('wire:model.debounce.500ms'))))));

    $resolvedName = $name ?? ($attributes->get('name') ?? $wireModel);

    // id aman untuk HTML
    $resolvedId =
        $attributes->get('id') ??
        ($resolvedName ? preg_replace('/[^A-Za-z0-9\-_:.]/', '_', (string) $resolvedName) : null);

    // old() hanya jika resolvedName ada
    $selected = $resolvedName ? old($resolvedName, $value) : $value;
@endphp

<div class="mb-4">
    @if ($label)
        <label @if ($resolvedId) for="{{ $resolvedId }}" @endif
            class="block text-sm font-medium text-gray-700">
            {{ $label }}
        </label>
    @endif

    <select @if ($resolvedId) id="{{ $resolvedId }}" @endif
        @if ($resolvedName) name="{{ $resolvedName }}" @endif
        {{ $attributes->merge(['class' => 'mt-1 block w-full border-gray-300 rounded-md shadow-sm']) }}>
        <option value="">-- Pilih --</option>

        @foreach ($options as $optValue => $text)
            <option value="{{ $optValue }}" @selected((string) $selected === (string) $optValue)>
                {{ $text }}
            </option>
        @endforeach
    </select>

    @if ($resolvedName)
        @error($resolvedName)
            <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror
    @endif
</div>
