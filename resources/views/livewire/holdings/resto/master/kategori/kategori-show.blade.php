<div class="p-6">
    @if ($Category)
        <h2 class="text-xl font-bold mb-4">Category Detail</h2>

        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500">ID</label>
                    <p class="text-sm font-mono">{{ $Category->id }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">Category Name</label>
                    <p class="text-sm font-semibold">{{ $Category->name }}</p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500">Description</label>
                <p class="text-sm">{{ $Category->description ?: '-' }}</p>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500">Active</label>
                    <p class="text-sm">
                        @if ($Category->is_active)
                            <span class="text-green-600 font-semibold">Yes</span>
                        @else
                            <span class="text-red-600">No</span>
                        @endif
                    </p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">Status</label>
                    <p class="text-sm">
                        @if ($Category->deleted_at)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Deleted</span>
                        @elseif (! $Category->is_active)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Draft</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Active</span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 text-xs text-gray-400">
                <div>
                    <label class="block text-xs font-medium text-gray-500">Created</label>
                    <p>{{ $Category->created_at?->format('d M Y H:i') ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">Updated</label>
                    <p>{{ $Category->updated_at?->format('d M Y H:i') ?? '-' }}</p>
                </div>
            </div>
        </div>

        <div class="flex gap-3 pt-4 border-t mt-4">
            @if (! $Category->deleted_at)
                <button type="button" wire:click="Edit"
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
