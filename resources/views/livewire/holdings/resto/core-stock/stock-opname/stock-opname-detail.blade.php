<x-ui.sccr-card transparent wire:key="stock-opname-detail" class="h-full min-h-0 flex flex-col">

    <div class="relative px-8 py-6 bg-teal-600/80 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">Stock Opname - Detail</h1>
                <p class="text-teal-100 text-sm">
                    Detail stock opname dan adjustment
                </p>
            </div>
        </div>

        <div class="mt-4 flex justify-between items-center text-sm">
            <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
        </div>
    </div>

    <div class="flex-1 min-h-0 px-4 pb-2 overflow-y-auto">
        @if ($detail)
            <div class="py-4">
                <div class="bg-teal-50 border border-teal-200 rounded-lg p-4 mb-4">
                    <h3 class="font-bold text-lg text-gray-800 mb-3">Opname Information</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-sm">
                        <div>
                            <div class="font-semibold text-gray-600 text-xs uppercase">Reference No.</div>
                            <div class="font-mono font-bold text-teal-700">{{ $detail['reference_number'] ?? '-' }}</div>
                        </div>

                        <div>
                            <div class="font-semibold text-gray-600 text-xs uppercase">Opname ID</div>
                            <div class="font-mono font-bold text-gray-700">#{{ $detail['id'] }}</div>
                        </div>

                        <div>
                            <div class="font-semibold text-gray-600 text-xs uppercase">Lokasi</div>
                            <div>{{ $detail->location?->name ?? '-' }}</div>
                        </div>

                        <div>
                            <div class="font-semibold text-gray-600 text-xs uppercase">Tanggal</div>
                            <div>{{ $detail->opname_date?->format('Y-m-d') ?? '-' }}</div>
                        </div>

                        <div>
                            <div class="font-semibold text-gray-600 text-xs uppercase">Status</div>
                            <div>
                                @if ($detail['status'] === 'draft')
                                    <span class="px-2 py-0.5 rounded bg-gray-200 text-gray-700 text-xs">Draft</span>
                                @elseif ($detail['status'] === 'requested')
                                    <span class="px-2 py-0.5 rounded bg-yellow-100 text-yellow-800 text-xs">Requested</span>
                                @elseif ($detail['status'] === 'completed')
                                    <span class="px-2 py-0.5 rounded bg-green-100 text-green-800 text-xs">Completed</span>
                                @elseif ($detail['status'] === 'rejected')
                                    <span class="px-2 py-0.5 rounded bg-red-100 text-red-800 text-xs">Rejected</span>
                                @elseif ($detail['status'] === 'cancelled')
                                    <span class="px-2 py-0.5 rounded bg-orange-100 text-orange-800 text-xs">Cancelled</span>
                                @else
                                    {{ $detail['status'] }}
                                @endif
                            </div>
                        </div>

                        <div>
                            <div class="font-semibold text-gray-600 text-xs uppercase">Frozen</div>
                            <div>
                                @if ($detail['is_frozen'])
                                    <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 text-xs">Frozen</span>
                                @else
                                    <span class="text-gray-400">Tidak</span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <div class="font-semibold text-gray-600 text-xs uppercase">Checker</div>
                            <div>{{ $detail['checker_name'] ?? '-' }} {{ $detail['checker_role'] ? '(' . $detail['checker_role'] . ')' : '' }}</div>
                        </div>

                        <div>
                            <div class="font-semibold text-gray-600 text-xs uppercase">Witness</div>
                            <div>{{ $detail['witness_name'] ?? '-' }} {{ $detail['witness_role'] ? '(' . $detail['witness_role'] . ')' : '' }}</div>
                        </div>

                        <div>
                            <div class="font-semibold text-gray-600 text-xs uppercase">Remark</div>
                            <div>{{ $detail['remark'] ?? '-' }}</div>
                        </div>
                    </div>
                </div>

                @if ($detail->items->count() > 0)
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-bold text-lg text-gray-800">Items</h3>
                            @if ($detail->items->some(fn ($i) => $i['status'] !== 'match'))
                                <x-ui.sccr-button type="button" wire:click="toggleAdjustmentForm"
                                    class="bg-amber-500 text-white hover:bg-amber-600">
                                    <x-ui.sccr-icon name="edit" :size="18" />
                                    Adjustment ({{ $detail->items->filter(fn ($i) => $i['status'] !== 'match')->count() }} selisih)
                                </x-ui.sccr-button>
                            @endif
                        </div>
                        <div class="bg-white border rounded-lg overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Item</th>
                                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-600 uppercase">Stok Sistem</th>
                                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-600 uppercase">Stok Fisik</th>
                                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-600 uppercase">Selisih</th>
                                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase">Status</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Satuan</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Catatan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($detail->items as $opnameItem)
                                        <tr class="{{ $opnameItem['status'] !== 'match' ? 'bg-yellow-50' : '' }}">
                                            <td class="px-4 py-3">{{ $opnameItem->item?->name ?? '-' }}</td>
                                            <td class="px-4 py-3 text-right font-mono">{{ number_format($opnameItem['system_qty'], 2) }}</td>
                                            <td class="px-4 py-3 text-right font-mono">{{ number_format($opnameItem['physical_qty'], 2) }}</td>
                                            <td class="px-4 py-3 text-right font-mono {{ $opnameItem['difference'] < 0 ? 'text-red-600' : ($opnameItem['difference'] > 0 ? 'text-green-600' : '') }}">
                                                {{ number_format($opnameItem['difference'], 2) }}
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                @if ($opnameItem['status'] === 'match')
                                                    <span class="px-2 py-0.5 rounded bg-green-100 text-green-800 text-xs">Match</span>
                                                @elseif ($opnameItem['status'] === 'surplus')
                                                    <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 text-xs">Surplus</span>
                                                @elseif ($opnameItem['status'] === 'deficit')
                                                    <span class="px-2 py-0.5 rounded bg-red-100 text-red-800 text-xs">Deficit</span>
                                                @else
                                                    {{ $opnameItem['status'] }}
                                                @endif
                                            </td>
                                            <td class="px-4 py-3">{{ $opnameItem->uom?->symbols ?? '' }}</td>
                                            <td class="px-4 py-3 text-sm">{{ $opnameItem['remark'] ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                @if ($showAdjustmentForm)
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-4">
                        <h3 class="font-bold text-lg text-gray-800 mb-3">
                            @if ($adjustmentLocked)
                                Adjustment (Terkunci)
                            @else
                                Form Adjustment
                            @endif
                        </h3>
                        @if (! $adjustmentLocked)
                            <p class="text-sm text-gray-600 mb-4">Ubah stok fisik untuk item yang memiliki selisih. Centang item yang ingin di-adjust.</p>
                        @else
                            <p class="text-sm text-gray-600 mb-4">Adjustment sudah dilakukan dan tidak bisa diubah lagi.</p>
                        @endif

                        <div class="bg-white border rounded-lg overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        @if (! $adjustmentLocked)
                                            <th class="px-3 py-2 text-center text-xs font-bold text-gray-600 uppercase w-12">✓</th>
                                        @endif
                                        <th class="px-3 py-2 text-left text-xs font-bold text-gray-600 uppercase">Item</th>
                                        <th class="px-3 py-2 text-right text-xs font-bold text-gray-600 uppercase">Stok Sistem</th>
                                        <th class="px-3 py-2 text-right text-xs font-bold text-gray-600 uppercase">Stok Fisik</th>
                                        <th class="px-3 py-2 text-right text-xs font-bold text-gray-600 uppercase">Selisih</th>
                                        <th class="px-3 py-2 text-center text-xs font-bold text-gray-600 uppercase">Status</th>
                                        @if (! $adjustmentLocked)
                                            <th class="px-3 py-2 text-left text-xs font-bold text-gray-600 uppercase">Catatan</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($adjustmentItems as $index => $adjItem)
                                        <tr class="{{ $adjItem['status'] !== 'match' ? 'bg-yellow-50' : '' }}">
                                            @if (! $adjustmentLocked)
                                                <td class="px-3 py-2 text-center">
                                                    <input type="checkbox" wire:model="adjustmentItems.{{ $index }}.confirmed"
                                                        class="rounded border-gray-300">
                                                </td>
                                            @endif
                                            <td class="px-3 py-2">{{ $adjItem['item_name'] }}</td>
                                            <td class="px-3 py-2 text-right font-mono">{{ number_format($adjItem['system_qty'], 2) }}</td>
                                            <td class="px-3 py-2 text-right font-mono">
                                                @if ($adjustmentLocked)
                                                    {{ number_format($adjItem['physical_qty'], 2) }}
                                                @else
                                                    <input type="number" step="0.01"
                                                        wire:model="adjustmentItems.{{ $index }}.physical_qty"
                                                        class="w-24 border-gray-300 rounded text-sm text-right">
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 text-right font-mono {{ $adjItem['difference'] < 0 ? 'text-red-600' : ($adjItem['difference'] > 0 ? 'text-green-600' : '') }}">
                                                {{ number_format($adjItem['difference'], 2) }}
                                            </td>
                                            <td class="px-3 py-2 text-center">
                                                @if ($adjItem['status'] === 'match')
                                                    <span class="px-2 py-0.5 rounded bg-green-100 text-green-800 text-xs">Match</span>
                                                @elseif ($adjItem['status'] === 'surplus')
                                                    <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 text-xs">Surplus</span>
                                                @elseif ($adjItem['status'] === 'deficit')
                                                    <span class="px-2 py-0.5 rounded bg-red-100 text-red-800 text-xs">Deficit</span>
                                                @endif
                                            </td>
                                            @if (! $adjustmentLocked)
                                                <td class="px-3 py-2">
                                                    <input type="text" wire:model="adjustmentItems.{{ $index }}.remark"
                                                        class="w-full border-gray-300 rounded text-sm">
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if (! $adjustmentLocked)
                            <div class="flex gap-2 mt-4">
                                <x-ui.sccr-button type="button" wire:click="saveAdjustments"
                                    class="bg-amber-500 text-white hover:bg-amber-600">
                                    <x-ui.sccr-icon name="save" :size="18" />
                                    Simpan Adjustment
                                </x-ui.sccr-button>
                                <x-ui.sccr-button type="button" wire:click="toggleAdjustmentForm"
                                    class="bg-gray-300 text-gray-700 hover:bg-gray-400">
                                    Batal
                                </x-ui.sccr-button>
                            </div>
                        @endif
                    </div>
                @endif

                @if ($detail['status'] === 'completed')
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                        <h3 class="font-bold text-lg text-gray-800 mb-3">Hasil Adjustment</h3>
                        <div class="bg-white border rounded-lg overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Item</th>
                                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-600 uppercase">Stok Sistem</th>
                                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-600 uppercase">Stok Fisik</th>
                                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-600 uppercase">Selisih</th>
                                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase">Status</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Satuan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($detail->items as $opnameItem)
                                        <tr class="{{ $opnameItem['status'] !== 'match' ? 'bg-yellow-50' : '' }}">
                                            <td class="px-4 py-3">{{ $opnameItem->item?->name ?? '-' }}</td>
                                            <td class="px-4 py-3 text-right font-mono">{{ number_format($opnameItem['system_qty'], 2) }}</td>
                                            <td class="px-4 py-3 text-right font-mono">{{ number_format($opnameItem['physical_qty'], 2) }}</td>
                                            <td class="px-4 py-3 text-right font-mono {{ $opnameItem['difference'] < 0 ? 'text-red-600' : ($opnameItem['difference'] > 0 ? 'text-green-600' : '') }}">
                                                {{ number_format($opnameItem['difference'], 2) }}
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                @if ($opnameItem['status'] === 'match')
                                                    <span class="px-2 py-0.5 rounded bg-green-100 text-green-800 text-xs">Match</span>
                                                @elseif ($opnameItem['status'] === 'surplus')
                                                    <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 text-xs">Surplus</span>
                                                @elseif ($opnameItem['status'] === 'deficit')
                                                    <span class="px-2 py-0.5 rounded bg-red-100 text-red-800 text-xs">Deficit</span>
                                                @else
                                                    {{ $opnameItem['status'] }}
                                                @endif
                                            </td>
                                            <td class="px-4 py-3">{{ $opnameItem->uom?->symbols ?? '' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                @if ($detail['status'] === 'draft')
                    <div class="bg-gray-50 border rounded-lg p-4 mt-6">
                        <h3 class="font-bold text-lg text-gray-800 mb-3">Actions</h3>
                        <div class="flex flex-wrap gap-2">
                            <x-ui.sccr-button type="button" wire:click="submitOpname('{{ $detail['id'] }}')"
                                class="bg-green-600 text-white hover:bg-green-700">
                                <x-ui.sccr-icon name="send" :size="18" />
                                Submit for Approval
                            </x-ui.sccr-button>
                        </div>
                    </div>
                @endif

                @if ($detail['status'] === 'requested')
                    @php
                        $allItemsAdjusted = $detail->items->every(function ($item) {
                            return $item['status'] === 'match' || $item['physical_qty'] > 0;
                        });
                    @endphp

                    @if ($allItemsAdjusted)
                        <div class="bg-gray-50 border rounded-lg p-4 mt-6">
                            <h3 class="font-bold text-lg text-gray-800 mb-3">Actions</h3>
                            <div class="flex flex-wrap gap-2">
                                @php $approvalLevel = $detail['approval_level'] ?? 0; @endphp

                                @if ($approvalLevel == 0)
                                    <x-ui.sccr-button type="button" wire:click="excChefCanApprove('{{ $detail['id'] }}')"
                                        class="bg-green-600 text-white hover:bg-green-700">
                                        <x-ui.sccr-icon name="approve" :size="18" />
                                        Approve (Exc Chef)
                                    </x-ui.sccr-button>
                                @endif

                                @if ($approvalLevel == 1)
                                    <x-ui.sccr-button type="button" wire:click="rmCanApprove('{{ $detail['id'] }}')"
                                        class="bg-green-600 text-white hover:bg-green-700">
                                        <x-ui.sccr-icon name="approve" :size="18" />
                                        Approve (RM)
                                    </x-ui.sccr-button>
                                @endif

                                @if ($approvalLevel == 2)
                                    <x-ui.sccr-button type="button" wire:click="spvCanApprove('{{ $detail['id'] }}')"
                                        class="bg-green-600 text-white hover:bg-green-700">
                                        <x-ui.sccr-icon name="approve" :size="18" />
                                        Approve (SPV)
                                    </x-ui.sccr-button>
                                @endif

                                @if ($approvalLevel == 3)
                                    <x-ui.sccr-button type="button" wire:click="finalizeOpname('{{ $detail['id'] }}')"
                                        class="bg-teal-600 text-white hover:bg-teal-700">
                                        <x-ui.sccr-icon name="check" :size="18" />
                                        Finalize & Adjust Stock
                                    </x-ui.sccr-button>
                                @endif

                                <x-ui.sccr-button type="button" wire:click="rejectOpname('{{ $detail['id'] }}')"
                                    class="bg-red-100 text-red-700 hover:bg-red-200 border border-red-300">
                                    <x-ui.sccr-icon name="no" :size="18" />
                                    Tolak
                                </x-ui.sccr-button>
                            </div>
                        </div>
                    @else
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mt-6">
                            <p class="text-sm text-yellow-800">
                                <x-ui.sccr-icon name="warning" :size="16" class="inline" />
                                Adjustment belum dilakukan. Silakan lakukan adjustment terlebih dahulu sebelum approve.
                            </p>
                        </div>
                    @endif
                @endif
            </div>
        @endif
    </div>

    <x-ui.sccr-toast :show="$toast['show']" :type="$toast['type']" :message="$toast['message']" wire:key="toast-{{ microtime() }}" />

</x-ui.sccr-card>
