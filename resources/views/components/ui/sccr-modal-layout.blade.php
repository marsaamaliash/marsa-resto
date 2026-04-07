@props([
    'groups' => [],
    'active' => 'tab1',
    'title' => 'Judul Modal',
])

<!-- Modal overlay -->
<div x-data="{ open: true, section: '{{ $active }}' }" x-show="open" @close-modal.window="open = false"
    class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
    <!-- Modal container -->
    <div class="bg-white rounded-lg shadow-lg w-full max-w-5xl mx-5 my-10 flex overflow-hidden h-[600px]">

        <!-- Sidebar -->
        <div class="w-1/4 bg-gray-100 p-4 space-y-2">
            <h2 class="text-center text-lg font-semibold mb-4">{{ $title }}</h2>
            @foreach ($groups as $key => $label)
                <button type="button" @click="section = '{{ $key }}'"
                    :class="section === '{{ $key }}' ? 'bg-yellow-400 font-semibold' : ''"
                    class="w-full text-left px-3 py-2 rounded hover:bg-gray-200">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <!-- Content -->
        <div class="w-3/4 p-6 overflow-y-auto space-y-4 h-full">
            <!-- Action buttons -->
            <div class="sticky top-0 bg-white/80 backdrop-blur-lg z-10 flex justify-end space-x-3 py-4">
                {{-- <div class="fixed top-0 left-0 right-0 bg-white/80 backdrop-blur-lg shadow z-50"> --}}
                {{ $buttons ?? '' }}
            </div>

            <!-- Section content -->
            @foreach ($groups as $key => $label)
                <div x-show="section === '{{ $key }}'" x-cloak>
                    {{ ${$key} ?? '' }}
                </div>
            @endforeach
        </div>
    </div>
</div>
