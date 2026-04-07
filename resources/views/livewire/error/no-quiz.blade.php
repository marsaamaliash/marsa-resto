<x-sccr-card>
    <div class="text-center py-12 text-gray-600">
        <h2 class="text-xl font-semibold">Quiz tidak ditemukan</h2>
        <p>Silakan pilih atau buat kuis terlebih dahulu sebelum mengakses modul ini.</p>
        <x-sccr-button :href="route('campus.lms.quiz.create', ['room' => $room->id ?? null])" variant="primary" class="mt-4">
            Buat Kuis Baru
        </x-sccr-button>
    </div>
</x-sccr-card>
