<x-sccr-card wire:key="lms-room-join">

    {{-- Header --}}
    <div class="relative py-4 bg-cyan-600 rounded-b-3xl shadow-lg text-white z-10">
        <div class="px-4 mb-2">
            <h1 class="text-3xl font-bold">Gabung Room LMS</h1>

            @if ($room)
                <p class="text-lg">Room: <strong>{{ $room->name }}</strong></p>
            @else
                <p class="text-lg text-red-200 italic">Room tidak ditemukan</p>
            @endif
        </div>

        @php
            $breadcrumbItems = [['label' => 'Dashboard LMS', 'url' => route('dashboard.lms-main')]];

            if ($room && $room->id) {
                $breadcrumbItems[] = [
                    'label' => 'Gabung Room',
                    'url' => route('holdings.campus.lms.room.join', ['room' => $room->id]),
                ];
            } else {
                $breadcrumbItems[] = [
                    'label' => 'Gabung Room',
                    'url' => '#',
                ];
            }
        @endphp

        <x-sccr-breadcrumb :items="$breadcrumbItems" />

    </div>

    {{-- Info Room --}}
    <div class="px-4 py-6 space-y-4">
        @if ($room)
            <x-sccr-card class="bg-white shadow">
                <h2 class="text-lg font-semibold">Informasi Room</h2>
                <p><strong>Kurikulum:</strong> {{ $room->kurikulum }}</p>
                <p><strong>Semester:</strong> {{ $room->semester }}</p>
                <p><strong>Max Peserta:</strong> {{ $room->max_participants }}</p>
            </x-sccr-card>

            {{-- Tombol Join --}}
            <div class="mt-6">
                @if ($joined)
                    <div class="text-green-600 font-semibold">Anda sudah bergabung ke room ini.</div>
                @else
                    <x-sccr-button wire:click="joinRoom" variant="primary">Gabung Sekarang</x-sccr-button>
                @endif
            </div>
        @else
            <div class="text-center py-12 text-gray-600">
                <h2 class="text-xl font-semibold">Room tidak ditemukan</h2>
                <p>Silakan pilih atau buat Room terlebih dahulu sebelum bergabung.</p>
                <x-sccr-button :href="route('holdings.campus.lms.room.create')" variant="primary" class="mt-4">
                    Buat Room Baru
                </x-sccr-button>
            </div>
        @endif
    </div>

</x-sccr-card>
