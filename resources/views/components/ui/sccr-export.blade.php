@props(['action', 'label' => 'Export'])

<form wire:submit.prevent="exportFile">
    <button type="submit"
        {{ $attributes->merge([
            'class' => 'px-3 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 text-sm',
        ]) }}>
        {{ $label }}
    </button>
</form>
