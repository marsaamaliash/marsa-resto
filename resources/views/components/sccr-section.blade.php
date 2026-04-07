<div {{ $attributes->merge(['class' => 'mb-6 p-4 bg-gray-100 rounded shadow']) }}>
    <h2 class="text-xl font-bold mb-2">{{ $title }}</h2>
    {{ $slot }}
</div>
