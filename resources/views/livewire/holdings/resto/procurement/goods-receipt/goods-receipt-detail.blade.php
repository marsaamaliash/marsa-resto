<x-ui.sccr-card transparent wire:key="goods-receipt-detail" class="h-full min-h-0 flex flex-col">

    {{-- ================= HEADER ================= --}}
    <div class="relative px-8 py-6 bg-gradient-to-r from-green-600 to-green-700 rounded-b-3xl shadow-lg">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">Detail Goods Receipt</h1>
                <p class="text-green-100 text-sm mt-1">{{ $gr?->receipt_number ?? '-' }}</p>
            </div>
        </div>

        <div class="mt-4 flex justify-between items-center text-sm">
            <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
        </div>
    </div>

    {{-- ================= MAIN CONTENT ================= --}}
    <div class="flex-1 min-h-0 px-4 py-4 overflow-auto">
        <div class="max-w-6xl mx-auto space-y-6">

            {{-- TOAST NOTIFICATION --}}
            @if ($toast['show'])
                <div class="fixed top-20 right-4 z-50">
                    <div class="px-6 py-4 rounded-lg shadow-lg {{ $toast['type'] === 'success' ? 'bg-green-500' : 'bg-red-500' }} text-white">
                        {{ $toast['message'] }}
                    </div>
                </div>
            @endif

            @if ($gr)
                {{-- INFO CARD --}}
                <div class="bg-white rounded-xl shadow border p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <span class="text-xs font-bold text-gray-500 uppercase">Receipt Number</span>
                            <p class="text-lg font-mono font-bold text-green-700">{{ $gr->receipt_number }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-gray-500 uppercase">PO Number</span>
                            <p class="text-lg font-mono font-bold text-blue-700">
                                <a href="{{ route('dashboard.resto.purchase-order.detail', $gr->purchase_order_id) }}" class="hover:underline">
                                    {{ $gr->purchaseOrder?->po_number ?? '-' }}
                                </a>
                            </p>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-gray-500 uppercase">Status</span>
                            <p>
                                @php
                                    $statusColor = match($gr->status) {
                                        'draft' => 'bg-gray-100 text-gray-800',
                                        'pending_rm' => 'bg-yellow-100 text-yellow-800',
                                        'pending_spv' => 'bg-blue-100 text-blue-800',
                                        'approved' => 'bg-green-100 text-green-800',
                                        'rejected' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800',
                                    };
                                    $statusLabel = match($gr->status) {
                                        'draft' => 'Draft',
                                        'pending_rm' => 'Pending RM',
                                        'pending_spv' => 'Pending SPV',
                                        'approved' => 'Approved',
                                        'rejected' => 'Rejected',
                                        default => ucfirst($gr->status),
                                    };
                                @endphp
                                <span class="px-3 py-1 rounded text-sm font-semibold {{ $statusColor }}">
                                    {{ $statusLabel }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-gray-500 uppercase">Vendor</span>
                            <p class="text-sm font-semibold">{{ $gr->purchaseOrder?->vendor_name ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-gray-500 uppercase">Lokasi</span>
                            <p class="text-sm font-semibold">{{ $gr->location?->name ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-gray-500 uppercase">Tanggal Terima</span>
                            <p class="text-sm">{{ $gr->received_at?->format('d/m/Y H:i') ?? '-' }}</p>
                        </div>
                    </div>

                    @if ($gr->notes)
                        <div class="mt-4 pt-4 border-t">
                            <span class="text-xs font-bold text-gray-500 uppercase">Catatan</span>
                            <p class="text-sm mt-1">{{ $gr->notes }}</p>
                        </div>
                    @endif
                </div>

                {{-- APPROVAL FLOW --}}
                <div class="bg-white rounded-xl shadow border p-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">Approval Flow</h2>

                    <div class="flex items-center space-x-4">
                        {{-- RM Approval --}}
                        <div class="flex-1">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center
                                    {{ $gr->rm_approved_at ? 'bg-green-500 text-white' : ($gr->isPendingRM() || $gr->isPendingSPV() || $gr->isApproved() ? 'bg-yellow-500 text-white' : 'bg-gray-300 text-gray-600') }}">
                                    @if ($gr->rm_approved_at)
                                        <x-ui.sccr-icon name="check" :size="16" />
                                    @else
                                        <span class="text-xs font-bold">1</span>
                                    @endif
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-bold">Restaurant Manager</p>
                                    @if ($gr->rm_approved_at)
                                        <p class="text-xs text-green-600">Approved - {{ $gr->rm_approved_at->format('d/m/Y H:i') }}</p>
                                        @if ($gr->rm_notes)
                                            <p class="text-xs text-gray-500">{{ $gr->rm_notes }}</p>
                                        @endif
                                    @elseif ($gr->isPendingRM())
                                        <p class="text-xs text-yellow-600">Pending</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Arrow --}}
                        <div class="text-gray-400">
                            <x-ui.sccr-icon name="arrow-right" :size="20" />
                        </div>

                        {{-- SPV Approval --}}
                        <div class="flex-1">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center
                                    {{ $gr->spv_approved_at ? 'bg-green-500 text-white' : ($gr->isPendingSPV() || $gr->isApproved() ? 'bg-blue-500 text-white' : 'bg-gray-300 text-gray-600') }}">
                                    @if ($gr->spv_approved_at)
                                        <x-ui.sccr-icon name="check" :size="16" />
                                    @else
                                        <span class="text-xs font-bold">2</span>
                                    @endif
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-bold">Supervisor</p>
                                    @if ($gr->spv_approved_at)
                                        <p class="text-xs text-green-600">Approved - {{ $gr->spv_approved_at->format('d/m/Y H:i') }}</p>
                                        @if ($gr->spv_notes)
                                            <p class="text-xs text-gray-500">{{ $gr->spv_notes }}</p>
                                        @endif
                                    @elseif ($gr->isPendingSPV())
                                        <p class="text-xs text-blue-600">Pending</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($gr->status === 'rejected')
                        <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                            <p class="text-sm font-bold text-red-700">Rejected</p>
                            <p class="text-xs text-red-600">{{ $gr->reject_reason }}</p>
                            <p class="text-xs text-gray-500 mt-1">Rejected by at {{ $gr->rejected_at?->format('d/m/Y H:i') ?? '-' }}</p>
                        </div>
                    @endif
                </div>

                {{-- RECEIVED ITEMS --}}
                <div class="bg-white rounded-xl shadow border p-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">Item Diterima</h2>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 border-b">
                                <tr>
                                    <th class="px-3 py-2 text-left font-bold text-gray-700">Item</th>
                                    <th class="px-3 py-2 text-center font-bold text-gray-700">Qty Ordered</th>
                                    <th class="px-3 py-2 text-center font-bold text-gray-700">Received (Baik)</th>
                                    <th class="px-3 py-2 text-center font-bold text-gray-700">Damaged</th>
                                    <th class="px-3 py-2 text-center font-bold text-gray-700">Expired</th>
                                    <th class="px-3 py-2 text-left font-bold text-gray-700">Catatan Kondisi</th>
                                    <th class="px-3 py-2 text-center font-bold text-gray-700">Dokumentasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($gr->items as $item)
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="px-3 py-2">{{ $item->item?->name ?? '-' }}</td>
                                        <td class="px-3 py-2 text-center font-semibold">{{ $item->ordered_qty }}</td>
                                        <td class="px-3 py-2 text-center">
                                            <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $item->received_qty > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                                {{ $item->received_qty }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            @if ($item->damaged_qty > 0)
                                                <span class="px-2 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-800">
                                                    {{ $item->damaged_qty }}
                                                </span>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            @if ($item->expired_qty > 0)
                                                <span class="px-2 py-0.5 rounded text-xs font-semibold bg-orange-100 text-orange-800">
                                                    {{ $item->expired_qty }}
                                                </span>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 text-sm">{{ $item->condition_notes ?? '-' }}</td>
                                        <td class="px-3 py-2 text-center">
                                            @if ($item->documentation_path)
                                                <a href="{{ Storage::url($item->documentation_path) }}" target="_blank"
                                                    class="text-blue-600 hover:underline text-xs">
                                                    <x-ui.sccr-icon name="image" :size="16" class="inline" />
                                                    Lihat
                                                </a>
                                            @else
                                                <span class="text-gray-400 text-xs">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ACTION BUTTONS --}}
                <div class="flex justify-end gap-4 pb-4">
                    @if ($gr->isDraft())
                        <x-ui.sccr-button type="button" wire:click="submitForApproval"
                            class="bg-orange-600 text-white hover:bg-orange-700">
                            Submit ke RM
                        </x-ui.sccr-button>
                    @endif

                    @if ($gr->isPendingRM() && $this->isRMApprover())
                        <x-ui.sccr-button type="button" wire:click="$set('showApprovalModal', true)"
                            class="bg-green-600 text-white hover:bg-green-700">
                            Approve (RM)
                        </x-ui.sccr-button>
                        <x-ui.sccr-button type="button" wire:click="$set('showRejectModal', true)"
                            class="bg-red-600 text-white hover:bg-red-700">
                            Reject
                        </x-ui.sccr-button>
                    @endif

                    @if ($gr->isPendingSPV() && $this->isSPVApprover())
                        <x-ui.sccr-button type="button" wire:click="$set('showApprovalModal', true)"
                            class="bg-green-600 text-white hover:bg-green-700">
                            Approve (SPV)
                        </x-ui.sccr-button>
                        <x-ui.sccr-button type="button" wire:click="$set('showRejectModal', true)"
                            class="bg-red-600 text-white hover:bg-red-700">
                            Reject
                        </x-ui.sccr-button>
                    @endif

                    <a href="{{ route('dashboard.resto.goods-receipt') }}"
                        class="px-6 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500">
                        Kembali
                    </a>
                </div>
            @endif

        </div>
    </div>

    {{-- ================= APPROVAL MODAL ================= --}}
    @if ($showApprovalModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click="$set('showApprovalModal', false)">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden" wire:click.stop>
                <div class="px-6 py-4 border-b bg-green-600 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-white">
                        @if ($gr->isPendingRM()) Approve (RM) @else Approve (SPV) @endif
                    </h3>
                    <button wire:click="$set('showApprovalModal', false)" class="text-white hover:text-gray-200 text-2xl">&times;</button>
                </div>

                <div class="p-6">
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-1">
                            Catatan (Opsional)
                        </label>
                        <textarea wire:model="approvalNotes" rows="3"
                            class="w-full border-gray-300 rounded-md text-sm"
                            placeholder="Tambahkan catatan..."></textarea>
                    </div>
                </div>

                <div class="px-6 py-4 border-t bg-gray-50 flex justify-end gap-2">
                    <x-ui.sccr-button type="button" wire:click="$set('showApprovalModal', false)"
                        class="bg-gray-500 text-white hover:bg-gray-600">
                        Batal
                    </x-ui.sccr-button>

                    @if ($gr->isPendingRM())
                        <x-ui.sccr-button type="button" wire:click="approveByRM"
                            class="bg-green-600 text-white hover:bg-green-700">
                            Approve
                        </x-ui.sccr-button>
                    @elseif ($gr->isPendingSPV())
                        <x-ui.sccr-button type="button" wire:click="approveBySPV"
                            class="bg-green-600 text-white hover:bg-green-700">
                            Approve
                        </x-ui.sccr-button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- ================= REJECT MODAL ================= --}}
    @if ($showRejectModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click="$set('showRejectModal', false)">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden" wire:click.stop>
                <div class="px-6 py-4 border-b bg-red-600 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-white">Reject Goods Receipt</h3>
                    <button wire:click="$set('showRejectModal', false)" class="text-white hover:text-gray-200 text-2xl">&times;</button>
                </div>

                <div class="p-6">
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-1">
                            Alasan Reject <span class="text-red-500">*</span>
                        </label>
                        <textarea wire:model="rejectReason" rows="3"
                            class="w-full border-gray-300 rounded-md text-sm"
                            placeholder="Alasan reject wajib diisi..."></textarea>
                    </div>
                </div>

                <div class="px-6 py-4 border-t bg-gray-50 flex justify-end gap-2">
                    <x-ui.sccr-button type="button" wire:click="$set('showRejectModal', false)"
                        class="bg-gray-500 text-white hover:bg-gray-600">
                        Batal
                    </x-ui.sccr-button>

                    <x-ui.sccr-button type="button" wire:click="rejectGR"
                        class="bg-red-600 text-white hover:bg-red-700">
                        Reject
                    </x-ui.sccr-button>
                </div>
            </div>
        </div>
    @endif

</x-ui.sccr-card>
