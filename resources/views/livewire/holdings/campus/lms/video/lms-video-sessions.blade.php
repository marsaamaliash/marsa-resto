<x-sccr-card>
    <h2 class="text-xl font-bold mb-4">Sesi Video Aktif</h2>

    <p><strong>Room:</strong> {{ $room->name }}</p>
    <p><strong>Session ID:</strong> {{ $session->session_id }}</p>
    <p><strong>Host:</strong> {{ $session->host_nip }}</p>
    <p><strong>Dimulai:</strong> {{ $session->started_at?->format('d M Y H:i') ?? '-' }}</p>

    <iframe src="http://localhost:3000/client.html" class="w-full h-[600px] border rounded mt-4"></iframe>
</x-sccr-card>
