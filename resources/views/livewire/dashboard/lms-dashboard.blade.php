<x-ui.sccr-card wire:key="lms-dashboard">

    {{-- Header --}}
    <div class="relative py-4 bg-blue-600 rounded-b-3xl shadow-lg text-white z-10">
        <div class="px-4 mb-2">
            <h1 class="text-3xl font-bold">Dashboard LMS</h1>
            <p class="text-lg">Statistik dan daftar room Anda</p>
        </div>

        <x-ui.sccr-breadcrumb :items="[
            ['label' => 'Main Dashboard', 'url' => route('dashboard')],
            ['label' => 'Campus', 'url' => route('dashboard.campus')],
            // ['label' => 'LMS', 'url' => route('dashboard.lms-dashboard')],
        ]" />

    </div>

    {{-- Room List --}}
    <div class="px-4 py-6 space-y-4">
        <h2 class="text-xl font-semibold">Room Anda</h2>
        <ul class="space-y-2">
            @forelse ($rooms as $room)
                <li class="bg-white shadow rounded px-4 py-2">
                    <div class="flex justify-between items-center">
                        <div>
                            <strong>{{ $room->name }}</strong> — {{ $room->kurikulum }} ({{ $room->semester }})
                        </div>
                        <a href="{{ route('campus.lms.room.manage', $room->id) }}">
                            <x-ui.sccr-button variant="secondary">Kelola</x-ui.sccr-button>
                        </a>
                    </div>
                </li>
            @empty
                <li class="text-gray-500">Belum ada room LMS yang Anda buat.</li>
            @endforelse
        </ul>
    </div>

</x-ui.sccr-card>
