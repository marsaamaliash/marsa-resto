<x-ui.sccr-card transparent wire:key="inventaris-delete-outstanding">

    {{-- ================= HEADER ================= --}}
    <div class="relative px-8 py-6 bg-green-600/80 rounded-b-3xl shadow overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">Delete Outstanding</h1>
                <p class="text-green-100 text-sm">
                    Daftar permintaan hapus inventaris yang menunggu approval.
                </p>
            </div>
        </div>

        <div class="mt-4 flex justify-between items-center text-sm">
            <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
            <div class="text-white">
                Menampilkan <span class="font-bold text-black">{{ $data->total() }}</span> request ⏳
            </div>
        </div>
    </div>

    {{-- ================= FILTERS & ACTIONS (simple) ================= --}}
    <div class="px-4 pt-8 pb-2">
        <div class="flex flex-wrap items-center justify-between gap-3">

            <div class="flex flex-wrap items-center gap-3 flex-grow">
                <div class="relative top-1">
                    <span class="absolute -top-5 left-1 text-[10px] font-bold text-green-700 uppercase">
                        Cari Kode / Nama Barang / Requester
                    </span>
                    <x-ui.sccr-input name="search" wire:model.live.debounce.400ms="search"
                        placeholder="Ketik untuk mencari..." class="w-72" />
                </div>
            </div>

            <div class="flex items-end gap-3 ml-auto">
                <div class="relative top-1">
                    <span class="absolute -top-5 left-1 text-[10px] font-bold text-green-700 uppercase">
                        Show:
                    </span>
                    <select wire:model.live="perPage" class="border-gray-300 rounded-md text-sm">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>

        </div>
    </div>

    {{-- ================= TABLE ================= --}}
    <div class="mx-6 rounded-xl shadow border overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-700/80 text-white">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-bold">ID</th>
                    <th class="px-4 py-3 text-left text-xs font-bold">Kode Label</th>
                    <th class="px-4 py-3 text-left text-xs font-bold">Nama Barang</th>
                    <th class="px-4 py-3 text-left text-xs font-bold">Requester</th>
                    <th class="px-4 py-3 text-left text-xs font-bold">Reason</th>
                    <th class="px-4 py-3 text-left text-xs font-bold">Created</th>
                    <th class="px-4 py-3 text-center text-xs font-bold">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 bg-gray-100">
                @forelse($data as $row)
                    @php
                        $payload = $row->action_payload ?? [];
                        $kode = $payload['kode_label'] ?? '(unknown)';
                        $reason = $payload['reason'] ?? '-';
                        // nama_barang diharapkan sudah ikut dari query (join)
                        $namaBarang = $row->nama_barang ?? '-';
                    @endphp

                    <tr class="hover:bg-green-100 transition">
                        <td class="px-4 py-2 text-xs text-gray-600">{{ $row->id }}</td>

                        <td class="px-4 py-2 font-mono text-xs font-bold">
                            {{ $kode }}
                        </td>

                        <td class="px-4 py-2 text-sm">
                            <div class="font-semibold text-gray-800">{{ $namaBarang }}</div>
                            <div class="text-[11px] text-gray-500">Label: {{ $kode }}</div>
                        </td>

                        <td class="px-4 py-2 text-xs">
                            <div class="font-semibold">{{ $row->requester?->username ?? 'N/A' }}</div>
                            <div class="text-gray-500">UID: {{ $row->auth_user_id }}</div>
                        </td>

                        <td class="px-4 py-2 text-xs">
                            <div class="line-clamp-3 text-gray-700">{{ $reason }}</div>
                        </td>

                        <td class="px-4 py-2 text-xs text-gray-600">
                            {{ $row->created_at }}
                        </td>

                        <td class="px-4 py-2 text-center">
                            <div class="flex justify-center gap-2">
                                <button type="button" wire:click="approve({{ $row->id }})"
                                    class="px-3 py-1 rounded bg-green-600 hover:bg-green-700 text-white text-xs font-bold transition">
                                    Approve
                                </button>

                                <button type="button" wire:click="openReject({{ $row->id }})"
                                    class="px-3 py-1 rounded bg-red-600 hover:bg-red-700 text-white text-xs font-bold transition">
                                    Reject
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-10 text-center text-gray-400 italic">
                            Tidak ada permintaan delete yang pending.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ================= FOOTER ================= --}}
    <div class="px-6 py-4 flex flex-col md:flex-row justify-between items-center gap-3">
        <div class="text-sm text-gray-600">
            Total pending: <span class="font-bold text-green-700">{{ $data->total() }}</span>
        </div>
        <div>
            {{ $data->links() }}
        </div>
    </div>

    {{-- ================= TOAST ================= --}}
    <x-ui.sccr-toast :show="$toast['show']" :type="$toast['type']" :message="$toast['message']"
        wire:key="toast-outstanding-{{ microtime() }}" />

    {{-- Reject Modal --}}
    @if ($showRejectModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Tolak Permintaan Delete</h3>
                        <p class="text-xs text-gray-500 mt-1">
                            Berikan alasan penolakan agar requester bisa follow-up.
                        </p>
                    </div>
                    <button wire:click="closeReject" class="text-gray-500 hover:text-gray-800 text-xl leading-none">
                        ×
                    </button>
                </div>

                <div class="mt-4">
                    <label class="text-sm font-semibold text-gray-700">Alasan Penolakan</label>
                    <textarea wire:model.defer="rejectReason" rows="3"
                        class="w-full mt-1 border-gray-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500"
                        placeholder="Contoh: Data masih dipakai untuk audit..."></textarea>
                    @error('rejectReason')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" wire:click="closeReject"
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg font-bold text-gray-700 transition">
                        Batal
                    </button>
                    <button type="button" wire:click="submitReject"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 rounded-lg font-bold text-white transition">
                        Tolak
                    </button>
                </div>
            </div>
        </div>
    @endif

</x-ui.sccr-card>
