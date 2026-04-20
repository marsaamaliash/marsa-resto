<div class="p-6">
    @if ($lokasi)
        <h2 class="text-xl font-bold mb-4">Detail Lokasi</h2>

        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500">ID</label>
                    <p class="text-sm font-mono">{{ $lokasi->id }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">Kode</label>
                    <p class="text-sm font-mono">{{ $lokasi->code ?: '-' }}</p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500">Nama Lokasi</label>
                <p class="text-sm font-semibold">{{ $lokasi->name }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500">Tipe</label>
                    <p class="text-sm">
                        @if ($lokasi->type === 'warehouse')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">Warehouse</span>
                        @elseif ($lokasi->type === 'kitchen')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800">Kitchen</span>
                        @elseif ($lokasi->type === 'outlet')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Outlet</span>
                        @elseif ($lokasi->type === 'transit')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Transit</span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">Nama PIC</label>
                    <p class="text-sm">{{ $lokasi->pic_name ?: '-' }}</p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500">Catatan</label>
                <p class="text-sm">{{ $lokasi->notes ?: '-' }}</p>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500">Aktif</label>
                    <p class="text-sm">
                        @if ($lokasi->is_active)
                            <span class="text-green-600 font-semibold">Ya</span>
                        @else
                            <span class="text-red-600">Tidak</span>
                        @endif
                    </p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">Status</label>
                    <p class="text-sm">
                        @if ($lokasi->deleted_at)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Deleted</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Active</span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 text-xs text-gray-400">
                <div>
                    <label class="block text-xs font-medium text-gray-500">Dibuat</label>
                    <p>{{ $lokasi->created_at?->format('d M Y H:i') ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">Diubah</label>
                    <p>{{ $lokasi->updated_at?->format('d M Y H:i') ?? '-' }}</p>
                </div>
            </div>
        </div>

        <div class="flex gap-3 pt-4 border-t mt-4">
            @if (! $lokasi->deleted_at)
                <button type="button" wire:click="edit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Edit
                </button>
            @endif
            <button type="button" wire:click="$dispatch('close-overlay')"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                Tutup
            </button>
        </div>
    @endif
</div>
