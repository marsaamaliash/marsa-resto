@props(['tabs' => [], 'active' => 'tab1'])

<div x-data="{ active: '{{ $active }}' }">
    <div class="flex border-b mb-4">
        @foreach ($tabs as $key => $label)
            <button @click="active = '{{ $key }}'"
                :class="active === '{{ $key }}' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600'"
                class="px-4 py-2 text-sm font-medium focus:outline-none">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Konten tab --}}
    <div x-show="active === 'tab1'" x-cloak>
        {{ $tab1 ?? '' }}
    </div>
    <div x-show="active === 'tab2'" x-cloak>
        {{ $tab2 ?? '' }}
    </div>
    <div x-show="active === 'tab3'" x-cloak>
        {{ $tab3 ?? '' }}
    </div>
    <div x-show="active === 'tab4'" x-cloak>
        {{ $tab4 ?? '' }}
    </div>
</div>
