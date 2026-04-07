@props(['title', 'route', 'label'])

<div class="flex items-center justify-between mb-2">
    <h1 class="text-lg font-semibold mb-4">{{ $titleNya }} {{ $wilayah->nama_domain }}</h1>
    <div class="space-x-2">
        <a href="{{ $routeNya }}"
            class="px-6 py-2 text-sm font-semibold text-white rounded-lg shadow-md bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 hover:from-indigo-600 hover:to-pink-600 transition">
            {{ $labelNya }}
        </a>
    </div>
</div>
