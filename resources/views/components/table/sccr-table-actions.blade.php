@props(['selectable' => false])

<th class="px-4 py-2 text-center">
    Aksi
    @if ($selectable)
        <input type="checkbox" wire:model="selectAll" class="ml-2">
    @endif
</th>
