<div class="p-6">
    @if ($meja)
        <h2 class="text-xl font-bold mb-4">Table Detail</h2>

        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500">ID</label>
                    <p class="text-sm font-mono">{{ $meja->id }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">Table No.</label>
                    <p class="text-sm font-mono font-semibold">Meja {{ $meja->table_number }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500">Capacity</label>
                    <p class="text-sm">{{ $meja->capacity }} persons</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">Area</label>
                    <p class="text-sm">
                        @if ($meja->area === 'indoor')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">Indoor</span>
                        @elseif ($meja->area === 'outdoor')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Outdoor</span>
                        @elseif ($meja->area === 'vip')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">VIP</span>
                        @elseif ($meja->area === 'smoking')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">Smoking</span>
                        @elseif ($meja->area === 'non-smoking')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-teal-100 text-teal-800">Non-Smoking</span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500">Status</label>
                    <p class="text-sm">
                        @if ($meja->deleted_at)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Deleted</span>
                        @elseif ($meja->status === 'available')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Available</span>
                        @elseif ($meja->status === 'occupied')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800">Occupied</span>
                        @elseif ($meja->status === 'reserved')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Reserved</span>
                        @elseif ($meja->status === 'maintenance')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">Maintenance</span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">Active</label>
                    <p class="text-sm">
                        @if ($meja->is_active)
                            <span class="text-green-600 font-semibold">Yes</span>
                        @else
                            <span class="text-red-600">No</span>
                        @endif
                    </p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500">Notes</label>
                <p class="text-sm">{{ $meja->notes ?: '-' }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4 text-xs text-gray-400">
                <div>
                    <label class="block text-xs font-medium text-gray-500">Created</label>
                    <p>{{ $meja->created_at?->format('d M Y H:i') ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">Updated</label>
                    <p>{{ $meja->updated_at?->format('d M Y H:i') ?? '-' }}</p>
                </div>
            </div>
        </div>

        <div class="flex gap-3 pt-4 border-t mt-4">
            @if (! $meja->deleted_at)
                <button type="button" wire:click="edit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Edit
                </button>
            @endif
            <button type="button" wire:click="$dispatch('close-overlay')"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                Close
            </button>
        </div>
    @endif
</div>
