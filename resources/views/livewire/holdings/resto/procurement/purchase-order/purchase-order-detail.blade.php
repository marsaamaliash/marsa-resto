<x-ui.sccr-card transparent wire:key="purchase-order-detail" class="h-full min-h-0 flex flex-col">

    {{-- ================= HEADER ================= --}}
    <div class="relative px-8 py-6 bg-gradient-to-r from-purple-600 to-purple-700 rounded-b-3xl shadow-lg">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">{{ $po?->po_number ?? 'PO Details' }}</h1>
                <p class="text-purple-100 text-sm mt-1">
                    Status: <span class="font-bold">{{ ucfirst(str_replace('_', ' ', $po?->status ?? 'Unknown')) }}</span>
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

            {{-- PO HEADER INFO --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white rounded-xl shadow border p-6">
                    <h3 class="text-base font-bold text-gray-800 mb-4">Informasi PO</h3>
                    @if ($po?->canBeEdited() && $isCreator)
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="font-semibold text-gray-700">Lokasi:</dt>
                                <dd class="text-gray-600">{{ $po?->location?->name }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="font-semibold text-gray-700">PR Number:</dt>
                                <dd class="text-gray-600">{{ $po?->purchaseRequest?->pr_number }}</dd>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Vendor <span class="text-red-500">*</span></label>
                                <select wire:model.live="selectedVendorId"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                    <option value="0">-- Pilih Vendor --</option>
                                    @foreach ($vendors as $vendor)
                                        <option value="{{ $vendor['id'] }}">{{ $vendor['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Pembayaran Dilakukan Oleh <span class="text-red-500">*</span></label>
                                <div class="space-y-2">
                                    <label class="flex items-center">
                                        <input type="radio" wire:model.live="paymentBy" value="holding"
                                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <span class="ml-2 text-gray-700">Holding (Pusat)</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" wire:model.live="paymentBy" value="resto"
                                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <span class="ml-2 text-gray-700">Resto (Cabang)</span>
                                    </label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan</label>
                                <textarea wire:model.live="poNotes" rows="2"
                                    placeholder="Catatan PO..."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></textarea>
                            </div>
                            <div class="pt-2">
                                <button type="button" wire:click="updatePODetails"
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-semibold">
                                    Simpan Detail
                                </button>
                            </div>
                        </div>
                    @else
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="font-semibold text-gray-700">PO Number:</dt>
                                <dd class="text-gray-600">{{ $po?->po_number }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="font-semibold text-gray-700">Vendor:</dt>
                                <dd class="text-gray-600">{{ $po?->vendor_name }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="font-semibold text-gray-700">PR Number:</dt>
                                <dd class="text-gray-600">{{ $po?->purchaseRequest?->pr_number }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="font-semibold text-gray-700">Status:</dt>
                                <dd>
                                    <span class="px-2 py-1 rounded text-white text-xs font-bold
                                        {{ $po?->status === 'draft' ? 'bg-gray-500' : '' }}
                                        {{ $po?->status === 'pending_rm' ? 'bg-yellow-500' : '' }}
                                        {{ $po?->status === 'pending_spv' ? 'bg-blue-500' : '' }}
                                        {{ $po?->status === 'approved' ? 'bg-green-500' : '' }}
                                        {{ $po?->status === 'rejected' ? 'bg-red-500' : '' }}
                                        {{ $po?->status === 'revised' ? 'bg-orange-500' : '' }}">
                                        {{ ucfirst(str_replace('_', ' ', $po?->status)) }}
                                    </span>
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="font-semibold text-gray-700">Payment By:</dt>
                                <dd class="text-gray-600">{{ ucfirst($po?->payment_by) }}</dd>
                            </div>
                            @if ($po?->notes)
                                <div class="mt-2 pt-2 border-t border-gray-200">
                                    <dt class="font-semibold text-gray-700">Catatan:</dt>
                                    <dd class="text-gray-600 italic">{{ $po?->notes }}</dd>
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
                            <dd class="text-lg font-bold text-blue-600">
                                Rp {{ number_format($po?->total_amount ?? 0, 2, ',', '.') }}
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="font-semibold text-gray-700">Lokasi:</dt>
                            <dd class="text-gray-600">{{ $po?->location?->name }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="font-semibold text-gray-700">Created:</dt>
                            <dd class="text-gray-600">{{ $po?->created_at?->format('d/m/Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- QUOTATION SECTION --}}
            <div class="bg-white rounded-xl shadow border p-6">
                <h3 class="text-base font-bold text-gray-800 mb-4">Quotation / Bukti Pembelian</h3>
                @if ($po?->quotation_path)
                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                        @php
                            $ext = pathinfo($po->quotation_path, PATHINFO_EXTENSION);
                            $url = asset('storage/' . $po->quotation_path);
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
                    <p class="text-gray-600 mb-4">Belum ada quotation yang diupload.</p>
                @endif

                @if ($po?->canBeEdited() && $isCreator)
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <h4 class="text-sm font-bold text-gray-700 mb-3">Update Quotation</h4>
                        <div class="flex items-center gap-3">
                            <input type="file" wire:model.live="newQuotationFile"
                                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                class="text-sm border border-gray-300 rounded-lg px-3 py-2">
                        </div>
                        @if ($newQuotationFile)
                            <div class="mt-2 text-sm text-green-600 font-semibold">
                                ✓ {{ $newQuotationFile->getClientOriginalName() }}
                                @if (in_array($newQuotationFile->extension(), ['jpg', 'jpeg', 'png']))
                                    <img src="{{ $newQuotationFile->temporaryUrl() }}" class="max-w-full max-h-48 mt-2 rounded-lg shadow">
                                @endif
                            </div>
                        @endif
                        @error('newQuotationFile') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        <button type="button" wire:click="updateQuotation"
                            class="mt-3 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-semibold">
                            Update Quotation
                        </button>
                    </div>
                @endif
            </div>

            {{-- PO ITEMS TABLE --}}
            <div class="bg-white rounded-xl shadow border p-6">
                <h3 class="text-base font-bold text-gray-800 mb-4">Item Detail</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-3 py-3 text-left font-bold text-gray-700">Item</th>
                                <th class="px-3 py-3 text-center font-bold text-gray-700">Qty</th>
                                <th class="px-3 py-3 text-center font-bold text-gray-700">UoM</th>
                                <th class="px-3 py-3 text-right font-bold text-gray-700">Unit Price</th>
                                <th class="px-3 py-3 text-right font-bold text-gray-700">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse ($po?->items ?? [] as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-3">{{ $item->item?->name ?? 'Unknown' }}</td>
                                    <td class="px-3 py-3 text-center">{{ $item->ordered_qty }}</td>
                                    <td class="px-3 py-3 text-center">{{ $item->uom?->name ?? '-' }}</td>
                                    <td class="px-3 py-3 text-right">
                                        @if ($po?->canBeEdited())
                                            <input type="number" wire:model.live="itemPrices.{{ $item->id }}" step="0.01" min="0"
                                                class="w-32 px-2 py-1 border border-gray-300 rounded text-right text-sm">
                                        @else
                                            Rp {{ number_format($item->unit_price ?? 0, 2, ',', '.') }}
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-right font-semibold">
                                        @if ($po?->canBeEdited())
                                            @php
                                                $price = (float) ($itemPrices[$item->id] ?? 0);
                                                $total = $price * $item->ordered_qty;
                                            @endphp
                                            Rp {{ number_format($total, 2, ',', '.') }}
                                        @else
                                            Rp {{ number_format($item->total_price ?? 0, 2, ',', '.') }}
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-3 py-4 text-center text-gray-500">No items</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($po?->canBeEdited())
                            <tfoot class="bg-gray-50 border-t">
                                <tr>
                                    <td colspan="4" class="px-3 py-3 text-right font-bold text-gray-700">Grand Total:</td>
                                    <td class="px-3 py-3 text-right font-bold text-blue-600">
                                        @php
                                            $grandTotal = 0;
                                            foreach ($po->items as $item) {
                                                $grandTotal += (float) ($itemPrices[$item->id] ?? 0) * $item->ordered_qty;
                                            }
                                        @endphp
                                        Rp {{ number_format($grandTotal, 2, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>

                @if ($po?->canBeEdited())
                    <div class="mt-4 flex justify-end">
                        <button type="button" wire:click="updateItemPrices"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-semibold">
                            Simpan Harga
                        </button>
                    </div>
                @endif
            </div>

            {{-- RECEIPT & PAYMENT STATUS --}}
            @if ($po?->isApproved())
                <div class="bg-white rounded-xl shadow border p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-base font-bold text-gray-800">Penerimaan & Pembayaran</h3>
                        @if ($po->canReceiveGoods())
                            <a href="{{ route('dashboard.resto.goods-receipt.create-from-po', $po->id) }}"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-semibold">
                                <x-ui.sccr-icon name="plus" :size="16" class="inline" />
                                Buat Goods Receipt
                            </a>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <span class="text-xs font-bold text-gray-500 uppercase">Received Status</span>
                            <p>
                                @php
                                    $receivedStatusColor = match($po->received_status) {
                                        'not_received' => 'bg-gray-100 text-gray-800',
                                        'partial' => 'bg-yellow-100 text-yellow-800',
                                        'fully_received' => 'bg-green-100 text-green-800',
                                        default => 'bg-gray-100 text-gray-800',
                                    };
                                    $receivedStatusLabel = match($po->received_status) {
                                        'not_received' => 'Not Received',
                                        'partial' => 'Partial',
                                        'fully_received' => 'Fully Received',
                                        default => ucfirst($po->received_status),
                                    };
                                @endphp
                                <span class="px-3 py-1 rounded text-sm font-semibold {{ $receivedStatusColor }}">
                                    {{ $receivedStatusLabel }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-gray-500 uppercase">Payment Status</span>
                            <p>
                                @php
                                    $paymentStatusColor = match($po->payment_status) {
                                        'unpaid' => 'bg-red-100 text-red-800',
                                        'pending_finance' => 'bg-yellow-100 text-yellow-800',
                                        'paid' => 'bg-green-100 text-green-800',
                                        default => 'bg-gray-100 text-gray-800',
                                    };
                                    $paymentStatusLabel = match($po->payment_status) {
                                        'unpaid' => 'Unpaid',
                                        'pending_finance' => 'Pending Finance',
                                        'paid' => 'Paid',
                                        default => ucfirst($po->payment_status),
                                    };
                                @endphp
                                <span class="px-3 py-1 rounded text-sm font-semibold {{ $paymentStatusColor }}">
                                    {{ $paymentStatusLabel }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-gray-500 uppercase">Invoice</span>
                            <p class="text-sm font-mono">{{ $po->invoice_number ?? '-' }}</p>
                        </div>
                    </div>

                    @if ($po->goodsReceipts->count() > 0)
                        <div class="mt-4 pt-4 border-t">
                            <h4 class="text-sm font-bold text-gray-700 mb-3">Goods Receipt History</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-xs">
                                    <thead class="bg-gray-50 border-b">
                                        <tr>
                                            <th class="px-3 py-2 text-left font-bold text-gray-700">Receipt Number</th>
                                            <th class="px-3 py-2 text-center font-bold text-gray-700">Status</th>
                                            <th class="px-3 py-2 text-center font-bold text-gray-700">Tanggal Terima</th>
                                            <th class="px-3 py-2 text-center font-bold text-gray-700">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($po->goodsReceipts as $gr)
                                            <tr class="border-b hover:bg-gray-50">
                                                <td class="px-3 py-2 font-mono">{{ $gr->receipt_number }}</td>
                                                <td class="px-3 py-2 text-center">
                                                    @php
                                                        $grStatusColor = match($gr->status) {
                                                            'draft' => 'bg-gray-100 text-gray-800',
                                                            'pending_rm' => 'bg-yellow-100 text-yellow-800',
                                                            'pending_spv' => 'bg-blue-100 text-blue-800',
                                                            'approved' => 'bg-green-100 text-green-800',
                                                            'rejected' => 'bg-red-100 text-red-800',
                                                            default => 'bg-gray-100 text-gray-800',
                                                        };
                                                        $grStatusLabel = match($gr->status) {
                                                            'draft' => 'Draft',
                                                            'pending_rm' => 'Pending RM',
                                                            'pending_spv' => 'Pending SPV',
                                                            'approved' => 'Approved',
                                                            'rejected' => 'Rejected',
                                                            default => ucfirst($gr->status),
                                                        };
                                                    @endphp
                                                    <span class="px-2 py-0.5 rounded text-xs {{ $grStatusColor }}">
                                                        {{ $grStatusLabel }}
                                                    </span>
                                                </td>
                                                <td class="px-3 py-2 text-center">{{ $gr->received_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                                <td class="px-3 py-2 text-center">
                                                    <a href="{{ route('dashboard.resto.goods-receipt.detail', $gr->id) }}"
                                                        class="text-blue-600 hover:underline">
                                                        Detail
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- APPROVAL FLOW --}}
            <div class="bg-white rounded-xl shadow border p-6">
                <h3 class="text-base font-bold text-gray-800 mb-4">Alur Approval</h3>

                <div class="space-y-4">
                    {{-- LEVEL 0: DRAFT --}}
                    <div class="flex items-start gap-4">
                        <div class="flex flex-col items-center">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white
                                {{ $po?->status === 'revised' ? 'bg-orange-500' : 'bg-green-500' }}">
                                {{ $po?->status === 'revised' ? '↻' : '✓' }}
                            </div>
                            @if ($po?->status !== 'approved' && $po?->status !== 'rejected')
                                <div class="w-1 h-12 bg-gray-300 mt-1"></div>
                            @endif
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">Draft - Preparation</h4>
                            <p class="text-xs text-gray-600">PO dibuat dan disiapkan untuk submission</p>
                            @if ($po?->status === 'revised')
                                <div class="mt-2 p-3 bg-orange-50 rounded-lg border border-orange-200">
                                    <p class="text-xs font-semibold text-orange-800">↻ Revisi - Menunggu perbaikan</p>
                                    @if ($po?->revise_reason)
                                        <p class="text-xs text-gray-700 mt-1 italic">{{ $po?->revise_reason }}</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- LEVEL 1: RM APPROVAL --}}
                    <div class="flex items-start gap-4">
                        <div class="flex flex-col items-center">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white
                                {{ $po?->approval_level >= 1 ? 'bg-green-500' : 'bg-gray-300' }}">
                                1
                            </div>
                            @if ($po?->approval_level < 2 && $po?->status !== 'approved' && $po?->status !== 'rejected')
                                <div class="w-1 h-12 bg-gray-300 mt-1"></div>
                            @endif
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-800">RM Approval</h4>
                            <p class="text-xs text-gray-600">Resource Manager melakukan review dan approval</p>

                            @if ($po?->rm_approved_at)
                                <div class="mt-2 p-3 bg-green-50 rounded-lg border border-green-200">
                                    <p class="text-xs font-semibold text-green-800">✓ Approved</p>
                                    <p class="text-xs text-gray-600 mt-1">{{ $po?->rm_approved_at?->format('d/m/Y H:i') }}</p>
                                    @if ($po?->rm_notes)
                                        <p class="text-xs text-gray-700 mt-1 italic">{{ $po?->rm_notes }}</p>
                                    @endif
                                </div>
                            @elseif ($po?->isPendingRM())
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
                                {{ $po?->approval_level >= 2 ? 'bg-green-500' : 'bg-gray-300' }}">
                                2
                            </div>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-800">Supervisor Approval (Final)</h4>
                            <p class="text-xs text-gray-600">Supervisor melakukan final approval</p>

                            @if ($po?->spv_approved_at)
                                <div class="mt-2 p-3 bg-green-50 rounded-lg border border-green-200">
                                    <p class="text-xs font-semibold text-green-800">✓ Approved</p>
                                    <p class="text-xs text-gray-600 mt-1">{{ $po?->spv_approved_at?->format('d/m/Y H:i') }}</p>
                                    @if ($po?->spv_notes)
                                        <p class="text-xs text-gray-700 mt-1 italic">{{ $po?->spv_notes }}</p>
                                    @endif
                                </div>
                            @elseif ($po?->isPendingSPV())
                                <div class="mt-2 p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                                    <p class="text-xs font-semibold text-yellow-800">Menunggu approval SPV</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- REJECTED STATUS --}}
                    @if ($po?->status === 'rejected')
                        <div class="flex items-start gap-4">
                            <div class="flex flex-col items-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white bg-red-500">
                                    ✗
                                </div>
                            </div>
                            <div>
                                <h4 class="font-semibold text-red-800">PO Ditolak</h4>
                                @if ($po?->reject_reason)
                                    <div class="mt-2 p-3 bg-red-50 rounded-lg border border-red-200">
                                        <p class="text-xs text-gray-700 italic">{{ $po?->reject_reason }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                {{-- ACTION BUTTONS --}}
                <div class="mt-6 pt-4 border-t border-gray-200 flex justify-between items-center">
                    <a href="{{ route('dashboard.resto.purchase-order') }}"
                        class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-sm font-semibold">
                        Kembali
                    </a>
                    <div class="flex gap-2">
                        @if ($po?->canBeEdited() && $isCreator)
                            <button wire:click="submitForApproval"
                                class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 text-sm font-semibold">
                                Submit PO
                            </button>
                        @endif
                        @if ($po?->isPendingRM() && $isRMApprover)
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
                        @if ($po?->isPendingSPV() && $isSPVApprover)
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
            <h3 class="text-lg font-bold text-red-800 mb-4">Tolak PO</h3>
            <textarea wire:model.live="rejectReason" rows="4"
                placeholder="Alasan penolakan..."
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm mb-2"></textarea>
            @error('rejectReason') <span class="text-red-500 text-xs mb-2 block">{{ $message }}</span> @enderror
            <div class="flex justify-end gap-2">
                <button type="button" @click="open = false; $wire.closeRejectModal()"
                    class="px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400">
                    Batal
                </button>
                <button type="button" wire:click="rejectPO"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Tolak PO
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
