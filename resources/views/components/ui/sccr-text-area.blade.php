@props(['name', 'label' => null, 'rows' => 4, 'required' => false])

<div class="mb-4">
    @if ($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700">
            {{ $label }}
        </label>
    @endif

    <textarea id="{{ $name }}" rows="{{ $rows }}" {{ $required ? 'required' : '' }}
        {{ $attributes->merge([
            'class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500',
        ]) }}></textarea>

    @error($name)
        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>
