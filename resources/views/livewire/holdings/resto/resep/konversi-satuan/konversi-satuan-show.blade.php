<div class="p-6">
    <h2 class="text-xl font-bold mb-4">Unit Conversion Detail</h2>

    @if ($konversi)
        <dl class="space-y-4">
            <div>
                <dt class="text-sm font-medium text-gray-500">ID</dt>
                <dd class="text-sm font-semibold text-gray-900">{{ $konversi->id }}</dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500">Item</dt>
                <dd class="text-sm text-gray-900">{{ $konversi->item?->name ?? '-' }}</dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500">From Unit</dt>
                <dd class="text-sm text-gray-900">{{ $konversi->fromUom?->name ?? '-' }}</dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500">To Unit</dt>
                <dd class="text-sm text-gray-900">{{ $konversi->toUom?->name ?? '-' }}</dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500">Conversion Value</dt>
                <dd class="text-sm text-gray-900">{{ $konversi->conversion_factor }}</dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500">Notes</dt>
                <dd class="text-sm text-gray-900">{{ $konversi->notes ?? '-' }}</dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500">Status</dt>
                <dd class="text-sm">
                    @if ($konversi->deleted_at)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Deleted</span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Active</span>
                    @endif
                </dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500">Created</dt>
                <dd class="text-sm text-gray-900">{{ $konversi->created_at?->format('d M Y H:i') ?? '-' }}</dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500">Updated</dt>
                <dd class="text-sm text-gray-900">{{ $konversi->updated_at?->format('d M Y H:i') ?? '-' }}</dd>
            </div>
        </dl>

        <div class="flex gap-3 mt-6 pt-4 border-t">
            @if (! $konversi->deleted_at)
                <button type="button" wire:click="edit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Edit
                </button>
            @endif
        </div>
    @else
        <p class="text-center text-gray-500">Data not found</p>
    @endif
</div>
