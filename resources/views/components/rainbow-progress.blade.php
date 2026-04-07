@props(['text' => 'SCCR'])

<div class="flex gap-[1px] justify-center text-[14px] font-semibold">
    @foreach (str_split($text) as $i => $char)
        <span class="rainbow-char" style="animation-delay: {{ $i * 0.2 }}s">
            {{ $char }}
        </span>
    @endforeach
</div>
