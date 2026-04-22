<x-ui.sccr-card transparent wire:key="direct-order-detail" class="h-full min-h-0 flex flex-col">

    {{-- ================= HEADER ================= --}}
    <div class="relative px-8 py-6 bg-gradient-to-r from-teal-600 to-cyan-700 rounded-b-3xl shadow-lg">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">{{ $do?->do_number ?? 'Direct Order Details' }}</h1>
                <p class="text-teal-100 text-sm mt-1">
                    Status: <span class="font-bold">{{ ucfirst(str_replace('_', ' ', $do?->status ?? 'Unknown')) }}</span>
                </p>
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

            {{-- DO HEADER INFO --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white rounded-xl shadow border p-6">
                    <h3 class="text-base font-bold text-gray-800 mb-4">Informasi Direct Order</h3>
                    @if ($do?->canBeEdited() && $isCreator)
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="font-semibold text-gray-700">Lokasi:</dt>
                                <dd class="text-gray-600">{{ $do?->location?->name }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="font-semibold text-gray-700">Pembeli:</dt>
                                <dd class="text-gray-600">{{ $do?->purchaser_name }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="font-semibold text-gray-700">Tanggal:</dt>
                                <dd class="text-gray-600">{{ $do?->purchase_date?->format('d/m/Y') }}</dd>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Pembayaran Dilakukan Oleh <span class="text-red-500">*</span></label>
                                <div class="space-y-2">
                                    <label class="flex items-center">
                                        <input type="radio" wire:model.live="paymentBy" value="holding"
                                            class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                                        <span class="ml-2 text-gray-700">Holding (Pusat)</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" wire:model.live="paymentBy" value="resto"
                                            class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                                        <span class="ml-2 text-gray-700">Resto (Cabang)</span>
                                    </label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan</label>
                                <textarea wire:model.live="doNotes" rows="2"
                                    placeholder="Catatan..."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></textarea>
                            </div>
                            <div class="pt-2">
                                <button type="button" wire:click="updateDODetails"
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-semibold">
                                    Simpan Detail
                                </button>
                            </div>
                        </div>
                    @else
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="font-semibold text-gray-700">DO Number:</dt>
                                <dd class="text-gray-600">{{ $do?->do_number }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="font-semibold text-gray-700">Pembeli:</dt>
                                <dd class="text-gray-600">{{ $do?->purchaser_name }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="font-semibold text-gray-700">Tanggal:</dt>
                                <dd class="text-gray-600">{{ $do?->purchase_date?->format('d/m/Y') }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="font-semibold text-gray-700">Status:</dt>
                                <dd>
                                    <span class="px-2 py-1 rounded text-white text-xs font-bold
                                        {{ $do?->status === 'draft' ? 'bg-gray-500' : '' }}
                                        {{ $do?->status === 'pending_rm' ? 'bg-yellow-500' : '' }}
                                        {{ $do?->status === 'pending_spv' ? 'bg-blue-500' : '' }}
                                        {{ $do?->status === 'approved' ? 'bg-green-500' : '' }}
                                        {{ $do?->status === 'rejected' ? 'bg-red-500' : '' }}
                                        {{ $do?->status === 'revised' ? 'bg-orange-500' : '' }}">
                                        {{ ucfirst(str_replace('_', ' ', $do?->status)) }}
                                    </span>
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="font-semibold text-gray-700">Payment By:</dt>
                                <dd class="text-gray-600">{{ ucfirst($do?->payment_by) }}</dd>
                            </div>
                            @if ($do?->notes)
                                <div class="mt-2 pt-2 border-t border-gray-200">
                                    <dt class="font-semibold text-gray-700">Catatan:</dt>
                                    <dd class="text-gray-600 italic">{{ $do?->notes }}</dd>
                                </div>
                            @endif
                        </dl>
                    @endif
                </div>

                <div class="bg-white rounded-xl shadow border p-6">
                    <h3 class="text-base font-bold text-gray-800 mb-4">Total Pembelian</h3>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between items-center">
                            <dt class="font-semibold text-gray-700">Total Amount:</dt>
                            <dd class="text-lg font-bold text-teal-600">
                                Rp {{ number_format($do?->total_amount ?? 0, 2, ',', '.') }}
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="font-semibold text-gray-700">Lokasi:</dt>
                            <dd class="text-gray-600">{{ $do?->location?->name }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="font-semibold text-gray-700">Created:</dt>
                            <dd class="text-gray-600">{{ $do?->created_at?->format('d/m/Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- PROOF SECTION --}}
            <div class="bg-white rounded-xl shadow border p-6">
                <h3 class="text-base font-bold text-gray-800 mb-4">Bukti Pembelian</h3>
                @if ($do?->proof_path)
                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                        @php
                            $ext = pathinfo($do->proof_path, PATHINFO_EXTENSION);
                            $url = asset('storage/' . $do->proof_path);
                        @endphp
                        @if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png']))
                            <img src="{{ $url }}" class="max-w-full max-h-96 mx-auto rounded-lg shadow mb-4">
                        @elseif (strtolower($ext) === 'pdf')
                            <iframe src="{{ $url }}" class="w-full h-96 rounded-lg border mb-4" frameborder="0"></iframe>
                        @else
                            <div class="text-center py-4 text-gray-600">
                                <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                                <p class="font-semibold">{{ strtoupper($ext) }} File</p>
                            </div>
                        @endif
                        <div class="flex justify-center gap-3">
                            <a href="{{ $url }}" target="_blank"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-semibold">
                                Lihat Penuh
                            </a>
                            <a href="{{ $url }}" download
                                class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-sm font-semibold">
                                Download
                            </a>
                        </div>
                    </div>
                @else
                    <p class="text-gray-600 mb-4">Belum ada bukti yang diupload.</p>
                @endif

                @if ($do?->canBeEdited() && $isCreator)
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <h4 class="text-sm font-bold text-gray-700 mb-3">Update Bukti</h4>
                        <div class="flex items-center gap-3">
                            <input type="file" wire:model.live="newProofFile"
                                accept=".pdf,.jpg,.jpeg,.png"
                                class="text-sm border border-gray-300 rounded-lg px-3 py-2">
                        </div>
                        @if ($newProofFile)
                            <div class="mt-2 text-sm text-green-600 font-semibold">
                                ✓ {{ $newProofFile->getClientOriginalName() }}
                                @if (in_array($newProofFile->extension(), ['jpg', 'jpeg', 'png']))
                                    <img src="{{ $newProofFile->temporaryUrl() }}" class="max-w-full max-h-48 mt-2 rounded-lg shadow">
                                @endif
                            </div>
                        @endif
                        @error('newProofFile') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        <button type="button" wire:click="updateProof"
                            class="mt-3 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-semibold">
                            Update Bukti
                        </button>
                    </div>
                @endif
            </div>

            {{-- ITEMS TABLE --}}
            <div class="bg-white rounded-xl shadow border p-6">
                <h3 class="text-base font-bold text-gray-800 mb-4">Detail Barang</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-3 py-3 text-left font-bold text-gray-700">Item</th>
                                <th class="px-3 py-3 text-center font-bold text-gray-700">Qty</th>
                                <th class="px-3 py-3 text-center font-bold text-gray-700">UoM</th>
                                <th class="px-3 py-3 text-right font-bold text-gray-700">Harga Satuan</th>
                                <th class="px-3 py-3 text-right font-bold text-gray-700">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse ($do?->items ?? [] as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-3">{{ $item->item?->name ?? 'Unknown' }}</td>
                                    <td class="px-3 py-3 text-center">{{ $item->quantity }}</td>
                                    <td class="px-3 py-3 text-center">{{ $item->uom?->name ?? '-' }}</td>
                                    <td class="px-3 py-3 text-right">
                                        @if ($do?->canBeEdited())
                                            <input type="number" wire:model.live="itemPrices.{{ $item->id }}" step="0.01" min="0"
                                                class="w-32 px-2 py-1 border border-gray-300 rounded text-right text-sm">
                                        @else
                                            Rp {{ number_format($item->unit_price ?? 0, 2, ',', '.') }}
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-right font-semibold">
                                        @if ($do?->canBeEdited())
                                            @php
                                                $price = (float) ($itemPrices[$item->id] ?? 0);
                                                $total = $price * $item->quantity;
                                            @endphp
                                            Rp {{ number_format($total, 2, ',', '.') }}
                                        @else
                                            Rp {{ number_format($item->total_price ?? 0, 2, ',', '.') }}
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-3 py-4 text-center text-gray-500">Tidak ada item</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($do?->canBeEdited())
                            <tfoot class="bg-gray-50 border-t">
                                <tr>
                                    <td colspan="4" class="px-3 py-3 text-right font-bold text-gray-700">Grand Total:</td>
                                    <td class="px-3 py-3 text-right font-bold text-teal-600">
                                        @php
                                            $grandTotal = 0;
                                            foreach ($do->items as $item) {
                                                $grandTotal += (float) ($itemPrices[$item->id] ?? 0) * $item->quantity;
                                            }
                                        @endphp
                                        Rp {{ number_format($grandTotal, 2, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>

                @if ($do?->canBeEdited())
                    <div class="mt-4 flex justify-end">
                        <button type="button" wire:click="updateItemPrices"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-semibold">
                            Simpan Harga
                        </button>
                    </div>
                @endif
            </div>

            {{-- APPROVAL FLOW --}}
            <div class="bg-white rounded-xl shadow border p-6">
                <h3 class="text-base font-bold text-gray-800 mb-4">Alur Approval</h3>

                <div class="space-y-4">
                    {{-- LEVEL 0: DRAFT --}}
                    <div class="flex items-start gap-4">
                        <div class="flex flex-col items-center">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white
                                {{ $do?->status === 'revised' ? 'bg-orange-500' : 'bg-green-500' }}">
                                {{ $do?->status === 'revised' ? '↻' : '✓' }}
                            </div>
                            @if ($do?->status !== 'approved' && $do?->status !== 'rejected')
                                <div class="w-1 h-12 bg-gray-300 mt-1"></div>
                            @endif
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">Draft - Preparation</h4>
                            <p class="text-xs text-gray-600">Direct Order dibuat dan disiapkan untuk submission</p>
                            @if ($do?->status === 'revised')
                                <div class="mt-2 p-3 bg-orange-50 rounded-lg border border-orange-200">
                                    <p class="text-xs font-semibold text-orange-800">↻ Revisi - Menunggu perbaikan</p>
                                    @if ($do?->revise_reason)
                                        <p class="text-xs text-gray-700 mt-1 italic">{{ $do?->revise_reason }}</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- LEVEL 1: RM APPROVAL --}}
                    <div class="flex items-start gap-4">
                        <div class="flex flex-col items-center">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white
                                {{ $do?->approval_level >= 1 ? 'bg-green-500' : 'bg-gray-300' }}">
                                1
                            </div>
                            @if ($do?->approval_level < 2 && $do?->status !== 'approved' && $do?->status !== 'rejected')
                                <div class="w-1 h-12 bg-gray-300 mt-1"></div>
                            @endif
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-800">RM Approval</h4>
                            <p class="text-xs text-gray-600">Resource Manager melakukan review dan approval</p>

                            @if ($do?->rm_approved_at)
                                <div class="mt-2 p-3 bg-green-50 rounded-lg border border-green-200">
                                    <p class="text-xs font-semibold text-green-800">✓ Approved</p>
                                    <p class="text-xs text-gray-600 mt-1">{{ $do?->rm_approved_at?->format('d/m/Y H:i') }}</p>
                                    @if ($do?->rm_notes)
                                        <p class="text-xs text-gray-700 mt-1 italic">{{ $do?->rm_notes }}</p>
                                    @endif
                                </div>
                            @elseif ($do?->isPendingRM())
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
                                {{ $do?->approval_level >= 2 ? 'bg-green-500' : 'bg-gray-300' }}">
                                2
                            </div>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-800">Supervisor Approval (Final)</h4>
                            <p class="text-xs text-gray-600">Supervisor melakukan final approval</p>

                            @if ($do?->spv_approved_at)
                                <div class="mt-2 p-3 bg-green-50 rounded-lg border border-green-200">
                                    <p class="text-xs font-semibold text-green-800">✓ Approved - DO Ready</p>
                                    <p class="text-xs text-gray-600 mt-1">{{ $do?->spv_approved_at?->format('d/m/Y H:i') }}</p>
                                    @if ($do?->spv_notes)
                                        <p class="text-xs text-gray-700 mt-1 italic">{{ $do?->spv_notes }}</p>
                                    @endif
                                </div>
                            @elseif ($do?->isPendingSPV())
                                <div class="mt-2 p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                                    <p class="text-xs font-semibold text-yellow-800">Menunggu approval SPV</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- REJECTED STATUS --}}
                    @if ($do?->status === 'rejected')
                        <div class="flex items-start gap-4">
                            <div class="flex flex-col items-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white bg-red-500">
                                    ✗
                                </div>
                            </div>
                            <div>
                                <h4 class="font-semibold text-red-800">DO Ditolak</h4>
                                @if ($do?->reject_reason)
                                    <div class="mt-2 p-3 bg-red-50 rounded-lg border border-red-200">
                                        <p class="text-xs text-gray-700 italic">{{ $do?->reject_reason }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                {{-- ACTION BUTTONS --}}
                <div class="mt-6 pt-4 border-t border-gray-200 flex justify-between items-center">
                    <a href="{{ route('dashboard.resto.direct-order') }}"
                        class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-sm font-semibold">
                        Kembali
                    </a>
                    <div class="flex gap-2">
                        @if ($do?->canBeEdited() && $isCreator)
                            <button wire:click="submitForApproval"
                                class="px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 text-sm font-semibold">
                                Submit DO
                            </button>
                        @endif
                        @if ($do?->isPendingRM() && $isRMApprover)
                            <button wire:click="approveByRM"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-semibold">
                                ✓ Approve RM
                            </button>
                            <button wire:click="openRejectModal"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-semibold">
                                ✗ Tolak
                            </button>
                            <button wire:click="openReviseModal"
                                class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 text-sm font-semibold">
                                ↻ Revisi
                            </button>
                        @endif
                        @if ($do?->isPendingSPV() && $isSPVApprover)
                            <button wire:click="approveBySPV"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-semibold">
                                ✓ Approve SPV
                            </button>
                            <button wire:click="openRejectModal"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-semibold">
                                ✗ Tolak
                            </button>
                            <button wire:click="openReviseModal"
                                class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 text-sm font-semibold">
                                ↻ Revisi
                            </button>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ================= MODALS ================= --}}

    {{-- REJECT MODAL --}}
    <div x-data="{ open: @entangle('showRejectModal') }" x-show="open" x-cloak
        class="fixed inset-0 bg-black bg-opacity-50 z-40 flex items-center justify-center"
        @click.self="open = false; $wire.closeRejectModal()">
        <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4" @click.stop>
            <h3 class="text-lg font-bold text-red-800 mb-4">Tolak Direct Order</h3>
            <textarea wire:model.live="rejectReason" rows="4"
                placeholder="Alasan penolakan..."
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm mb-2"></textarea>
            @error('rejectReason') <span class="text-red-500 text-xs mb-2 block">{{ $message }}</span> @enderror
            <div class="flex justify-end gap-2">
                <button type="button" @click="open = false; $wire.closeRejectModal()"
                    class="px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400">
                    Batal
                </button>
                <button type="button" wire:click="rejectDO"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Tolak DO
                </button>
            </div>
        </div>
    </div>

    {{-- REVISE MODAL --}}
    <div x-data="{ open: @entangle('showReviseModal') }" x-show="open" x-cloak
        class="fixed inset-0 bg-black bg-opacity-50 z-40 flex items-center justify-center"
        @click.self="open = false; $wire.closeReviseModal()">
        <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4" @click.stop>
            <h3 class="text-lg font-bold text-orange-800 mb-4">Minta Revisi</h3>
            <textarea wire:model.live="reviseReason" rows="4"
                placeholder="Alasan revisi..."
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm mb-2"></textarea>
            @error('reviseReason') <span class="text-red-500 text-xs mb-2 block">{{ $message }}</span> @enderror
            <div class="flex justify-end gap-2">
                <button type="button" @click="open = false; $wire.closeReviseModal()"
                    class="px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400">
                    Batal
                </button>
                <button type="button" wire:click="requestRevision"
                    class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
                    Minta Revisi
                </button>
            </div>
        </div>
    </div>

</x-ui.sccr-card>
