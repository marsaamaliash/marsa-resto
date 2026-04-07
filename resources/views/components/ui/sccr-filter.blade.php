@props([
    'fields' => [], // ['search' => 'Cari Nama', 'nip' => 'NIP']
])

<form wire:submit.prevent class="flex flex-wrap gap-4 mb-6">
    @foreach ($fields as $name => $label)
        <x-ui.sccr-input name="{{ $name }}" wire:model.defer="{{ $name }}" label="{{ $label }}"
            class="w-48" />
    @endforeach

    <x-ui.sccr-button type="submit" variant="primary">Filter</x-ui.sccr-button>
</form>
