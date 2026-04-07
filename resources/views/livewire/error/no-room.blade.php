<x-sccr-card>
    <div class="text-center py-12 text-gray-600">
        <h2 class="text-xl font-semibold">Room tidak ditemukan</h2>
        <p>Silakan pilih atau buat Room terlebih dahulu sebelum mengakses modul ini.</p>
        <x-sccr-button :href="route('campus.lms.room.create')" variant="primary" class="mt-4">
            Buat Room Baru
        </x-sccr-button>
    </div>
</x-sccr-card>
