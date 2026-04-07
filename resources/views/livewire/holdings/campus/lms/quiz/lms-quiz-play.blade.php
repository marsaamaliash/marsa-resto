<x-sccr-card wire:key="lms-quiz-play">

    {{-- Header --}}
    <div class="relative py-4 bg-red-600 rounded-b-3xl shadow-lg text-white z-10">
        <div class="px-4 mb-2">
            <h1 class="text-3xl font-bold">Kerjakan Kuis</h1>
            <p class="text-lg">Judul: <strong>{{ $quiz->title }}</strong></p>
        </div>

        <x-sccr-breadcrumb :items="[
            ['label' => 'Dashboard LMS', 'url' => route('dashboard.lms-main')],
            // ['label' => 'Kuis', 'url' => route('holdings.campus.lms.quiz.play', $quiz->id)],
        ]" />
    </div>

    {{-- Soal --}}
    <div class="px-4 py-6 space-y-6">
        @foreach ($questions as $index => $question)
            <div class="bg-white shadow rounded p-4">
                <h2 class="font-semibold">Soal {{ $index + 1 }}:</h2>
                <p class="mb-2">{{ $question->question }}</p>

                @if ($question->type === 'multiple_choice')
                    @foreach ($question->options as $i => $option)
                        <label class="block">
                            <input type="radio" wire:model.defer="answers.{{ $question->id }}"
                                value="{{ $i }}">
                            {{ $option }}
                        </label>
                    @endforeach
                @else
                    <x-sccr-input type="textarea" wire:model.defer="answers.{{ $question->id }}"
                        placeholder="Jawaban Anda..." />
                @endif
            </div>
        @endforeach

        <x-sccr-button wire:click="submitQuiz" variant="primary">Kirim Jawaban</x-sccr-button>

        @if ($submitted)
            <div class="text-green-600 font-semibold mt-4">
                Kuis telah dikirim. Nilai Anda: <strong>{{ $score }}</strong>
            </div>
        @endif
    </div>

</x-sccr-card>
