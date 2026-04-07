@props(['transparent' => false])

<div
    {{ $attributes->merge([
        // 'class' => $transparent ? 'bg-transparent shadow-none' : 'bg-grey-200 rounded shadow',
        // 'class' => $transparent ? 'bg-gradient-to-r from-yellow-500 to-emerald-700 shadow-none' : 'bg-white rounded shadow',
        'class' => $transparent
            ? 'bg-gradient-to-r from-yellow-200/70 to-emerald-400/70 shadow-none'
            : 'bg-white rounded shadow',
    ]) }}>
    {{ $slot }}
</div>
