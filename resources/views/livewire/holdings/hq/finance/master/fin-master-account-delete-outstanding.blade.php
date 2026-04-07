<x-ui.sccr-card transparent wire:key="fin-master-account-delete-outstanding">

    {{-- HEADER --}}
    <div class="relative px-8 py-6 bg-red-600/80 rounded-b-3xl shadow overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">Outstanding Delete — Master Account</h1>
                <p class="text-red-100 text-sm">Approve / Reject permintaan hapus (ERP)</p>
            </div>
        </div>

        <div class="mt-4 flex justify-between items-center text-sm">
            <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
            <div class="text-white">
                Pending: <span class="font-bold text-black">{{ $rows->total() }}</span>
            </div>
        </div>
    </div>

    {{-- FILTERS & ACTIONS --}}
    <div class="px-4 pt-8 pb-2">
        <div class="flex flex-wrap items-center justify-between gap-3">

            <form wire:submit.prevent class="flex flex-wrap items-center gap-3 flex-grow">
                <div class="relative top-1">
                    <span class="absolute -top-5 left-1 text-[10px] font-bold text-red-700 uppercase">
                        Cari Holding / Account / Alasan
                    </span>
                    <x-ui.sccr-input name="search" wire:model.live="search" placeholder="Ketik..." class="w-72" />
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <x-ui.sccr-button type="button" wire:click="approveSelected" variant="success"
                        class="bg-emerald-600/70 hover:bg-emerald-700 text-white" :disabled="count($selected) === 0">
                        ✅ Approve Selected ({{ count($selected) }})
                    </x-ui.sccr-button>
                </div>
            </form>

            <div class="flex items-end gap-3 ml-auto">
                <div class="relative top-1">
                    <span class="absolute -top-5 left-1 text-[10px] font-bold text-red-700 uppercase">Show:</span>
                    <select wire:model.live="perPage" class="border-gray-300 rounded-md text-sm">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>

        </div>
    </div>

    {{-- TABLE --}}
    <div class="mx-6 rounded-xl shadow border overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-700/80 text-white">
                <tr>
                    <th class="px-4 py-3 text-center w-10">
                        <input type="checkbox" wire:model.live="selectAll" class="rounded border-gray-300">
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-bold">Key</th>
                    <th class="px-4 py-3 text-left text-xs font-bold">Alasan</th>
                    <th class="px-4 py-3 text-left text-xs font-bold">Requested At</th>
                    <th class="px-4 py-3 text-center text-xs font-bold">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 bg-gray-100">
                @forelse ($rows as $r)
                    <tr class="hover:bg-red-50 transition">
                        <td class="px-4 py-2 text-center">
                            <input type="checkbox" value="{{ $r->id }}" wire:model.live="selected"
                                class="rounded border-gray-300">
                        </td>

                        <td class="px-4 py-2 text-sm font-mono font-semibold">
                            {{ $r->holding_kode }}.{{ $r->lokasi_kode }}
                        </td>

                        <td class="px-4 py-2 text-sm">
                            {{ $r->reason }}
                        </td>

                        <td class="px-4 py-2 text-xs text-gray-700">
                            {{ \Illuminate\Support\Carbon::parse($r->requested_at)->format('d-m-Y H:i') }}
                        </td>

                        <td class="px-4 py-2 text-center">
                            <div class="flex justify-center gap-3">
                                <x-ui.sccr-button type="button" variant="icon"
                                    wire:click="approveOne({{ $r->id }})"
                                    class="text-emerald-700 hover:scale-125" title="Approve">
                                    ✅
                                </x-ui.sccr-button>

                                <x-ui.sccr-button type="button" variant="icon"
                                    wire:click="openRejectOne({{ $r->id }})" class="text-red-700 hover:scale-125"
                                    title="Reject">
                                    ✖
                                </x-ui.sccr-button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-10 text-center text-gray-400 italic">
                            Tidak ada request pending
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- FOOTER --}}
    <div class="px-6 py-4 flex flex-col md:flex-row justify-between items-center gap-3">
        <div class="text-sm text-gray-600 flex items-center">
            <span class="font-bold text-red-700 mr-1">{{ count($selected) }}</span> request dipilih
        </div>
        <div>
            {{ $rows->links() }}
        </div>
    </div>

    {{-- TOAST --}}
    <x-ui.sccr-toast :show="$toast['show']" :type="$toast['type']" :message="$toast['message']" wire:key="toast-{{ microtime() }}" />

    {{-- REJECT MODAL --}}
    @if ($showRejectModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Reject Request Delete</h3>
                        <p class="text-xs text-gray-500 mt-1">Opsional: isi alasan penolakan</p>
                    </div>

                    <x-ui.sccr-button type="button" variant="icon" wire:click="cancelReject"
                        class="text-gray-500 hover:text-gray-800" title="Tutup">
                        <span class="text-xl leading-none">×</span>
                    </x-ui.sccr-button>
                </div>

                <div class="mt-4">
                    <label class="text-sm font-bold text-gray-700">Alasan Reject (opsional)</label>
                    <textarea wire:model.live="rejectReason" class="w-full border-gray-300 rounded-lg text-sm mt-1" rows="3"
                        placeholder="Contoh: masih dipakai / salah request..."></textarea>
                    <div class="text-[11px] text-gray-500 mt-1">Maks 255 karakter.</div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <x-ui.sccr-button type="button" variant="secondary" wire:click="cancelReject">
                        Batal
                    </x-ui.sccr-button>

                    <x-ui.sccr-button type="button" variant="danger" wire:click="submitReject">
                        Reject
                    </x-ui.sccr-button>
                </div>
            </div>
        </div>
    @endif

</x-ui.sccr-card>
