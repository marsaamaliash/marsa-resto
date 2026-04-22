<x-ui.sccr-card transparent wire:key="purchase-order-create" class="h-full min-h-0 flex flex-col">

    {{-- ================= HEADER ================= --}}
    <div class="relative px-8 py-6 bg-gradient-to-r from-blue-600 to-blue-700 rounded-b-3xl shadow-lg">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">Create Purchase Order</h1>
                <p class="text-blue-100 text-sm mt-1">Create PO from approved Purchase Requests</p>
            </div>
        </div>

        <div class="mt-4 flex justify-between items-center text-sm">
            <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
        </div>
    </div>

    {{-- ================= MAIN FORM ================= --}}
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

            {{-- STEP 1: SELECT LOCATION & APPROVED PR --}}
            <div class="bg-white rounded-xl shadow border p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Step 1: Select Location and Approved PR</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- LOCATION --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Location <span class="text-red-500">*</span></label>
                        <select wire:model.live="selectedLocationId"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="0">-- Select Location --</option>
                            @foreach ($locations as $loc)
                                <option value="{{ $loc['id'] }}">{{ $loc['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- APPROVED PR --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Purchase Request <span class="text-red-500">*</span></label>
                        <select wire:model.live="selectedPRId"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            {{ $selectedLocationId == 0 ? 'disabled' : '' }}>
                            <option value="0">-- Select Approved PR --</option>
                            @foreach ($approvedPRs as $pr)
                                @if ($pr['has_available_items'] ?? false)
                                    <option value="{{ $pr['id'] }}">
                                        {{ $pr['pr_number'] }} - {{ $pr['notes'] ?? 'No notes' }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- SELECTED PR ITEMS --}}
                @if (!empty($selectedPRItems))
                    <div class="mt-6">
                        <h3 class="text-base font-bold text-gray-800 mb-3">Items from Selected PR:</h3>

                        

                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50 border-b">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-bold text-gray-700">Item</th>
                                        <th class="px-3 py-2 text-center font-bold text-gray-700">Qty</th>
                                        <th class="px-3 py-2 text-center font-bold text-gray-700">UoM</th>
                                        <th class="px-3 py-2 text-right font-bold text-gray-700">Unit Price <span class="text-red-500">*</span></th>
                                        <th class="px-3 py-2 text-right font-bold text-gray-700">Total</th>
                                        <th class="px-3 py-2 text-center font-bold text-gray-700">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($selectedPRItems as $item)
                                        <tr class="border-b hover:bg-gray-50" wire:key="item-{{ $item['id'] }}">
                                            <td class="px-3 py-2">{{ $item['item']['name'] ?? 'Unknown' }}</td>
                                            <td class="px-3 py-2 text-center">{{ $item['requested_qty'] }}</td>
                                            <td class="px-3 py-2 text-center">{{ $item['uom']['name'] ?? $item['uom_id'] ?? '-' }}</td>
                                            <td class="px-3 py-2 text-right">
                                                <input type="number" wire:model.live="itemPrices.{{ $item['id'] }}" step="0.01" min="0"
                                                    class="w-32 px-2 py-1 border border-gray-300 rounded text-right text-sm">
                                            </td>
                                            <td class="px-3 py-2 text-right font-semibold">
                                                @php
                                                    $price = (float) ($itemPrices[$item['id']] ?? 0);
                                                    $qty = (float) ($item['requested_qty'] ?? 0);
                                                @endphp
                                                Rp {{ number_format($price * $qty, 2, ',', '.') }}
                                            </td>
                                            <td class="px-3 py-2 text-center">
                                                <button type="button" wire:click="removeItem({{ $item['id'] }})"
                                                    class="text-red-600 hover:text-red-800 text-sm font-medium">
                                                    Remove
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50 border-t">
                                    <tr>
                                        <td colspan="4" class="px-3 py-2 text-right font-bold text-gray-700">Grand Total:</td>
                                        <td class="px-3 py-2 text-right font-bold text-blue-600">
                                            @php
                                                $grandTotal = 0;
                                                foreach ($selectedPRItems as $it) {
                                                    $grandTotal += floatval($itemPrices[$it['id']] ?? 0) * floatval($it['requested_qty'] ?? 0);
                                                }
                                            @endphp
                                            Rp {{ number_format($grandTotal, 2, ',', '.') }}
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        {{-- VENDOR SELECTION --}}
                        <div class="mt-4">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Vendor <span class="text-red-500">*</span></label>
                            <select wire:model.live="selectedVendorId"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="0">-- Select Vendor --</option>
                                @foreach ($vendors as $vendor)
                                    <option value="{{ $vendor['id'] }}">{{ $vendor['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endif

                {{-- REMOVED ITEMS --}}
                @if (!empty($removedItems))
                    <div class="mt-6">
                        <h3 class="text-base font-bold text-gray-800 mb-3">Removed Items (can be re-added):</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50 border-b">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-bold text-gray-700">Item</th>
                                        <th class="px-3 py-2 text-center font-bold text-gray-700">Qty</th>
                                        <th class="px-3 py-2 text-center font-bold text-gray-700">UoM</th>
                                        <th class="px-3 py-2 text-right font-bold text-gray-700">Unit Price</th>
                                        <th class="px-3 py-2 text-center font-bold text-gray-700">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($removedItems as $prItemId => $removed)
                                        <tr class="border-b bg-gray-50 opacity-75" wire:key="removed-{{ $removed['id'] }}">
                                            <td class="px-3 py-2">{{ $removed['item']['item']['name'] ?? 'Unknown' }}</td>
                                            <td class="px-3 py-2 text-center">{{ $removed['item']['requested_qty'] }}</td>
                                            <td class="px-3 py-2 text-center">{{ $removed['item']['uom']['name'] ?? $removed['item']['uom_id'] ?? '-' }}</td>
                                            <td class="px-3 py-2 text-right">
                                                Rp {{ number_format($removed['price'], 2, ',', '.') }}
                                            </td>
                                            <td class="px-3 py-2 text-center">
                                                <button type="button" wire:click="restoreItem({{ $prItemId }})"
                                                    class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                    Restore
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            {{-- STEP 2: UPLOAD QUOTATION & PAYMENT --}}
            <div class="bg-white rounded-xl shadow border p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Step 2: Upload Quotation, Payment & Notes</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- QUOTATION FILE --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Upload Quotation/Purchase Proof <span class="text-red-500">*</span></label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center cursor-pointer hover:border-blue-500"
                            onclick="document.getElementById('quotation-file-input').click()">
                            <input type="file" wire:model.live="quotationFile"
                                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                class="hidden" id="quotation-file-input">
                            @if ($quotationFile)
                                <div class="text-green-600 font-semibold mb-3">&#10003; {{ $quotationFile->getClientOriginalName() }}</div>
                                @if (in_array($quotationFile->extension(), ['jpg', 'jpeg', 'png']))
                                    <img src="{{ $quotationFile->temporaryUrl() }}" class="max-w-full max-h-64 mx-auto rounded-lg shadow">
                                @elseif ($quotationFile->extension() === 'pdf')
                                    <div class="text-sm text-gray-600">PDF: {{ round($quotationFile->getSize() / 1024, 1) }} KB</div>
                                @else
                                    <div class="text-sm text-gray-600">{{ strtoupper($quotationFile->extension()) }}: {{ round($quotationFile->getSize() / 1024, 1) }} KB</div>
                                @endif
                            @else
                                <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                <p class="text-sm text-gray-600">Click or drag quotation file (PDF, JPG, PNG, DOC)</p>
                                <p class="text-xs text-gray-500 mt-1">Max 5MB</p>
                            @endif
                        </div>
                        @error('quotationFile') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    {{-- PAYMENT BY --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Payment Made By <span class="text-red-500">*</span></label>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="radio" wire:model="paymentBy" value="holding"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-gray-700">Holding (Central)</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" wire:model="paymentBy" value="resto"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-gray-700">Resto (Branch)</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- VENDOR NOTES --}}
                <div class="mt-4">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Notes</label>
                    <textarea wire:model="poNotes" rows="3"
                        placeholder="PO notes (optional)..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>
            </div>

            {{-- ACTION BUTTONS --}}
            <div class="flex justify-end gap-4 pb-4">
                <a href="{{ route('dashboard.resto.purchase-order') }}"
                    class="px-6 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500">
                    Cancel
                </a>
                <button type="button" wire:click="saveDraft"
                    class="px-6 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 font-semibold">
                    Save Draft
                </button>
                <button type="button" wire:click="submitPO"
                    class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold">
                    Submit
                </button>
            </div>

        </div>
    </div>

</x-ui.sccr-card>
