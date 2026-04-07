<x-sccr-card wire:key="lms-room-manage">

    {{-- Header --}}
    <div class="relative py-4 bg-teal-600 rounded-b-3xl shadow-lg text-white z-10">
        <div class="px-4 mb-2">
            <h1 class="text-3xl font-bold">Kelola Room LMS</h1>
            <p class="text-lg">Room: <strong>{{ $room->name }}</strong></p>
        </div>

        <x-sccr-breadcrumb :items="[
            ['label' => 'Dashboard LMS', 'url' => route('dashboard.lms-main')],
            // ['label' => 'Kelola Room', 'url' => route('holdings.campus.lms.room.manage', $room->id)],
        ]" />
    </div>

    {{-- Info Room --}}
    <div class="px-4 py-6 space-y-4">
        <x-sccr-card class="bg-white shadow">
            <h2 class="text-lg font-semibold">Informasi Room</h2>
            <p><strong>Kurikulum:</strong> {{ $room->kurikulum }}</p>
            <p><strong>Semester:</strong> {{ $room->semester }}</p>
            <p><strong>Max Peserta:</strong> {{ $room->max_participants }}</p>
        </x-sccr-card>

        {{-- Daftar Kuis --}}
        <h2 class="text-xl font-semibold">Daftar Kuis</h2>
        <ul class="space-y-2">
            @forelse ($quizzes as $quiz)
                <li class="bg-white shadow rounded px-4 py-2">
                    <div class="flex justify-between items-center">
                        <div>
                            <strong>{{ $quiz->title }}</strong><br>
                            <span class="text-sm text-gray-600">{{ $quiz->start_time->format('d M Y H:i') }} -
                                {{ $quiz->end_time->format('d M Y H:i') }}</span>
                        </div>
                        <a href="{{ route('campus.lms.quiz.result', $quiz->id) }}">
                            <x-sccr-button variant="secondary">Lihat Hasil</x-sccr-button>
                        </a>
                    </div>
                </li>
            @empty
                <li class="text-gray-500">Belum ada kuis di room ini.</li>
            @endforelse
        </ul>
    </div>

</x-sccr-card>
