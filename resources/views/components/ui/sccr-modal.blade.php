{{-- @props(['show' => false, 'title' => '']) --}}
@props(['show' => false, 'title' => '', 'maxWidth' => '2xl'])

<div x-data="{ open: @entangle($attributes->wire('model')).defer || @js($show) }" x-show="open" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div @click.away="open = false" class="bg-white rounded-lg shadow-lg w-auto max-w-{{ $maxWidth }} p-6">
        {{-- <div @click.away="open = false" class="bg-white rounded-lg shadow-lg w-full max-w-2xl p-6"> --}}
        {{-- Header --}}
        <div class="flex justify-between items-center pb-2 mb-4">
            <h2 class="text-lg font-semibold">{{ $title }}</h2>
            {{-- <button @click="open = false" class="text-gray-500 hover:text-gray-700">&times;</button> --}}
        </div>

        {{-- Body --}}
        <div>
            {{ $slot }}
        </div>
    </div>
</div>
