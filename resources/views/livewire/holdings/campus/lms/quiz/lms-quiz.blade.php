<x-sccr-layout>
    <x-sccr-card title="Modul Kuis LMS">

        {{-- Navigasi atau ringkasan kuis --}}
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-700">Kuis Aktif</h2>
            <p class="text-sm text-gray-500">Silakan kerjakan kuis yang tersedia dan lihat hasilnya setelah submit.</p>
        </div>

        {{-- Komponen pengerjaan kuis --}}
        <div class="mb-10">
            @livewire('holdings.campus.lms.lms-quiz-play')
        </div>

        {{-- Komponen hasil kuis --}}
        <div class="mt-10">
            @livewire('holdings.campus.lms.lms-result-view')
        </div>

    </x-sccr-card>
</x-sccr-layout>
