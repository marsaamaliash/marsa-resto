{{-- @props(['name' => 'Stem Cell and Cancer Research Indonesia']) --}}
@props(['name' => 'Default Text'])

<span x-data="{ colors: ['text-red-600', 'text-green-600', 'text-blue-600', 'text-purple-600'], i: 0 }" x-init="setInterval(() => { i = (i + 1) % colors.length }, 2000)" x-bind:class="colors[i]"
    class="text-2xl font-bold transition-colors duration-500">
    {{ $name }}
</span>
