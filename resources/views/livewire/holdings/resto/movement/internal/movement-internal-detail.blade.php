<x-ui.sccr-card transparent wire:key="movement-internal-2-detail" class="h-full min-h-0 flex flex-col">

    {{-- ================= HEADER ================= --}}
    <div class="relative px-8 py-6 bg-blue-600/80 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">Stock Movement - Detail</h1>
                <p class="text-blue-100 text-sm">
                    Detail movement dengan Reference Number
                </p>
            </div>
        </div>

        <div class="mt-4 flex justify-between items-center text-sm">
            <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
        </div>
    </div>

    {{-- ================= CONTENT ================= --}}
    <div class="flex-1 min-h-0 px-4 pb-2 overflow-y-auto">
        @if ($detail)
            <div class="py-4">
                {{-- Movement Info --}}
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <h3 class="font-bold text-lg text-gray-800 mb-3">Movement Information</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-sm">
                        <div>
                            <div class="font-semibold text-gray-600 text-xs uppercase">Request No.</div>
                            <div class="font-mono font-bold text-blue-700">{{ $detail['reference_number'] ?? '-' }}
                            </div>
                        </div>

                        <div>
                            <div class="font-semibold text-gray-600 text-xs uppercase">Movement ID</div>
                            <div class="font-mono font-bold text-gray-700">#{{ $detail['id'] }}</div>
                        </div>

                        <div>
                            <div class="font-semibold text-gray-600 text-xs uppercase">From Location</div>
                            <div>{{ $detail->fromLocation?->name ?? '-' }}</div>
                        </div>

                        <div>
                            <div class="font-semibold text-gray-600 text-xs uppercase">To Location</div>
                            <div>{{ $detail->toLocation?->name ?? '-' }}</div>
                        </div>

                        <div>
                            <div class="font-semibold text-gray-600 text-xs uppercase">Status</div>
                            <div>
                                @if ($detail['status'] === 'requested')
                                    <span
                                        class="px-2 py-0.5 rounded bg-yellow-100 text-yellow-800 text-xs">Requested</span>
                                @elseif($detail['status'] === 'approved')
                                    <span
                                        class="px-2 py-0.5 rounded bg-green-100 text-green-800 text-xs">Approved</span>
                                @elseif($detail['status'] === 'in_transit')
                                    <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 text-xs">In
                                        Transit</span>
                                @elseif($detail['status'] === 'completed')
                                    <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-800 text-xs">Completed</span>
                                @else
                                    {{ $detail['status'] }}
                                @endif
                            </div>
                        </div>

                        <div>
                            <div class="font-semibold text-gray-600 text-xs uppercase">PIC</div>
                            <div>{{ $detail['pic_name'] ?? '-' }}</div>
                        </div>

                        <div>
                            <div class="font-semibold text-gray-600 text-xs uppercase">Remark</div>
                            <div>{{ $detail['remark'] ?? '-' }}</div>
                        </div>

                        <div>
                            <div class="font-semibold text-gray-600 text-xs uppercase">Created</div>
                            <div>{{ $detail['created_at'] }}</div>
                        </div>
                    </div>
                </div>

                {{-- Movement Items --}}
                @if ($detail->items->count() > 0)
                    <div class="mb-4">
                        <h3 class="font-bold text-lg text-gray-800 mb-3">Items</h3>
                        <div class="bg-white border rounded-lg overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Item
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-600 uppercase">Qty
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Satuan
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">
                                            Catatan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($detail->items as $movementItem)
                                        <tr>
                                            <td class="px-4 py-3">{{ $movementItem->item?->name ?? '-' }}</td>
                                            <td class="px-4 py-3 text-right font-mono">
                                                {{ number_format($movementItem->qty, 2) }}</td>
                                            <td class="px-4 py-3">{{ $movementItem->uom?->symbols ?? '' }}</td>
                                            <td class="px-4 py-3">{{ $movementItem->remark ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- Stock Mutations by Reference Number --}}
                @if ($stockMutations->count() > 0)
                    <div class="mb-4">
                        <h3 class="font-bold text-lg text-gray-800 mb-3">
                            Stock Mutations
                            <span class="text-sm font-normal text-gray-500">(Reference:
                                {{ $detail['reference_number'] ?? '-' }})</span>
                        </h3>
                        <div class="bg-white border rounded-lg overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Item
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Type
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-600 uppercase">Qty
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-600 uppercase">
                                            Before</th>
                                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-600 uppercase">After
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Notes
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">
                                            Created</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($stockMutations as $mutation)
                                        <tr>
                                            <td class="px-4 py-3">{{ $mutation->item?->name ?? '-' }}</td>
                                            <td class="px-4 py-3">
                                                @if ($mutation['type'] === 'in')
                                                    <span
                                                        class="px-2 py-0.5 rounded bg-green-100 text-green-800 text-xs">IN</span>
                                                @elseif($mutation['type'] === 'out')
                                                    <span
                                                        class="px-2 py-0.5 rounded bg-red-100 text-red-800 text-xs">OUT</span>
                                                @elseif($mutation['type'] === 'reservation')
                                                    <span
                                                        class="px-2 py-0.5 rounded bg-yellow-100 text-yellow-800 text-xs">RES</span>
                                                @elseif($mutation['type'] === 'unreserved')
                                                    <span
                                                        class="px-2 py-0.5 rounded bg-gray-100 text-gray-800 text-xs">UNR</span>
                                                @elseif($mutation['type'] === 'waste')
                                                    <span
                                                        class="px-2 py-0.5 rounded bg-red-100 text-red-800 text-xs">WASTE</span>
                                                @else
                                                    {{ $mutation['type'] }}
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-right font-mono">
                                                {{ number_format($mutation['qty'], 2) }}</td>
                                            <td class="px-4 py-3 text-right font-mono text-gray-500">
                                                {{ number_format($mutation['qty_before'], 2) }}</td>
                                            <td class="px-4 py-3 text-right font-mono text-gray-500">
                                                {{ number_format($mutation['qty_after'], 2) }}</td>
                                            <td class="px-4 py-3 text-xs">{{ $mutation['notes'] ?? '-' }}</td>
                                            <td class="px-4 py-3 text-xs text-gray-500">{{ $mutation['created_at'] }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- Request Activities --}}
                @if ($requestActivities->count() > 0)
                    <div class="mb-4">
                        <h3 class="font-bold text-lg text-gray-800 mb-3">Activities</h3>
                        <div class="bg-white border rounded-lg overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">PIC
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Action
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Status
                                            From</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Status
                                            To</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">
                                            Comment</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">
                                            Created</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($requestActivities as $activity)
                                        <tr>
                                            <td class="px-4 py-3">{{ $activity['pic'] ?? '-' }}</td>
                                            <td class="px-4 py-3">
                                                @if ($activity['action'] === 'requested')
                                                    <span
                                                        class="px-2 py-0.5 rounded bg-yellow-100 text-yellow-800 text-xs">Requested</span>
                                                @elseif($activity['action'] === 'approved_exc_chef')
                                                    <span
                                                        class="px-2 py-0.5 rounded bg-green-100 text-green-800 text-xs">EC
                                                        Approved</span>
                                                @elseif($activity['action'] === 'approved_rm')
                                                    <span
                                                        class="px-2 py-0.5 rounded bg-green-100 text-green-800 text-xs">RM
                                                        Approved</span>
                                                @elseif($activity['action'] === 'approved_spv')
                                                    <span
                                                        class="px-2 py-0.5 rounded bg-green-100 text-green-800 text-xs">SPV
                                                        Approved</span>
                                                @elseif($activity['action'] === 'distributed')
                                                    <span
                                                        class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 text-xs">Distributed</span>
                                                @elseif($activity['action'] === 'received')
                                                    <span
                                                        class="px-2 py-0.5 rounded bg-gray-100 text-gray-800 text-xs">Received</span>
                                                @elseif($activity['action'] === 'rejected')
                                                    <span
                                                        class="px-2 py-0.5 rounded bg-red-100 text-red-800 text-xs">Rejected</span>
                                                @elseif($activity['action'] === 'revised')
                                                    <span
                                                        class="px-2 py-0.5 rounded bg-orange-100 text-orange-800 text-xs">Revised</span>
                                                @else
                                                    {{ $activity['action'] }}
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-xs">{{ $activity['status_from'] ?? '-' }}</td>
                                            <td class="px-4 py-3 text-xs">{{ $activity['status_to'] ?? '-' }}</td>
                                            <td class="px-4 py-3 text-xs">{{ $activity['comment'] ?? '-' }}</td>
                                            <td class="px-4 py-3 text-xs text-gray-500">{{ $activity['created_at'] }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- Actions --}}
                @if (in_array($detail['status'], ['requested', 'approved']))
                    <div class="bg-gray-50 border rounded-lg p-4 mt-6">
                        <h3 class="font-bold text-lg text-gray-800 mb-3">Actions</h3>
                        <div class="flex flex-wrap gap-2">
                            @if ($detail['status'] === 'requested')
                                @php
                                    $approvalLevel = $detail['approval_level'] ?? 0;
                                @endphp
                                @if ($approvalLevel == 0 && ($canApproveExcChef || $canApprove))
                                    <x-ui.sccr-button type="button"
                                        wire:click="excChefCanApprove('{{ $detail['id'] }}')"
                                        class="bg-green-600 text-white hover:bg-green-700">
                                        <x-ui.sccr-icon name="approve" :size="18" />
                                        Approve (Exc Chef)
                                    </x-ui.sccr-button>
                                @endif
                                @if ($approvalLevel == 1 && ($canApproveRM || $canApprove))
                                    <x-ui.sccr-button type="button" wire:click="rmCanApprove('{{ $detail['id'] }}')"
                                        class="bg-green-600 text-white hover:bg-green-700">
                                        <x-ui.sccr-icon name="approve" :size="18" />
                                        Approve (RM)
                                    </x-ui.sccr-button>
                                @endif
                                @if ($approvalLevel == 2 && ($canApproveSPV || $canApprove))
                                    <x-ui.sccr-button type="button"
                                        wire:click="spvCanApprove('{{ $detail['id'] }}')"
                                        class="bg-green-600 text-white hover:bg-green-700">
                                        <x-ui.sccr-icon name="approve" :size="18" />
                                        Approve (SPV)
                                    </x-ui.sccr-button>
                                @endif
                            @endif
                            <x-ui.sccr-button type="button" wire:click="openRejectOverlay('{{ $detail['id'] }}')"
                                class="bg-red-100 text-red-700 hover:bg-red-200 border border-red-300">
                                <x-ui.sccr-icon name="no" :size="18" />
                                Tolak
                            </x-ui.sccr-button>
                        </div>
                    </div>
                @endif
            </div>
            @endif
    </div>

    {{-- ================= TOAST ================= --}}
    <x-ui.sccr-toast :show="$toast['show']" :type="$toast['type']" :message="$toast['message']" wire:key="toast-{{ microtime() }}" />

    {{-- ================= OVERLAY: REJECT ================= --}}
    @if ($rejectOverlayMode === 'reject' && $rejectOverlayId)
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="closeRejectOverlay"></div>

        <div class="fixed inset-0 z-50 flex items-center justify-center px-6">
            <div class="w-full max-w-lg bg-white rounded-2xl shadow-2xl relative">
                <x-ui.sccr-button type="button" variant="icon" wire:click="closeRejectOverlay"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500 z-10" title="Tutup">
                    <span class="text-xl leading-none">✕</span>
                </x-ui.sccr-button>

                <div class="p-6">
                    <h3 class="text-xl font-bold mb-4 text-red-600">Tolak Permintaan</h3>

                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div class="font-semibold text-gray-600">No. Movement:</div>
                            <div class="font-mono font-bold text-red-700">#{{ $detail['id'] }}</div>

                            <div class="font-semibold text-gray-600">Reference:</div>
                            <div>{{ $detail['reference_number'] ?? '-' }}</div>

                            <div class="font-semibold text-gray-600">Dari:</div>
                            <div>{{ $detail->fromLocation?->name ?? '-' }}</div>

                            <div class="font-semibold text-gray-600">Ke:</div>
                            <div>{{ $detail->toLocation?->name ?? '-' }}</div>

                            <div class="font-semibold text-gray-600">Status:</div>
                            <div><span
                                    class="px-2 py-0.5 rounded bg-yellow-100 text-yellow-800 text-xs">{{ $detail['status'] }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alasan Penolakan</label>
                        <textarea wire:model="rejectNotes" rows="4" class="w-full border border-gray-300 rounded-md px-3 py-2"
                            placeholder="Contoh: Stok tidak mencukupi, item tidak tersedia, dll..."></textarea>
                    </div>

                    <div class="flex gap-2">
                        <x-ui.sccr-button type="button" wire:click="closeRejectOverlay"
                            class="flex-1 bg-gray-300 text-gray-700 hover:bg-gray-400">
                            Batal
                        </x-ui.sccr-button>
                        <x-ui.sccr-button type="button" wire:click="excChefCanReject('{{ $rejectOverlayId }}')"
                            class="flex-1 bg-red-600 text-white hover:bg-red-700">
                            Tolak
                        </x-ui.sccr-button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</x-ui.sccr-card>
