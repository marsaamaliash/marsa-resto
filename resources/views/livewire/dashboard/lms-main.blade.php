{{-- <x-sccr-layout> --}}
<div>
    {{-- Header --}}
    <x-sccr-header title="Modul LMS Kampus" subtitle="Kelola semua aktivitas pembelajaran daring" color="indigo" />

    {{-- Breadcrumb --}}
    <x-ui.sccr-breadcrumb :items="[
        ['label' => 'Main Dashboard', 'url' => route('dashboard')],
        ['label' => 'Campus', 'url' => route('dashboard.campus')],
        ['label' => 'LMS', 'url' => route('dashboard.lms-main')],
        // ['label' => 'Modul Lengkap', 'url' => route('campus.lms.page')],
    ]" />

    {{-- Grid Modular --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 px-4 py-6">

        <x-sccr-section title="Room Management">
            <x-sccr-button :href="route('holdings.campus.lms.room.create')">Buat Room</x-sccr-button>
            <x-sccr-button :href="route('holdings.campus.lms.room.join', ['room' => $room->id ?? 0])">Gabung Room</x-sccr-button>
        </x-sccr-section>

        <x-sccr-section title="Quiz">
            <x-sccr-button :href="route('holdings.campus.lms.quiz')">Daftar Kuis</x-sccr-button>
            <x-sccr-button :href="route('holdings.campus.lms.quiz.create', ['room' => $room->id ?? 0])">Buat Kuis</x-sccr-button>
        </x-sccr-section>

        <x-sccr-section title="Material">
            <x-sccr-button :href="route('holdings.campus.lms.material.upload', ['room' => $room->id ?? 0])">Upload Materi</x-sccr-button>
        </x-sccr-section>


        {{-- Dashboard --}}
        {{-- <x-sccr-card title="Dashboard LMS">
            @livewire('dashboard.lms-dashboard')
        </x-sccr-card> --}}

        {{-- Buat Room --}}
        {{-- <x-sccr-card title="Buat Room LMS">
            @livewire('holdings.campus.lms.lms-room-create')
        </x-sccr-card> --}}

        {{-- Kelola Room --}}
        {{-- <x-sccr-card title="Kelola Room LMS">
            @isset($room)
                @livewire('holdings.campus.lms.lms-room-manage', ['room' => $room])
            @else
                @livewire('holdings.campus.lms.lms-room-manage')
            @endisset
        </x-sccr-card> --}}

        {{-- Upload Materi --}}
        {{-- <x-sccr-card title="Upload Materi Belajar">
            @livewire('holdings.campus.lms.lms-material-upload')
        </x-sccr-card> --}}

        {{-- Buat Kuis --}}
        {{-- <x-sccr-card title="Buat Kuis">
            @livewire('holdings.campus.lms.lms-quiz-create')
        </x-sccr-card> --}}

        {{-- Kerjakan Kuis --}}
        {{-- <x-sccr-card title="Kerjakan Kuis">
            @isset($quiz)
                @livewire('holdings.campus.lms.lms-quiz-play', ['quiz' => $quiz])
            @else
                @livewire('holdings.campus.lms.lms-quiz-play')
            @endisset
        </x-sccr-card> --}}

        {{-- Hasil Kuis --}}
        {{-- <x-sccr-card title="Lihat Hasil Kuis">
            @isset($quiz)
                @livewire('holdings.campus.lms.lms-result-view', ['quiz' => $quiz])
            @else
                @livewire('holdings.campus.lms.lms-result-view')
            @endisset
        </x-sccr-card> --}}

        {{-- Gabung Room --}}
        {{-- <x-sccr-card title="Gabung Room LMS">
            @livewire('holdings.campus.lms.lms-room-join')
        </x-sccr-card> --}}

        {{-- Video Frame --}}
        {{-- <x-sccr-card title="Sesi Video LMS">
            @livewire('holdings.campus.lms.lms-video-frame')
        </x-sccr-card> --}}

    </div>

</div>
{{-- </x-sccr-layout> --}}
