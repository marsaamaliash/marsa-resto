@props(['id', 'height' => '300px'])

<div class="bg-white rounded shadow p-4">
    <canvas id="{{ $id }}" style="height: {{ $height }}"></canvas>
</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:load', () => {
            const ctx = document.getElementById('{{ $id }}').getContext('2d');
            new Chart(ctx, {
                type: 'bar', // or 'line', 'pie', etc.
                data: @json($attributes['data']),
                options: @json($attributes['options'] ?? [])
            });
        });
    </script>
@endpush
