@props([
    'headers' => [], // ['nip' => 'NIP', 'nama' => 'Nama', ...]
    'sortable' => [], // ['nip', 'nama', 'tanggal_join']
])

<table {{ $attributes->merge(['class' => 'min-w-full text-sm border']) }}>
    <thead class="bg-gray-100">
        {{ $head }}
    </thead>
    <tbody class="divide-y">
        {{ $body }}
    </tbody>
</table>
