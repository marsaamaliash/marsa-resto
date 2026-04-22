<x-ui.sccr-card transparent wire:key="purchase-request-detail" class="h-full min-h-0 flex flex-col">

    {{-- ================= HEADER ================= --}}
    <div class="relative px-8 py-6 bg-gradient-to-r from-blue-600 to-blue-700 rounded-b-3xl shadow-lg">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">{{ $pr?->pr_number ?? 'PR Details' }}</h1>
                <p class="text-blue-100 text-sm mt-1">
                    Status: <span class="font-bold">{{ ucfirst(str_replace('_', ' ', $pr?->status ?? 'Unknown')) }}</span>
                </p>
            </div>
        </div>

        <div class="mt-4 flex justify-between items-center text-sm">
            <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
        </div>
    </div>

    {{-- ================= MAIN CONTENT ================= --}}
    <div class="flex-1 min-h-0 px-4 py-4 overflow-auto">
        @if ($pr)
            <div class="max-w-6xl mx-auto space-y-6">

                {{-- TOAST NOTIFICATION --}}
                @if ($toast['show'])
                    <div class="fixed top-20 right-4 z-50">
                        <div class="px-6 py-4 rounded-lg shadow-lg {{ $toast['type'] === 'success' ? 'bg-green-500' : 'bg-red-500' }} text-white">
                            {{ $toast['message'] }}
                        </div>
                    </div>
                @endif

                {{-- PR HEADER INFO --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white rounded-xl shadow border p-6">
                        <h3 class="text-base font-bold text-gray-800 mb-4">Informasi PR</h3>
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="font-semibold text-gray-700">PR Number:</dt>
                                <dd class="text-gray-600">{{ $pr?->pr_number }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="font-semibold text-gray-700">Lokasi:</dt>
                                <dd class="text-gray-600">{{ $pr?->requesterLocation?->name ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="font-semibold text-gray-700">Requester:</dt>
                                <dd class="text-gray-600">{{ $pr?->requested_by ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="font-semibold text-gray-700">Tanggal Request:</dt>
                                <dd class="text-gray-600">{{ $pr?->requested_at?->format('d/m/Y H:i') ?? '-' }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="bg-white rounded-xl shadow border p-6">
                        <h3 class="text-base font-bold text-gray-800 mb-4">Status & Total</h3>
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="font-semibold text-gray-700">Status:</dt>
                                <dd>
                                    <span class="px-2 py-1 rounded text-white text-xs font-bold
                                        {{ $pr?->status === 'draft' ? 'bg-gray-500' : '' }}
                                        {{ $pr?->status === 'pending_rm' ? 'bg-yellow-500' : '' }}
                                        {{ $pr?->status === 'pending_spv' ? 'bg-blue-500' : '' }}
                                        {{ $pr?->status === 'approved' ? 'bg-green-500' : '' }}
                                        {{ $pr?->status === 'rejected' ? 'bg-red-500' : '' }}
                                        {{ $pr?->status === 'revised' ? 'bg-orange-500' : '' }}">
                                        {{ ucfirst(str_replace('_', ' ', $pr?->status)) }}
                                    </span>
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="font-semibold text-gray-700">Total Items:</dt>
                                <dd class="text-gray-600">{{ $pr?->items?->count() ?? 0 }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="font-semibold text-gray-700">Created:</dt>
                                <dd class="text-gray-600">{{ $pr?->created_at?->format('d/m/Y H:i') ?? '-' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                {{-- NOTES --}}
                @if ($pr?->notes)
                    <div class="bg-blue-50 rounded-xl shadow border border-blue-200 p-6">
                        <h3 class="text-base font-bold text-blue-800 mb-2">Catatan PR</h3>
                        <p class="text-sm text-gray-800">{{ $pr?->notes }}</p>
                    </div>
                @endif

                {{-- PR ITEMS TABLE --}}
                <div class="bg-white rounded-xl shadow border p-6">
                    <h3 class="text-base font-bold text-gray-800 mb-4">Daftar Item</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 border-b">
                                <tr>
                                    <th class="px-3 py-3 text-left font-bold text-gray-700">#</th>
                                    <th class="px-3 py-3 text-left font-bold text-gray-700">Item</th>
                                    <th class="px-3 py-3 text-center font-bold text-gray-700">Stok Info</th>
                                    <th class="px-3 py-3 text-center font-bold text-gray-700">Qty Order</th>
                                    <th class="px-3 py-3 text-left font-bold text-gray-700">Catatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach ($pr->items as $index => $item)
                                    <tr class="hover:bg-gray-50 {{ $item->is_critical ? 'bg-red-50' : '' }}">
                                        <td class="px-3 py-3 text-sm text-gray-900">{{ $index + 1 }}</td>
                                        <td class="px-3 py-3">
                                            <div class="text-sm font-medium text-gray-900">{{ $item->item?->name ?? 'Unknown' }}</div>
                                            <div class="text-xs text-gray-500">SKU: {{ $item->item?->sku ?? '-' }}</div>
                                            @if ($item->is_critical)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 mt-1">
                                                    Stok Kritis
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <div class="text-xs">
                                                <div class="flex justify-center items-center gap-2">
                                                    <span class="text-gray-600">Actual:</span>
                                                    <span class="{{ $item->is_critical ? 'text-red-600 font-bold' : 'text-gray-900 font-medium' }}">{{ number_format($item->actual_stock, 2) }}</span>
                                                </div>
                                                <div class="flex justify-center items-center gap-2">
                                                    <span class="text-gray-600">Minimal:</span>
                                                    <span class="text-gray-900 font-medium">{{ number_format($item->min_stock, 2) }}</span>
                                                </div>
                                                <div class="text-xs text-gray-400 mt-1">{{ $item->uom?->name ?? 'Pcs' }}</div>
                                            </div>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <span class="text-sm font-mono font-medium">{{ number_format($item->requested_qty, 2) }}</span>
                                            <div class="text-xs text-gray-500">{{ $item->uom?->name ?? 'Pcs' }}</div>
                                        </td>
                                        <td class="px-3 py-3 text-sm text-gray-600">
                                            {{ $item->notes ?? '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- APPROVAL FLOW --}}
                <div class="bg-white rounded-xl shadow border p-6">
                    <h3 class="text-base font-bold text-gray-800 mb-4">Alur Approval</h3>

                    <div class="space-y-4">
                        {{-- LEVEL 0: DRAFT --}}
                        <div class="flex items-start gap-4">
                            <div class="flex flex-col items-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white
                                    {{ $pr?->status === 'revised' ? 'bg-orange-500' : 'bg-green-500' }}">
                                    {{ $pr?->status === 'revised' ? '↻' : '✓' }}
                                </div>
                                @if ($pr?->status !== 'approved' && $pr?->status !== 'rejected')
                                    <div class="w-1 h-12 bg-gray-300 mt-1"></div>
                                @endif
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800">Draft - Preparation</h4>
                                <p class="text-xs text-gray-600">PR dibuat dan disiapkan untuk submission</p>
                                @if ($pr?->status === 'revised')
                                    <div class="mt-2 p-3 bg-orange-50 rounded-lg border border-orange-200">
                                        <p class="text-xs font-semibold text-orange-800">↻ Revisi - Menunggu perbaikan</p>
                                        @if ($pr?->revise_reason)
                                            <p class="text-xs text-gray-700 mt-1 italic">{{ $pr?->revise_reason }}</p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- LEVEL 1: RM APPROVAL --}}
                        <div class="flex items-start gap-4">
                            <div class="flex flex-col items-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white
                                    {{ $pr?->approval_level >= 1 ? 'bg-green-500' : 'bg-gray-300' }}">
                                    1
                                </div>
                                @if ($pr?->approval_level < 2 && $pr?->status !== 'approved' && $pr?->status !== 'rejected')
                                    <div class="w-1 h-12 bg-gray-300 mt-1"></div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-800">RM Approval</h4>
                                <p class="text-xs text-gray-600">Restaurant Manager melakukan review dan approval</p>

                                @if ($pr?->rm_approved_at)
                                    <div class="mt-2 p-3 bg-green-50 rounded-lg border border-green-200">
                                        <p class="text-xs font-semibold text-green-800">✓ Approved</p>
                                        <p class="text-xs text-gray-600 mt-1">{{ $pr?->rm_approved_at?->format('d/m/Y H:i') }}</p>
                                        @if ($pr?->rm_notes)
                                            <p class="text-xs text-gray-700 mt-1 italic">{{ $pr?->rm_notes }}</p>
                                        @endif
                                    </div>
                                @elseif ($pr?->isPendingRM())
                                    <div class="mt-2 p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                                        <p class="text-xs font-semibold text-yellow-800">Menunggu approval RM</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- LEVEL 2: SPV APPROVAL --}}
                        <div class="flex items-start gap-4">
                            <div class="flex flex-col items-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white
                                    {{ $pr?->approval_level >= 2 ? 'bg-green-500' : 'bg-gray-300' }}">
                                    2
                                </div>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-800">Supervisor Approval (Final)</h4>
                                <p class="text-xs text-gray-600">Supervisor melakukan final approval</p>

                                @if ($pr?->spv_approved_at)
                                    <div class="mt-2 p-3 bg-green-50 rounded-lg border border-green-200">
                                        <p class="text-xs font-semibold text-green-800">✓ Approved - PR Ready</p>
                                        <p class="text-xs text-gray-600 mt-1">{{ $pr?->spv_approved_at?->format('d/m/Y H:i') }}</p>
                                        @if ($pr?->spv_notes)
                                            <p class="text-xs text-gray-700 mt-1 italic">{{ $pr?->spv_notes }}</p>
                                        @endif
                                    </div>
                                @elseif ($pr?->isPendingSPV())
                                    <div class="mt-2 p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                                        <p class="text-xs font-semibold text-yellow-800">Menunggu approval SPV</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- REJECTED STATUS --}}
                        @if ($pr?->status === 'rejected')
                            <div class="flex items-start gap-4">
                                <div class="flex flex-col items-center">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white bg-red-500">
                                        ✗
                                    </div>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-red-800">PR Ditolak</h4>
                                    @if ($pr?->reject_reason)
                                        <div class="mt-2 p-3 bg-red-50 rounded-lg border border-red-200">
                                            <p class="text-xs text-gray-700 italic">{{ $pr?->reject_reason }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- ACTION BUTTONS --}}
                    <div class="mt-6 pt-4 border-t border-gray-200 flex justify-between items-center">
                        <a href="{{ route('dashboard.resto.purchase-request') }}"
                            class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-sm font-semibold">
                            Kembali
                        </a>
                        <div class="flex gap-2">
                            @if ($canEdit)
                                <a href="{{ $pr->isRevised() ? route('dashboard.resto.purchase-request.revise', $pr->id) : route('dashboard.resto.purchase-request.edit', $pr->id) }}"
                                    class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 text-sm font-semibold">
                                    {{ $pr->isRevised() ? 'Revisi PR' : 'Edit PR' }}
                                </a>
                            @endif
                            @if ($pr->isPendingRM() && $canApproveRM)
                                <button wire:click="directApproveByRM"
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-semibold">
                                    ✓ Approve RM
                                </button>
                                <button wire:click="openActionModal('reject')"
                                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-semibold">
                                    ✗ Tolak
                                </button>
                                <button wire:click="openActionModal('revise')"
                                    class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 text-sm font-semibold">
                                    ↻ Revisi
                                </button>
                            @endif
                            @if ($pr->isPendingSPV() && $canApproveSPV)
                                <button wire:click="directApproveBySPV"
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-semibold">
                                    ✓ Approve SPV
                                </button>
                                <button wire:click="openActionModal('reject')"
                                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-semibold">
                                    ✗ Tolak
                                </button>
                                <button wire:click="openActionModal('revise')"
                                    class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 text-sm font-semibold">
                                    ↻ Revisi
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        @endif
    </div>

    {{-- ================= MODALS ================= --}}

    {{-- REJECT MODAL --}}
    <div x-data="{ open: @entangle('showRejectModal') }" x-show="open" x-cloak
        class="fixed inset-0 bg-black bg-opacity-50 z-40 flex items-center justify-center"
        @click.self="open = false; $wire.closeActionModal()">
        <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4" @click.stop>
            <h3 class="text-lg font-bold text-red-800 mb-4">Tolak PR</h3>
            <textarea wire:model.live="rejectReason" rows="4"
                placeholder="Alasan penolakan..."
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm mb-2"></textarea>
            @error('rejectReason') <span class="text-red-500 text-xs mb-2 block">{{ $message }}</span> @enderror
            <div class="flex justify-end gap-2">
                <button type="button" @click="open = false; $wire.closeActionModal()"
                    class="px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400">
                    Batal
                </button>
                <button type="button" wire:click="rejectPR"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Tolak PR
                </button>
            </div>
        </div>
    </div>

    {{-- REVISE MODAL --}}
    <div x-data="{ open: @entangle('showReviseModal') }" x-show="open" x-cloak
        class="fixed inset-0 bg-black bg-opacity-50 z-40 flex items-center justify-center"
        @click.self="open = false; $wire.closeActionModal()">
        <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4" @click.stop>
            <h3 class="text-lg font-bold text-orange-800 mb-4">Minta Revisi</h3>
            <textarea wire:model.live="reviseReason" rows="4"
                placeholder="Alasan revisi..."
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm mb-2"></textarea>
            @error('reviseReason') <span class="text-red-500 text-xs mb-2 block">{{ $message }}</span> @enderror
            <div class="flex justify-end gap-2">
                <button type="button" @click="open = false; $wire.closeActionModal()"
                    class="px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400">
                    Batal
                </button>
                <button type="button" wire:click="requestRevise"
                    class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
                    Minta Revisi
                </button>
            </div>
        </div>
    </div>

</x-ui.sccr-card>
