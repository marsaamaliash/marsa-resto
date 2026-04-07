@props(['value', 'label' => null, 'mode' => 'label-sato', 'width' => '50mm', 'height' => '25mm'])
<div {{ $attributes->merge(['class' => 'text-center']) }}>
    <div class="bg-gray-50 p-3 rounded-lg border-2 border-dashed border-gray-200 inline-block w-full">
        <img src="{{ route('sso.qr.generate', ['q' => $value, 's' => 200]) }}" class="mx-auto h-32 w-32">
        <p class="text-[10px] mt-2 font-mono font-bold">{{ $label ?? $value }}</p>
    </div>
    <button type="button"
        onclick="printSccrFlexible('{{ $value }}', '{{ $mode }}', '{{ $width }}', '{{ $height }}')"
        class="mt-3 inline-flex items-center px-2 py-1 bg-gray-200 hover:bg-gray-300 text-gray-700 text-[10px] font-bold rounded uppercase transition">
        <i class="fas fa-print mr-1"></i> Cetak
    </button>
</div>
