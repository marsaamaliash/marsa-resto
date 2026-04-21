<x-ui.sccr-card transparent wire:key="invoice-detail" class="h-full min-h-0 flex flex-col">

    {{-- ================= HEADER ================= --}}
    <div class="relative px-8 py-6 bg-gradient-to-r from-indigo-600 to-indigo-700 rounded-b-3xl shadow-lg">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">Detail Invoice</h1>
                <p class="text-indigo-100 text-sm mt-1">{{ $po?->po_number ?? '-' }}</p>
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

            @if ($po)
                {{-- PO INFO CARD --}}
                <div class="bg-white rounded-xl shadow border p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <span class="text-xs font-bold text-gray-500 uppercase">PO Number</span>
                            <p class="text-lg font-mono font-bold text-blue-700">
                                <a href="{{ route('dashboard.resto.purchase-order.detail', $po->id) }}" class="hover:underline">
                                    {{ $po->po_number }}
                                </a>
                            </p>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-gray-500 uppercase">Vendor</span>
                            <p class="text-sm font-semibold">{{ $po->vendor_name }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-gray-500 uppercase">Lokasi</span>
                            <p class="text-sm font-semibold">{{ $po->location?->name ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-gray-500 uppercase">Total Amount</span>
                            <p class="text-lg font-bold text-green-700">Rp {{ number_format($po->total_amount ?? 0, 2, ',', '.') }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-gray-500 uppercase">Payment By</span>
                            <p>
                                <span class="px-2 py-0.5 rounded text-xs font-semibold
                                    {{ $po->payment_by === 'holding' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                    {{ ucfirst($po->payment_by) }}
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
                    </div>
                </div>

                {{-- INVOICE INFO CARD --}}
                <div class="bg-white rounded-xl shadow border p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-bold text-gray-800">Informasi Invoice</h2>
                        @if ($po->isFullyReceived() && ($po->isUnpaid() || $po->isPendingFinance()))
                            <x-ui.sccr-button type="button" wire:click="$set('showUploadModal', true)"
                                class="bg-indigo-600 text-white hover:bg-indigo-700">
                                <x-ui.sccr-icon name="upload" :size="16" />
                                Upload Invoice
                            </x-ui.sccr-button>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <span class="text-xs font-bold text-gray-500 uppercase">Invoice Number</span>
                            <p class="text-sm font-mono font-semibold">{{ $po->invoice_number ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-gray-500 uppercase">Invoice Date</span>
                            <p class="text-sm">{{ $po->invoice_date?->format('d/m/Y') ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-gray-500 uppercase">Invoice File</span>
                            <p class="text-sm">
                                @if ($po->invoice_path)
                                    <a href="{{ Storage::url($po->invoice_path) }}" target="_blank"
                                        class="text-blue-600 hover:underline">
                                        <x-ui.sccr-icon name="file" :size="14" class="inline" />
                                        Download Invoice
                                    </a>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    @if ($po->payment_status === 'paid')
                        <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                            <p class="text-sm font-bold text-green-700">Invoice sudah dibayar</p>
                        </div>
                    @endif
                </div>

                {{-- GOODS RECEIPT HISTORY --}}
                @if ($po->goodsReceipts->count() > 0)
                    <div class="bg-white rounded-xl shadow border p-6">
                        <h2 class="text-lg font-bold text-gray-800 mb-4">Riwayat Penerimaan Barang</h2>

                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50 border-b">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-bold text-gray-700">Receipt Number</th>
                                        <th class="px-3 py-2 text-center font-bold text-gray-700">Status</th>
                                        <th class="px-3 py-2 text-center font-bold text-gray-700">Tanggal Terima</th>
                                        <th class="px-3 py-2 text-left font-bold text-gray-700">Approved By (RM)</th>
                                        <th class="px-3 py-2 text-left font-bold text-gray-700">Approved By (SPV)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($po->goodsReceipts as $gr)
                                        <tr class="border-b hover:bg-gray-50">
                                            <td class="px-3 py-2 font-mono text-sm">
                                                <a href="{{ route('dashboard.resto.goods-receipt.detail', $gr->id) }}" class="text-blue-600 hover:underline">
                                                    {{ $gr->receipt_number }}
                                                </a>
                                            </td>
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
                                            <td class="px-3 py-2 text-sm text-center">{{ $gr->received_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                            <td class="px-3 py-2 text-sm">{{ $gr->rm_approved_by ? 'Yes' : '-' }}</td>
                                            <td class="px-3 py-2 text-sm">{{ $gr->spv_approved_by ? 'Yes' : '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- ACTION BUTTONS --}}
                <div class="flex justify-end gap-4 pb-4">
                    @if ($po->payment_status === 'pending_finance')
                        <x-ui.sccr-button type="button" wire:click="markAsPaid"
                            class="bg-green-600 text-white hover:bg-green-700">
                            Mark as Paid
                        </x-ui.sccr-button>
                    @endif

                    <a href="{{ route('dashboard.resto.invoice') }}"
                        class="px-6 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500">
                        Kembali
                    </a>
                </div>
            @endif

        </div>
    </div>

    {{-- ================= UPLOAD INVOICE MODAL ================= --}}
    @if ($showUploadModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click="$set('showUploadModal', false)">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden" wire:click.stop>
                <div class="px-6 py-4 border-b bg-indigo-600 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-white">Upload Invoice</h3>
                    <button wire:click="$set('showUploadModal', false)" class="text-white hover:text-gray-200 text-2xl">&times;</button>
                </div>

                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">
                            Invoice Number
                        </label>
                        <input type="text" wire:model="invoiceNumber"
                            class="w-full border-gray-300 rounded-md text-sm"
                            placeholder="Nomor invoice dari vendor...">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">
                            Invoice Date
                        </label>
                        <input type="date" wire:model="invoiceDate"
                            class="w-full border-gray-300 rounded-md text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">
                            Upload Invoice File
                        </label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center cursor-pointer hover:border-indigo-500"
                            onclick="document.getElementById('invoice-file-input').click()">
                            <input type="file" wire:model.live="invoiceFile"
                                accept=".pdf,.jpg,.jpeg,.png"
                                class="hidden" id="invoice-file-input">
                            @if ($invoiceFile)
                                <div class="text-green-600 font-semibold mb-3">✓ {{ $invoiceFile->getClientOriginalName() }}</div>
                                @if (in_array($invoiceFile->extension(), ['jpg', 'jpeg', 'png']))
                                    <img src="{{ $invoiceFile->temporaryUrl() }}" class="max-w-full max-h-64 mx-auto rounded-lg shadow">
                                @elseif ($invoiceFile->extension() === 'pdf')
                                    <div class="text-sm text-gray-600">PDF: {{ round($invoiceFile->getSize() / 1024, 1) }} KB</div>
                                @endif
                            @else
                                <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                <p class="text-sm text-gray-600">Click atau drag invoice file (PDF, JPG, PNG)</p>
                                <p class="text-xs text-gray-500 mt-1">Max 5MB</p>
                            @endif
                        </div>
                        @error('invoiceFile') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="px-6 py-4 border-t bg-gray-50 flex justify-end gap-2">
                    <x-ui.sccr-button type="button" wire:click="$set('showUploadModal', false)"
                        class="bg-gray-500 text-white hover:bg-gray-600">
                        Batal
                    </x-ui.sccr-button>

                    <x-ui.sccr-button type="button" wire:click="uploadInvoice"
                        class="bg-indigo-600 text-white hover:bg-indigo-700">
                        Upload
                    </x-ui.sccr-button>
                </div>
            </div>
        </div>
    @endif

</x-ui.sccr-card>
