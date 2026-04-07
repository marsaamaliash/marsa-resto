@props(['title', 'description' => null, 'breadcrumb' => [], 'theme' => 'green'])

<div class="relative px-8 py-4 bg-{{ $theme }}-600/70 rounded-b-3xl shadow">
    <div class="flex justify-between items-start">
        <div>
            <h1 class="text-3xl font-bold text-white">{{ $title }}</h1>
            @if ($description)
                <p class="text-sm text-{{ $theme }}-50">{{ $description }}</p>
            @endif
        </div>

        {{ $actions ?? '' }}
    </div>

    <div class="mt-3 flex justify-between items-center">
        <x-ui.sccr-breadcrumb :items="$breadcrumb" />
        <div class="text-sm text-white">
            {{ $meta ?? '' }}
        </div>
    </div>
</div>
