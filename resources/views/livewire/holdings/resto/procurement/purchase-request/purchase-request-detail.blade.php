<x-ui.sccr-card transparent wire:key="purchase-request-detail" class="h-full min-h-0 flex flex-col">

    {{-- ================= HEADER ================= --}}
    <div class="relative px-8 py-6 bg-blue-600/80 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">Detail Purchase Request</h1>
                <p class="text-blue-100 text-sm font-mono">
                    {{ $pr->pr_number ?? '-' }}
                </p>
            </div>

            @if ($pr)
                @php
                    $statusColor = match($pr->status) {
                        'draft' => 'bg-gray-100 text-gray-800',
                        'pending_rm' => 'bg-yellow-100 text-yellow-800',
                        'pending_spv' => 'bg-blue-100 text-blue-800',
                        'approved' => 'bg-green-100 text-green-800',
                        'rejected' => 'bg-red-100 text-red-800',
                        'revised' => 'bg-orange-100 text-orange-800',
                        default => 'bg-gray-100 text-gray-800',
                    };
                    $statusLabel = match($pr->status) {
                        'draft' => 'Draft',
                        'pending_rm' => 'Pending RM Approval',
                        'pending_spv' => 'Pending SPV Approval',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'revised' => 'Revised',
                        default => ucfirst($pr->status),
                    };
                @endphp
                <span class="px-4 py-2 rounded-full text-sm font-bold {{ $statusColor }}">
                    {{ $statusLabel }}
                </span>
            @endif
        </div>

        <div class="mt-4 flex justify-between items-center text-sm">
            <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
        </div>
    </div>

    {{-- ================= MAIN CONTENT ================= --}}
    <div class="flex-1 min-h-0 px-4 py-4 overflow-auto">
        @if ($pr)
            <div class="max-w-6xl mx-auto space-y-6">

                {{-- INFO CARDS --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- PR INFO --}}
                    <div class="bg-white rounded-xl shadow border p-5">
                        <h3 class="text-sm font-bold text-gray-500 uppercase mb-3">Informasi PR</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Lokasi</span>
                                <span class="text-sm font-medium">{{ $pr->requesterLocation?->name ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Requester</span>
                                <span class="text-sm font-medium">{{ $pr->requested_by ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Tanggal Request</span>
                                <span class="text-sm font-medium">{{ $pr->requested_at?->format('d/m/Y H:i') ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Tanggal Dibutuhkan</span>
                                <span class="text-sm font-medium">{{ $pr->required_date?->format('d/m/Y') ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- APPROVAL INFO --}}
                    <div class="bg-white rounded-xl shadow border p-5">
                        <h3 class="text-sm font-bold text-gray-500 uppercase mb-3">Approval History</h3>
                        <div class="space-y-2">
                            @if ($pr->rm_approved_by)
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">RM Approve</span>
                                    <span class="text-xs text-green-600 bg-green-50 px-2 py-0.5 rounded">✓ {{ $pr->rm_approved_by }}</span>
                                </div>
                                <div class="text-xs text-gray-400 text-right">
                                    {{ $pr->rm_approved_at?->format('d/m/Y H:i') }}
                                </div>
                            @else
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">RM Approve</span>
                                    <span class="text-xs text-gray-400">-</span>
                                </div>
                            @endif

                            @if ($pr->spv_approved_by)
                                <div class="flex justify-between items-center pt-2 border-t">
                                    <span class="text-sm text-gray-600">SPV Approve</span>
                                    <span class="text-xs text-green-600 bg-green-50 px-2 py-0.5 rounded">✓ {{ $pr->spv_approved_by }}</span>
                                </div>
                                <div class="text-xs text-gray-400 text-right">
                                    {{ $pr->spv_approved_at?->format('d/m/Y H:i') }}
                                </div>
                            @else
                                <div class="flex justify-between pt-2 border-t">
                                    <span class="text-sm text-gray-600">SPV Approve</span>
                                    <span class="text-xs text-gray-400">-</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- REJECT/REVISE INFO --}}
                    <div class="bg-white rounded-xl shadow border p-5">
                        <h3 class="text-sm font-bold text-gray-500 uppercase mb-3">Reject/Revise History</h3>
                        <div class="space-y-2">
                            @if ($pr->rejected_by)
                                <div class="bg-red-50 rounded p-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-red-600 font-medium">Rejected</span>
                                        <span class="text-xs text-red-500">{{ $pr->rejected_by }}</span>
                                    </div>
                                    <p class="text-xs text-red-500 mt-1">{{ $pr->reject_reason }}</p>
                                </div>
                            @endif

                            @if ($pr->revise_requested_by)
                                <div class="bg-orange-50 rounded p-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-orange-600 font-medium">Revise Requested</span>
                                        <span class="text-xs text-orange-500">{{ $pr->revise_requested_by }}</span>
                                    </div>
                                    <p class="text-xs text-orange-500 mt-1">{{ $pr->revise_reason }}</p>
                                </div>
                            @endif

                            @if (! $pr->rejected_by && ! $pr->revise_requested_by)
                                <p class="text-sm text-gray-400 italic">No reject/revise history</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- NOTES --}}
                @if ($pr->notes)
                    <div class="bg-yellow-50 rounded-xl shadow border border-yellow-200 p-5">
                        <h3 class="text-sm font-bold text-yellow-700 uppercase mb-2">Catatan PR</h3>
                        <p class="text-sm text-gray-800">{{ $pr->notes }}</p>
                    </div>
                @endif

                {{-- ITEMS TABLE --}}
                <div class="bg-white rounded-xl shadow border overflow-hidden">
                    <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-800">Daftar Item</h3>
                        <div class="text-right">
                            <span class="text-sm text-gray-600">Total Estimasi:</span>
                            <span class="text-lg font-bold text-gray-800 font-mono">
                                Rp {{ number_format($pr->total_estimated_cost, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">#</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Item</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Stok Info</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Qty Order</th>
                                    <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase">Est. Harga</th>
                                    <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase">Total</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Catatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach ($pr->items as $index => $item)
                                    <tr class="{{ $item->is_critical ? 'bg-red-50' : '' }}">
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $index + 1 }}</td>
                                        <td class="px-4 py-3">
                                            <div class="text-sm font-medium text-gray-900">{{ $item->item?->name ?? 'Unknown' }}</div>
                                            <div class="text-xs text-gray-500">SKU: {{ $item->item?->sku ?? '-' }}</div>
                                            @if ($item->is_critical)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 mt-1">
                                                    <x-ui.sccr-icon name="alert-triangle" :size="12" class="mr-1" />
                                                    Stok Kritis
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if ($item->is_critical)
                                                <div class="text-xs">
                                                    <span class="text-red-600 font-bold">{{ number_format($item->actual_stock, 2) }}</span>
                                                    <span class="text-gray-400">/</span>
                                                    <span class="text-gray-600">{{ number_format($item->min_stock, 2) }}</span>
                                                </div>
                                                <div class="text-xs text-gray-400">{{ $item->uom?->name ?? 'Pcs' }}</div>
                                            @else
                                                <span class="text-xs text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="text-sm font-mono font-medium">{{ number_format($item->requested_qty, 2) }}</span>
                                            <span class="text-xs text-gray-500">{{ $item->uom?->name ?? 'Pcs' }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            @if ($item->unit_cost)
                                                <span class="text-sm font-mono">{{ number_format($item->unit_cost, 0, ',', '.') }}</span>
                                            @else
                                                <span class="text-xs text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            @if ($item->total_cost)
                                                <span class="text-sm font-mono font-medium">{{ number_format($item->total_cost, 0, ',', '.') }}</span>
                                            @else
                                                <span class="text-xs text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            {{ $item->notes ?? '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ACTION BUTTONS --}}
                <div class="flex flex-wrap gap-3 justify-end">
                    <x-ui.sccr-button type="button" wire:click="back"
                        class="bg-gray-500 text-white hover:bg-gray-600">
                        <x-ui.sccr-icon name="arrow-left" :size="16" class="mr-1" />
                        Kembali
                    </x-ui.sccr-button>

                    @if ($canEdit)
                        <x-ui.sccr-button type="button" wire:click="edit"
                            class="bg-blue-600 text-white hover:bg-blue-700">
                            <x-ui.sccr-icon name="edit" :size="16" class="mr-1" />
                            {{ $pr->isRevised() ? 'Revisi PR' : 'Edit PR' }}
                        </x-ui.sccr-button>
                    @endif

                    @if ($pr->isPendingRM() && $canApproveRM)
                        <x-ui.sccr-button type="button" wire:click="directApproveByRM"
                            class="bg-green-600 text-white hover:bg-green-700">
                            <x-ui.sccr-icon name="approve" :size="16" class="mr-1" />
                            Approve (RM)
                        </x-ui.sccr-button>

                        <x-ui.sccr-button type="button" wire:click="openActionModal('reject')"
                            class="bg-red-600 text-white hover:bg-red-700">
                            <x-ui.sccr-icon name="no" :size="16" class="mr-1" />
                            Reject
                        </x-ui.sccr-button>

                        <x-ui.sccr-button type="button" wire:click="openActionModal('revise')"
                            class="bg-orange-600 text-white hover:bg-orange-700">
                            <x-ui.sccr-icon name="refresh" :size="16" class="mr-1" />
                            Request Revise
                        </x-ui.sccr-button>
                    @endif

                    @if ($pr->isPendingSPV() && $canApproveSPV)
                        <x-ui.sccr-button type="button" wire:click="directApproveBySPV"
                            class="bg-green-600 text-white hover:bg-green-700">
                            <x-ui.sccr-icon name="approve" :size="16" class="mr-1" />
                            Approve (SPV)
                        </x-ui.sccr-button>

                        <x-ui.sccr-button type="button" wire:click="openActionModal('reject')"
                            class="bg-red-600 text-white hover:bg-red-700">
                            <x-ui.sccr-icon name="no" :size="16" class="mr-1" />
                            Reject
                        </x-ui.sccr-button>

                        <x-ui.sccr-button type="button" wire:click="openActionModal('revise')"
                            class="bg-orange-600 text-white hover:bg-orange-700">
                            <x-ui.sccr-icon name="refresh" :size="16" class="mr-1" />
                            Request Revise
                        </x-ui.sccr-button>
                    @endif
                </div>

            </div>
        @endif
    </div>

    {{-- ================= ACTION MODAL ================= --}}
    @if ($actionModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click="closeActionModal">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden" wire:click.stop>
                @php
                    $modalTitle = match($actionModal) {
                        'approve_rm' => 'Approve PR (Restaurant Manager)',
                        'approve_spv' => 'Approve PR (Supervisor)',
                        'reject' => 'Reject PR',
                        'revise' => 'Request Revise PR',
                        default => 'Action',
                    };
                    $modalColor = match($actionModal) {
                        'approve_rm', 'approve_spv' => 'bg-green-600',
                        'reject' => 'bg-red-600',
                        'revise' => 'bg-orange-600',
                        default => 'bg-gray-600',
                    };
                @endphp

                <div class="px-6 py-4 border-b {{ $modalColor }} flex justify-between items-center">
                    <h3 class="text-lg font-bold text-white">{{ $modalTitle }}</h3>
                    <button wire:click="closeActionModal" class="text-white hover:text-gray-200 text-2xl">&times;</button>
                </div>

                <div class="p-6">
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-1">
                            @if ($actionModal === 'reject')
                                Alasan Reject <span class="text-red-500">*</span>
                            @elseif ($actionModal === 'revise')
                                Alasan Revise <span class="text-red-500">*</span>
                            @else
                                Catatan (Opsional)
                            @endif
                        </label>
                        <textarea wire:model="actionNotes" rows="3"
                            class="w-full border-gray-300 rounded-md text-sm"
                            placeholder="@if ($actionModal === 'reject') Alasan reject wajib diisi... @elseif ($actionModal === 'revise') Alasan revise wajib diisi... @else Tambahkan catatan... @endif"></textarea>
                    </div>
                </div>

                <div class="px-6 py-4 border-t bg-gray-50 flex justify-end gap-2">
                    <x-ui.sccr-button type="button" wire:click="closeActionModal"
                        class="bg-gray-500 text-white hover:bg-gray-600">
                        Batal
                    </x-ui.sccr-button>

                    @if ($actionModal === 'approve_rm')
                        <x-ui.sccr-button type="button" wire:click="approveByRM"
                            class="bg-green-600 text-white hover:bg-green-700">
                            Approve
                        </x-ui.sccr-button>
                    @elseif ($actionModal === 'approve_spv')
                        <x-ui.sccr-button type="button" wire:click="approveBySPV"
                            class="bg-green-600 text-white hover:bg-green-700">
                            Approve
                        </x-ui.sccr-button>
                    @elseif ($actionModal === 'reject')
                        <x-ui.sccr-button type="button" wire:click="rejectPR"
                            class="bg-red-600 text-white hover:bg-red-700">
                            Reject
                        </x-ui.sccr-button>
                    @elseif ($actionModal === 'revise')
                        <x-ui.sccr-button type="button" wire:click="requestRevise"
                            class="bg-orange-600 text-white hover:bg-orange-700">
                            Request Revise
                        </x-ui.sccr-button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- ================= TOAST ================= --}}
    <x-ui.sccr-toast :show="$toast['show']" :type="$toast['type']" :message="$toast['message']" wire:key="toast-{{ microtime() }}" />

</x-ui.sccr-card>
