<x-sccr-card wire:key="lms-video-frame">

    {{-- Header --}}
    <div class="relative py-4 bg-gray-800 rounded-b-3xl shadow-lg text-white z-10">
        <div class="px-4 mb-2">
            <h1 class="text-3xl font-bold">Sesi Video LMS</h1>
            <p class="text-lg">Room: <strong>{{ $room->name }}</strong></p>
        </div>

        <x-sccr-breadcrumb :items="[
            ['label' => 'Dashboard LMS', 'url' => route('dashboard.lms-main')],
            // ['label' => 'Video Room', 'url' => route('holdings.campus.lms.room.video', $room->id)],
        ]" />
    </div>

    {{-- Frame Video --}}
    <div class="px-4 py-6">
        <div class="bg-black rounded-lg overflow-hidden shadow-lg">
            {{-- Embed iframe Jitsi/WebRTC --}}
            <iframe src="{{ $room->video_url }}" allow="camera; microphone; fullscreen; display-capture"
                class="w-full h-[600px] border-none" title="LMS Video Room"></iframe>
        </div>

        {{-- Status --}}
        <div class="mt-4 text-sm text-gray-600">
            <p>Pastikan kamera dan mikrofon Anda aktif selama sesi berlangsung.</p>
            <p>Jika mengalami kendala, refresh halaman atau hubungi dosen pengampu.</p>
        </div>
    </div>

</x-sccr-card>
