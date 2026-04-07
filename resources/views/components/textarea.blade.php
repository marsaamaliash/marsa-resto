@props([
    'name',
    'id' => $name,
    'label' => null,
    'value' => '',
    'required' => false,
    'placeholder' => '',
    'rows' => 2,
])

<div class="mb-1">
    @if ($label)
        <label for="{{ $id }}" class="block text-xs font-medium text-gray-700 ">
            {{ $label }} <span class="text-sm text-red-500"> {{ $required ? ' *' : '' }}</span>
        </label>
    @endif

    <textarea name="{{ $name }}" id="{{ $id }}" rows="{{ $rows }}" placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500']) }}>{{ old($name, $value) }}</textarea>

    @error($name)
        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>
