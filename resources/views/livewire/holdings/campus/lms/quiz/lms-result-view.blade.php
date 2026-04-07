<x-sccr-card wire:key="lms-result-view">

    {{-- Header --}}
    <div class="relative py-4 bg-green-600 rounded-b-3xl shadow-lg text-white z-10">
        <div class="px-4 mb-2">
            <h1 class="text-3xl font-bold">Hasil Kuis</h1>
            <p class="text-lg">Judul: <strong>{{ $quiz->title }}</strong></p>
        </div>

        <x-sccr-breadcrumb :items="[
            ['label' => 'Dashboard LMS', 'url' => route('dashboard.lms-main')],
            // ['label' => 'Room', 'url' => route('holdings.campus.lms.room.manage', $quiz->room_id)],
            // ['label' => 'Hasil Kuis', 'url' => route('holdings.campus.lms.quiz.result', $quiz->id)],
        ]" />
    </div>

    {{-- Tabel Hasil --}}
    <div class="px-4 py-6">
        <div class="overflow-x-auto bg-white shadow-md rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2">NIM</th>
                        <th class="px-4 py-2">Nama</th>
                        <th class="px-4 py-2">Skor</th>
                        <th class="px-4 py-2">Durasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($results as $result)
                        <tr>
                            <td class="px-4 py-2">{{ $result->student_nim }}</td>
                            <td class="px-4 py-2">{{ optional($result->student)->name ?? '-' }}</td>
                            <td class="px-4 py-2">{{ $result->total_score }}</td>
                            <td class="px-4 py-2">{{ $result->duration }} menit</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-4 text-center text-gray-500">Belum ada hasil kuis.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-sccr-card>
