<div class="p-6">
    @if ($item)
        <h2 class="text-xl font-bold mb-4">Detail Item</h2>

        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500">ID</label>
                    <p class="text-sm font-mono">{{ $item->id }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">Nama Item</label>
                    <p class="text-sm font-semibold">{{ $item->name }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500">SKU / Barcode</label>
                    <p class="text-sm font-mono">{{ $item->sku }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">Tipe Item</label>
                    <p class="text-sm">
                        @if ($item->type === 'raw')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">Raw Material</span>
                        @elseif ($item->type === 'prep')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">Semi Finished</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">{{ ucfirst($item->type) }}</span>
                        @endif
                    </p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500">Deskripsi</label>
                <p class="text-sm">{{ $item->description ?: '-' }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500">Kategori</label>
                    <p class="text-sm">{{ $item->category?->name ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">Satuan</label>
                    <p class="text-sm">{{ $item->uom?->name ?? '-' }}</p>
                </div>
            </div>

            <div class="grid grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500">Min. Stok</label>
                    <p class="text-sm">{{ $item->is_stockable ? number_format($item->min_stock, 2) : '-' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">Aktif</label>
                    <p class="text-sm">
                        @if ($item->is_active)
                            <span class="text-green-600 font-semibold">Ya</span>
                        @else
                            <span class="text-red-600">Tidak</span>
                        @endif
                    </p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">Stokable</label>
                    <p class="text-sm">
                        @if ($item->is_stockable)
                            <span class="text-green-600 font-semibold">Ya</span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">Status</label>
                    <p class="text-sm">
                        @if ($item->deleted_at)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Deleted</span>
                        @elseif (! $item->is_active)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Draft</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Active</span>
                        @endif
                    </p>
                </div>
            </div>

            @if ($item->is_stockable)
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500">Batch</label>
                        <p class="text-sm">{{ $item->has_batch ? 'Ya' : 'Tidak' }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500">Expiry</label>
                        <p class="text-sm">{{ $item->has_expiry ? 'Ya' : 'Tidak' }}</p>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-2 gap-4 text-xs text-gray-400">
                <div>
                    <label class="block text-xs font-medium text-gray-500">Dibuat</label>
                    <p>{{ $item->created_at?->format('d M Y H:i') ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">Diubah</label>
                    <p>{{ $item->updated_at?->format('d M Y H:i') ?? '-' }}</p>
                </div>
            </div>
        </div>

        <div class="flex gap-3 pt-4 border-t mt-4">
            <button type="button" wire:click="edit"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Edit
            </button>
            <button type="button" wire:click="$dispatch('close-overlay')"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                Tutup
            </button>
        </div>
    @endif
</div>
