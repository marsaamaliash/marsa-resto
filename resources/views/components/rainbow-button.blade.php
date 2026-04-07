@props([
    'href' => '#',
    'label' => 'Klik Saya',
    'target' => '_self',
])

{{-- <a href="{{ $href }}" target="{{ $target }}" class="rainbow-button">
    {{ $label }}
</a> --}}


<a href="{{ $href }}" {{ isset($target) ? "target=$target" : '' }}
    class="rainbow-button px-6 py-2 rounded-full font-bold shadow text-white inline-block text-center">
    {{ $label }}
</a>


{{-- Jika di Blade mau buka tab baru
<x-rainbow-button href="{{ route('ewarga.demo.home') }}" label="Lihat Dashboard Demo" target="_blank" /> --}}

{{-- Jika di Blade mau buka pada tab yang sama
<x-rainbow-button href="{{ route('ewarga.demo.home') }}" label="Lihat Dashboard Demo" /> --}}



@once
    @push('styles')
        <style>
            @keyframes rainbow {
                0% {
                    background-position: 0% 50%;
                }

                50% {
                    background-position: 100% 50%;
                }

                100% {
                    background-position: 0% 50%;
                }
            }

            .rainbow-button {
                background: linear-gradient(270deg, #ff0080, #ff8c00, #40e0d0, #8a2be2, #ff0080);
                background-size: 800% 800%;
                animation: rainbow 6s ease infinite;
                color: white;
                padding: 0.5rem 1.25rem;
                border-radius: 0.5rem;
                font-weight: bold;
                display: inline-block;
                text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
                transition: transform 0.2s, box-shadow 0.3s;
            }

            .rainbow-button:hover {
                transform: scale(1.05);
                box-shadow: 0 0 15px rgba(255, 255, 255, 0.5);
            }
        </style>
    @endpush
@endonce
