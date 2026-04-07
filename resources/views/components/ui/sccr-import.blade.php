@props([
    'label' => 'Import',
])

<form wire:submit.prevent="importFile" enctype="multipart/form-data">
    <x-ui.sccr-file name="file" required />
    <x-ui.sccr-button type="submit" variant="success">{{ $label }}</x-ui.sccr-button>
</form>
