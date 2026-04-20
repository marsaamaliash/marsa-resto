<div>
    @if ($order)
        <div class="relative px-8 py-6 bg-emerald-600/80 rounded-b-3xl shadow-lg overflow-hidden">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-bold text-white">{{ $order->prod_no }}</h1>
                    <p class="text-emerald-100 text-sm">{{ $order->recipe?->recipe_name ?? '-' }} &middot; V{{ $order->recipeVersion?->version_no ?? '-' }}</p>
                </div>
                <div class="flex gap-2">
                    @if ($order->status === 'draft')
                        <button wire:click="updateStatus('issued')" class="px-3 py-1 rounded-md text-sm font-medium bg-blue-500 hover:bg-blue-600 text-white">Issue</button>
                    @endif
                    @if ($order->status === 'issued')
                        <button wire:click="updateStatus('in_progress')" class="px-3 py-1 rounded-md text-sm font-medium bg-yellow-500 hover:bg-yellow-600 text-white">Start</button>
                    @endif
                    @if ($order->status === 'in_progress')
                        <button wire:click="updateStatus('completed')" class="px-3 py-1 rounded-md text-sm font-medium bg-green-500 hover:bg-green-600 text-white">Complete</button>
                        <button wire:click="updateStatus('cancelled')" class="px-3 py-1 rounded-md text-sm font-medium bg-red-500 hover:bg-red-600 text-white">Cancel</button>
                    @endif
                    <a href="{{ route('dashboard.resto.resep.production') }}" class="px-3 py-1 rounded-md text-sm font-medium bg-gray-200 hover:bg-gray-300 text-gray-700">&larr; Kembali</a>
                </div>
            </div>
            <div class="mt-4 flex justify-between items-center text-sm">
                <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-6 lg:px-8 py-6">
            {{-- Info Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow p-4">
                    <div class="text-xs text-gray-500 uppercase font-bold">Status</div>
                    <div class="text-lg font-semibold mt-1">
                        @php $s = $order->status ?? 'draft'; @endphp
                        @if ($s === 'draft') <span class="text-gray-600">Draft</span>
                        @elseif ($s === 'issued') <span class="text-blue-600">Issued</span>
                        @elseif ($s === 'in_progress') <span class="text-yellow-600">In Progress</span>
                        @elseif ($s === 'completed') <span class="text-green-600">Completed</span>
                        @else <span class="text-red-600">Cancelled</span> @endif
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow p-4">
                    <div class="text-xs text-gray-500 uppercase font-bold">Planned Qty</div>
                    <div class="text-lg font-semibold text-gray-800 mt-1">{{ number_format($order->planned_output_qty, 2) }} {{ $order->outputUom?->name ?? '' }}</div>
                </div>
                <div class="bg-white rounded-xl shadow p-4">
                    <div class="text-xs text-gray-500 uppercase font-bold">Actual Output</div>
                    <div class="text-lg font-semibold mt-1 {{ ($order->actual_output_qty ?? 0) >= ($order->planned_output_qty ?? 0) ? 'text-green-600' : 'text-red-600' }}">{{ number_format($order->actual_output_qty ?? 0, 2) }}</div>
                </div>
                <div class="bg-white rounded-xl shadow p-4">
                    <div class="text-xs text-gray-500 uppercase font-bold">Issue Loc.</div>
                    <div class="text-sm font-semibold text-gray-800 mt-1">{{ $order->issueLocation?->name ?? '-' }}</div>
                </div>
                <div class="bg-white rounded-xl shadow p-4">
                    <div class="text-xs text-gray-500 uppercase font-bold">Output Loc.</div>
                    <div class="text-sm font-semibold text-gray-800 mt-1">{{ $order->outputLocation?->name ?? '-' }}</div>
                </div>
            </div>

            {{-- Tabs --}}
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="border-b border-gray-200">
                    <nav class="flex -mb-px">
                        <button wire:click="setActiveTab('components')" class="px-6 py-3 text-sm font-medium border-b-2 transition {{ $activeTab === 'components' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                            Component Plan
                            @if ($order->componentPlans->count() > 0) <span class="ml-1 bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded-full text-xs">{{ $order->componentPlans->count() }}</span> @endif
                        </button>
                        <button wire:click="setActiveTab('issue')" class="px-6 py-3 text-sm font-medium border-b-2 transition {{ $activeTab === 'issue' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                            Material Issue
                            @if ($order->materialIssueLines->count() > 0) <span class="ml-1 bg-blue-100 text-blue-800 px-1.5 py-0.5 rounded-full text-xs">{{ $order->materialIssueLines->count() }}</span> @endif
                        </button>
                        <button wire:click="setActiveTab('output')" class="px-6 py-3 text-sm font-medium border-b-2 transition {{ $activeTab === 'output' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                            Output
                            @if ($order->outputLines->count() > 0) <span class="ml-1 bg-green-100 text-green-800 px-1.5 py-0.5 rounded-full text-xs">{{ $order->outputLines->count() }}</span> @endif
                        </button>
                        <button wire:click="setActiveTab('waste')" class="px-6 py-3 text-sm font-medium border-b-2 transition {{ $activeTab === 'waste' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                            Waste
                            @if ($wasteLines->count() > 0) <span class="ml-1 bg-red-100 text-red-800 px-1.5 py-0.5 rounded-full text-xs">{{ $wasteLines->count() }}</span> @endif
                        </button>
                        <button wire:click="setActiveTab('cost')" class="px-6 py-3 text-sm font-medium border-b-2 transition {{ $activeTab === 'cost' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                            Cost Summary
                        </button>
                    </nav>
                </div>

                {{-- COMPONENT PLANS TAB --}}
                @if ($activeTab === 'components')
                    <div class="p-6">
                        <div class="overflow-hidden border border-gray-200 rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipe</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Qty/Batch</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Planned Total</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Satuan</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Wastage %</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Std Unit Cost</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Std Total Cost</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($order->componentPlans as $plan)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm text-gray-600">{{ $plan->line_no }}</td>
                                            <td class="px-4 py-3 text-sm">
                                                @if ($plan->component_kind === 'item')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">Item</span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800">Sub-Resep</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                                @if ($plan->component_kind === 'item')
                                                    {{ $plan->componentItem?->name ?? '-' }}
                                                @else
                                                    {{ $plan->componentRecipe?->recipe_name ?? '-' }}
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm text-right font-mono">{{ number_format($plan->qty_standard_per_batch, 4) }}</td>
                                            <td class="px-4 py-3 text-sm text-right font-mono font-semibold">{{ number_format($plan->planned_total_qty, 4) }}</td>
                                            <td class="px-4 py-3 text-sm">{{ $plan->uom?->name ?? '-' }}</td>
                                            <td class="px-4 py-3 text-sm text-right">{{ $plan->wastage_pct_standard }}%</td>
                                            <td class="px-4 py-3 text-sm text-right font-mono">{{ number_format($plan->standard_unit_cost, 4) }}</td>
                                            <td class="px-4 py-3 text-sm text-right font-mono font-semibold text-gray-900">{{ number_format($plan->standard_total_cost, 4) }}</td>
                                        </tr>
                                    @endforeach
                                    @if ($order->componentPlans->count() === 0)
                                        <tr><td colspan="9" class="py-6 text-center text-gray-400 italic">Tidak ada component plan</td></tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- MATERIAL ISSUE TAB --}}
                @if ($activeTab === 'issue')
                    <div class="p-6">
                        @if (in_array($order->status, ['draft', 'issued', 'in_progress']))
                            <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 mb-4">
                                <h4 class="text-sm font-semibold text-emerald-800 mb-3">Issue Material</h4>
                                <form wire:submit.prevent="issueMaterial" class="grid grid-cols-4 gap-3">
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Komponen (Plan Line) <span class="text-red-500">*</span></label>
                                        <select wire:model="issueForm.plan_line_id" class="w-full border-gray-300 rounded-md text-sm">
                                            <option value="">-- Pilih --</option>
                                            @foreach ($order->componentPlans as $plan)
                                                <option value="{{ $plan->id }}">{{ $plan->line_no }} - {{ $plan->component_kind === 'item' ? ($plan->componentItem?->name ?? '-') : ($plan->componentRecipe?->recipe_name ?? '-') }}</option>
                                            @endforeach
                                        </select>
                                        @error('issueForm.plan_line_id') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Qty Issue <span class="text-red-500">*</span></label>
                                        <input type="number" step="0.000001" min="0" wire:model.defer="issueForm.qty_issued" class="w-full border-gray-300 rounded-md text-sm" placeholder="0">
                                        @error('issueForm.qty_issued') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Catatan</label>
                                        <input type="text" wire:model.defer="issueForm.notes" class="w-full border-gray-300 rounded-md text-sm" placeholder="Opsional">
                                    </div>
                                    <div class="flex items-end">
                                        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 text-sm font-medium">Issue</button>
                                    </div>
                                </form>
                            </div>
                        @endif

                        <div class="overflow-hidden border border-gray-200 rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Qty Issued</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Satuan</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Unit Cost</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Cost</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Issued At</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($order->materialIssueLines as $line)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm text-gray-600">{{ $line->line_no }}</td>
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $line->item?->name ?? '-' }}</td>
                                            <td class="px-4 py-3 text-sm text-right font-mono">{{ number_format($line->qty_issued, 4) }}</td>
                                            <td class="px-4 py-3 text-sm">{{ $line->uom?->name ?? '-' }}</td>
                                            <td class="px-4 py-3 text-sm text-right font-mono">{{ number_format($line->actual_unit_cost, 4) }}</td>
                                            <td class="px-4 py-3 text-sm text-right font-mono font-semibold">{{ number_format($line->actual_total_cost, 4) }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600">{{ $line->issued_at?->format('d M Y H:i') ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                    @if ($order->materialIssueLines->count() === 0)
                                        <tr><td colspan="7" class="py-6 text-center text-gray-400 italic">Belum ada material issue</td></tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        @if ($order->materialIssueLines->count() > 0)
                            <div class="mt-4 bg-gray-50 border border-gray-200 rounded-lg p-4">
                                <div class="text-sm text-gray-600">Total Material Cost:</div>
                                <div class="text-xl font-bold text-gray-900">{{ number_format($order->materialIssueLines->sum('actual_total_cost'), 4) }}</div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- OUTPUT TAB --}}
                @if ($activeTab === 'output')
                    <div class="p-6">
                        @if (in_array($order->status, ['issued', 'in_progress']))
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                                <h4 class="text-sm font-semibold text-blue-800 mb-3">Catat Output</h4>
                                <form wire:submit.prevent="recordOutput" class="grid grid-cols-5 gap-3">
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Item Output <span class="text-red-500">*</span></label>
                                        <select wire:model="outputForm.output_item_id" class="w-full border-gray-300 rounded-md text-sm">
                                            <option value="">-- Pilih --</option>
                                            @foreach ($items as $item)
                                                <option value="{{ $item['value'] }}">{{ $item['label'] }}</option>
                                            @endforeach
                                        </select>
                                        @error('outputForm.output_item_id') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Qty Output <span class="text-red-500">*</span></label>
                                        <input type="number" step="0.000001" min="0" wire:model.defer="outputForm.qty_output" class="w-full border-gray-300 rounded-md text-sm" placeholder="0">
                                        @error('outputForm.qty_output') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Tipe</label>
                                        <select wire:model="outputForm.output_type" class="w-full border-gray-300 rounded-md text-sm">
                                            <option value="main">Main</option>
                                            <option value="by_product">By-Product</option>
                                            <option value="co_product">Co-Product</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Satuan <span class="text-red-500">*</span></label>
                                        <select wire:model="outputForm.uom_id" class="w-full border-gray-300 rounded-md text-sm">
                                            <option value="">-- Pilih --</option>
                                            @foreach ($uoms as $uom)
                                                <option value="{{ $uom['value'] }}">{{ $uom['label'] }}</option>
                                            @endforeach
                                        </select>
                                        @error('outputForm.uom_id') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="flex items-end">
                                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">Catat</button>
                                    </div>
                                </form>
                            </div>
                        @endif

                        <div class="overflow-hidden border border-gray-200 rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipe</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">QC Status</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Inventory</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($order->outputLines as $line)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm text-gray-600">{{ $line->line_no }}</td>
                                            <td class="px-4 py-3 text-sm">
                                                @if ($line->output_type === 'main')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Main</span>
                                                @elseif ($line->output_type === 'by_product')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">By-Product</span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">Co-Product</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $line->outputItem?->name ?? '-' }}</td>
                                            <td class="px-4 py-3 text-sm text-right font-mono">{{ number_format($line->qty_output, 4) }}</td>
                                            <td class="px-4 py-3 text-center">
                                                @if ($line->qc_status === 'pending')
                                                    <span class="text-yellow-600 text-xs font-medium">Pending</span>
                                                @elseif ($line->qc_status === 'approved')
                                                    <span class="text-green-600 text-xs font-medium">Approved</span>
                                                @else
                                                    <span class="text-red-600 text-xs font-medium">Rejected</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                @if ($line->posted_to_inventory)
                                                    <span class="text-green-600 text-xs font-semibold">Posted</span>
                                                @else
                                                    <span class="text-gray-400 text-xs">Belum</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                @if (! $line->posted_to_inventory && $line->qc_status !== 'rejected')
                                                    <button wire:click="postToInventory({{ $line->id }})" class="px-2 py-1 text-xs bg-emerald-500 text-white rounded hover:bg-emerald-600">Post to Inventory</button>
                                                @else
                                                    <span class="text-gray-400 text-xs">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    @if ($order->outputLines->count() === 0)
                                        <tr><td colspan="7" class="py-6 text-center text-gray-400 italic">Belum ada output</td></tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- WASTE TAB --}}
                @if ($activeTab === 'waste')
                    <div class="p-6">
                        @if (in_array($order->status, ['issued', 'in_progress']))
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                                <h4 class="text-sm font-semibold text-red-800 mb-3">Catat Waste</h4>
                                <form wire:submit.prevent="recordWaste" class="grid grid-cols-7 gap-3">
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Item <span class="text-red-500">*</span></label>
                                        <select wire:model="wasteForm.item_id" class="w-full border-gray-300 rounded-md text-sm">
                                            <option value="">-- Pilih --</option>
                                            @foreach ($items as $item)
                                                <option value="{{ $item['value'] }}">{{ $item['label'] }}</option>
                                            @endforeach
                                        </select>
                                        @error('wasteForm.item_id') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Qty Waste <span class="text-red-500">*</span></label>
                                        <input type="number" step="0.000001" min="0" wire:model.defer="wasteForm.qty_waste" class="w-full border-gray-300 rounded-md text-sm" placeholder="0">
                                        @error('wasteForm.qty_waste') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Satuan</label>
                                        <select wire:model="wasteForm.uom_id" class="w-full border-gray-300 rounded-md text-sm">
                                            <option value="">-- Pilih --</option>
                                            @foreach ($uoms as $uom)
                                                <option value="{{ $uom['value'] }}">{{ $uom['label'] }}</option>
                                            @endforeach
                                        </select>
                                        @error('wasteForm.uom_id') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Waste Type</label>
                                        <select wire:model="wasteForm.waste_type" class="w-full border-gray-300 rounded-md text-sm">
                                            <option value="normal">Normal</option>
                                            <option value="abnormal">Abnormal</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Charge Mode</label>
                                        <select wire:model="wasteForm.charge_mode" class="w-full border-gray-300 rounded-md text-sm">
                                            <option value="absorbed">Absorbed</option>
                                            <option value="deduct_stock">Deduct Stock</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Catatan</label>
                                        <input type="text" wire:model.defer="wasteForm.notes" class="w-full border-gray-300 rounded-md text-sm" placeholder="Opsional">
                                    </div>
                                    <div class="flex items-end">
                                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm font-medium">Catat Waste</button>
                                    </div>
                                </form>
                            </div>
                        @endif

                        <div class="overflow-hidden border border-gray-200 rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Qty Waste</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Satuan</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stage</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Charge Mode</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Unit Cost</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Cost</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($wasteLines as $line)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm text-gray-600">{{ $line->line_no }}</td>
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $line->item?->name ?? '-' }}</td>
                                            <td class="px-4 py-3 text-sm text-right font-mono">{{ number_format($line->qty_waste, 4) }}</td>
                                            <td class="px-4 py-3 text-sm">{{ $line->uom?->name ?? '-' }}</td>
                                            <td class="px-4 py-3 text-sm">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 capitalize">{{ $line->waste_stage }}</span>
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                @if ($line->waste_type === 'normal')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Normal</span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Abnormal</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                @if ($line->charge_mode === 'absorbed')
                                                    <span class="text-gray-600 text-xs">Absorbed</span>
                                                @else
                                                    <span class="text-orange-600 text-xs font-medium">Deduct Stock</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm text-right font-mono">{{ number_format($line->actual_unit_cost, 4) }}</td>
                                            <td class="px-4 py-3 text-sm text-right font-mono font-semibold text-gray-900">{{ number_format($line->actual_total_cost, 4) }}</td>
                                        </tr>
                                    @endforeach
                                    @if ($wasteLines->count() === 0)
                                        <tr><td colspan="9" class="py-6 text-center text-gray-400 italic">Belum ada waste</td></tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        @if ($wasteLines->count() > 0)
                            <div class="mt-4 grid grid-cols-2 gap-4">
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                    <div class="text-sm text-gray-600">Normal Waste Cost:</div>
                                    <div class="text-xl font-bold text-yellow-700">{{ number_format($wasteLines->where('waste_type', 'normal')->sum('actual_total_cost'), 4) }}</div>
                                </div>
                                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                    <div class="text-sm text-gray-600">Abnormal Waste Cost:</div>
                                    <div class="text-xl font-bold text-red-700">{{ number_format($wasteLines->where('waste_type', 'abnormal')->sum('actual_total_cost'), 4) }}</div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- COST SUMMARY TAB --}}
                @if ($activeTab === 'cost')
                    <div class="p-6">
                        @if ($costSummary)
                            <div class="space-y-4">
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <div class="bg-white border border-gray-200 rounded-lg p-4">
                                        <div class="text-xs text-gray-500 uppercase font-bold">Material Cost</div>
                                        <div class="text-lg font-semibold text-gray-900 mt-1">{{ number_format($costSummary->material_cost_total, 4) }}</div>
                                    </div>
                                    <div class="bg-white border border-gray-200 rounded-lg p-4">
                                        <div class="text-xs text-gray-500 uppercase font-bold">Packaging Cost</div>
                                        <div class="text-lg font-semibold text-gray-900 mt-1">{{ number_format($costSummary->packaging_cost_total, 4) }}</div>
                                    </div>
                                    <div class="bg-white border border-gray-200 rounded-lg p-4">
                                        <div class="text-xs text-gray-500 uppercase font-bold">Labor Absorbed</div>
                                        <div class="text-lg font-semibold text-gray-900 mt-1">{{ number_format($costSummary->labor_absorbed_total, 4) }}</div>
                                    </div>
                                    <div class="bg-white border border-gray-200 rounded-lg p-4">
                                        <div class="text-xs text-gray-500 uppercase font-bold">Overhead Absorbed</div>
                                        <div class="text-lg font-semibold text-gray-900 mt-1">{{ number_format($costSummary->overhead_absorbed_total, 4) }}</div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                        <div class="text-xs text-gray-600 uppercase font-bold">Normal Loss Cost</div>
                                        <div class="text-lg font-semibold text-yellow-700 mt-1">{{ number_format($costSummary->normal_loss_cost_total, 4) }}</div>
                                    </div>
                                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                        <div class="text-xs text-gray-600 uppercase font-bold">Abnormal Waste Cost</div>
                                        <div class="text-lg font-semibold text-red-700 mt-1">{{ number_format($costSummary->abnormal_waste_cost_total, 4) }}</div>
                                    </div>
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                        <div class="text-xs text-gray-600 uppercase font-bold">Yield Variance</div>
                                        <div class="text-lg font-semibold {{ $costSummary->yield_variance_cost >= 0 ? 'text-blue-700' : 'text-red-700' }} mt-1">{{ number_format($costSummary->yield_variance_cost, 4) }}</div>
                                    </div>
                                </div>

                                <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-6">
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                                        <div>
                                            <div class="text-xs text-gray-600 uppercase font-bold">Total Input Cost</div>
                                            <div class="text-2xl font-bold text-gray-900">{{ number_format($costSummary->total_input_cost, 4) }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-600 uppercase font-bold">Total Output Cost</div>
                                            <div class="text-2xl font-bold text-gray-900">{{ number_format($costSummary->total_output_cost, 4) }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-600 uppercase font-bold">Cost per Output Unit</div>
                                            <div class="text-2xl font-bold text-emerald-700">{{ number_format($costSummary->cost_per_output_unit, 4) }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-600 uppercase font-bold">Computed At</div>
                                            <div class="text-sm font-semibold text-gray-700 mt-2">{{ $costSummary->computed_at?->format('d M Y H:i') ?? '-' }}</div>
                                        </div>
                                    </div>
                                </div>

                                @if ($order->status === 'in_progress')
                                    <div class="flex justify-end">
                                        <button wire:click="completeOrder" wire:confirm="Yakin ingin menyelesaikan production order ini? Semua output akan di-post ke inventory." class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-semibold shadow-sm">
                                            Complete Order
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="text-center py-12 text-gray-400">
                                <p class="text-lg font-medium">Cost summary belum tersedia</p>
                                <p class="text-sm mt-1">Cost summary akan otomatis di-generate saat production order di-complete.</p>
                                @if ($order->status === 'in_progress')
                                    <button wire:click="completeOrder" wire:confirm="Yakin ingin menyelesaikan production order ini? Semua output akan di-post ke inventory." class="mt-6 px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-semibold shadow-sm">
                                        Complete Order
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="relative px-8 py-6 bg-emerald-600/80 rounded-b-3xl shadow-lg overflow-hidden">
            <h1 class="text-3xl font-bold text-white">Production Order Tidak Ditemukan</h1>
            <div class="mt-4">
                <a href="{{ route('dashboard.resto.resep.production') }}" class="px-4 py-2 bg-white text-emerald-600 rounded-md hover:bg-gray-100 text-sm font-medium">&larr; Kembali</a>
            </div>
        </div>
    @endif

    <x-ui.sccr-toast :show="$toast['show']" :type="$toast['type']" :message="$toast['message']" wire:key="toast-{{ microtime() }}" />
</div>