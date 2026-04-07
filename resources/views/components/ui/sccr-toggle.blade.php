@props(['name', 'label' => null])

<div class="mb-4 flex items-center space-x-3">
    @if ($label)
        <label for="{{ $name }}" class="text-sm font-medium text-gray-700">
            {{ $label }}
        </label>
    @endif

    <input type="checkbox" id="{{ $name }}" value="1"
        {{ $attributes->merge([
            'class' => 'rounded focus:ring-blue-500 text-blue-600 border-gray-300',
        ]) }} />
</div>
