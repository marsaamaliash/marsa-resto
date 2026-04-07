@props([
    'name',
    'id' => $name,
    'label' => null,
    'options' => [],
    'required' => false,
    'placeholder' => 'Pilih salah satu...',
    'multiple' => false,
    'selected' => old($name),
])

@php
    $inputName = $multiple ? $name . '[]' : $name;
    $selectedValues = is_array($selected) ? $selected : [$selected];
@endphp

<div class="mb-1">
    @if ($label)
        <label for="{{ $id }}" class="block text-xs font-medium text-gray-700 ">
            {{ $label }} <span class="text-sm text-red-500"> {{ $required ? ' *' : '' }}</span>
        </label>
    @endif

    <select name="{{ $inputName }}" id="{{ $id }}" {{ $required ? 'required' : '' }}
        {{ $multiple ? 'multiple' : '' }}
        {{ $attributes->merge(['class' => 'w-full text-xs  border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500']) }}>
        @unless ($multiple)
            <option value="" disabled {{ empty($selected) ? 'selected' : '' }}>{{ $placeholder }}</option>
        @endunless

        @foreach ($options as $key => $value)
            <option value="{{ $key }}" {{ in_array($key, $selectedValues) ? 'selected' : '' }}>
                {{ $value }}
            </option>
        @endforeach
    </select>

    @error($name)
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>
