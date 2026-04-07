@props(['name', 'label' => null, 'accept' => '.xlsx,.xls,.csv', 'required' => false])

<div class="mb-4">
    @if ($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700">
            {{ $label }}
        </label>
    @endif

    <input type="file" id="{{ $name }}" accept="{{ $accept }}" {{ $required ? 'required' : '' }}
        {{ $attributes->merge([
            'class' =>
                'block w-full text-sm text-gray-700 file:mr-2 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100',
        ]) }} />

    @error($name)
        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>
