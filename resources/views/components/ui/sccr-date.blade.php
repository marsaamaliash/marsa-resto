@props(['name', 'label' => null, 'required' => false])

<div class="mb-4">
    @if ($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700">
            {{ $label }}
        </label>
    @endif

    <input type="date" id="{{ $name }}" name="{{ $name }}" {{ $required ? 'required' : '' }}
        {{ $attributes->merge([
            'class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500',
        ]) }} />

    @error($name)
        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>
