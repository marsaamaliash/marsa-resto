<div class="p-6">
    @if ($vendor)
        <h2 class="text-xl font-bold mb-4">Vendor Detail</h2>

        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500">ID</label>
                    <p class="text-sm font-mono">{{ $vendor->id }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">Vendor Code</label>
                    <p class="text-sm font-mono">{{ $vendor->code }}</p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500">Vendor Name</label>
                <p class="text-sm font-semibold">{{ $vendor->name }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500">Email</label>
                    <p class="text-sm">{{ $vendor->Email ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">PIC</label>
                    <p class="text-sm">{{ $vendor->pic ?? '-' }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500">Phone</label>
                    <p class="text-sm">{{ $vendor->no_telp ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">Default Terms</label>
                    <p class="text-sm">
                        @if ($vendor->default_terms === 'cash')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Cash</span>
                        @elseif ($vendor->default_terms === '7_hari')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">7 Days</span>
                        @elseif ($vendor->default_terms === '30_hari')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">30 Days</span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500">Address</label>
                <p class="text-sm">{{ $vendor->address ?: '-' }}</p>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500">Description</label>
                <p class="text-sm">{{ $vendor->description ?: '-' }}</p>
            </div>

            <div class="grid grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500">Active</label>
                    <p class="text-sm">
                        @if ($vendor->is_active)
                            <span class="text-green-600 font-semibold">Yes</span>
                        @else
                            <span class="text-red-600">No</span>
                        @endif
                    </p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">Status</label>
                    <p class="text-sm">
                        @if ($vendor->deleted_at)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Deleted</span>
                        @elseif ($vendor->status === 'approved')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Approved</span>
                        @elseif ($vendor->status === 'rejected')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800">Rejected</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Requested</span>
                        @endif
                    </p>
                </div>
            </div>

            @if ($vendor->status === 'rejected' && $vendor->rejection_reason)
                <div>
                    <label class="block text-xs font-medium text-gray-500">Rejection Reason</label>
                    <p class="text-sm text-red-600">{{ $vendor->rejection_reason }}</p>
                </div>
            @endif

            <div class="grid grid-cols-2 gap-4 text-xs text-gray-400">
                <div>
                    <label class="block text-xs font-medium text-gray-500">Created</label>
                    <p>{{ $vendor->created_at?->format('d M Y H:i') ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">Updated</label>
                    <p>{{ $vendor->updated_at?->format('d M Y H:i') ?? '-' }}</p>
                </div>
            </div>
        </div>

        <div class="flex gap-3 pt-4 border-t mt-4">
            @if (! $vendor->deleted_at)
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
