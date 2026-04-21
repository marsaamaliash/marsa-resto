<x-ui.sccr-card transparent wire:key="stock-opname" class="h-full min-h-0 flex flex-col">

    <div class="relative px-8 py-6 bg-teal-600/80 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">Stock Opname</h1>
                <p class="text-teal-100 text-sm">
                    Pengecekan stok fisik vs sistem dengan adjustment
                </p>
            </div>
        </div>

        <div class="mt-4 flex justify-between items-center text-sm">
            <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
            <div class="text-white">
                Menampilkan <span class="font-bold text-black">{{ $data->total() }}</span> data
            </div>
        </div>
    </div>

    <div class="px-4 pt-3 pb-1">
        <div class="flex flex-wrap items-center justify-between gap-1">

            <form wire:submit.prevent="applyFilter" class="flex flex-wrap items-center gap-1 flex-grow">

                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Cari</span>
                    <x-ui.sccr-input name="search" wire:model="search"
                        placeholder="Reference, Lokasi, Status..." class="w-72" />
                </div>

                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Status</span>
                    <x-ui.sccr-select name="filter1" wire:model.live="filter1" :options="$filter1Options" class="w-40" />
                </div>

                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Data</span>
                    <select wire:model.live="filterStatus" class="border-gray-300 rounded-md text-sm w-28">
                        <option value="">Semua</option>
                        <option value="active">Active</option>
                        <option value="deleted">Deleted</option>
                    </select>
                </div>

                <div class="flex flex-wrap items-center gap-1">
                    <x-ui.sccr-button type="submit" variant="primary"
                        class="bg-gray-900 text-gray-100 hover:bg-gray-400">
                        <x-ui.sccr-icon name="cari" :size="20" />
                        Cari
                    </x-ui.sccr-button>

                    <x-ui.sccr-button type="button" wire:click="clearFilters"
                        class="bg-gray-800 text-gray-100 hover:bg-gray-400">
                        <x-ui.sccr-icon name="clear" :size="20" />
                        Clear
                    </x-ui.sccr-button>

                    <x-ui.sccr-button type="button" wire:click="toggleColumnPicker"
                        class="bg-gray-700 text-gray-100 hover:bg-gray-400" title="Filter Kolom">
                        <x-ui.sccr-icon name="filter" :size="20" />
                        Kolom
                    </x-ui.sccr-button>

                    <x-ui.sccr-button type="button" wire:click="exportFiltered"
                        class="bg-green-600 text-gray-100 hover:bg-green-700" title="Export Filtered">
                        <x-ui.sccr-icon name="download" :size="20" />
                        Export
                    </x-ui.sccr-button>

                    <x-ui.sccr-button type="button" wire:click="exportSelected"
                        class="bg-green-700 text-gray-100 hover:bg-green-800" title="Export Selected">
                        <x-ui.sccr-icon name="download" :size="20" />
                        Export Selected
                    </x-ui.sccr-button>

                    <x-ui.sccr-button type="button" wire:click="freezeAll"
                        class="bg-blue-600 text-gray-100 hover:bg-blue-700" title="Freeze Semua Lokasi">
                        <x-ui.sccr-icon name="freeze" :size="20" />
                        Freeze
                    </x-ui.sccr-button>

                    <x-ui.sccr-button type="button" wire:click="unfreezeAll"
                        class="bg-orange-500 text-gray-100 hover:bg-orange-600" title="Unfreeze Semua Lokasi">
                        <x-ui.sccr-icon name="unlock" :size="20" />
                        Unfreeze
                    </x-ui.sccr-button>
                </div>
            </form>

            <div class="flex items-end gap-1 ml-auto">
                <div class="relative top-0">
                    <span class="absolute -top-4 left-1 text-[10px] font-bold text-black uppercase">Show</span>
                    <select wire:model.live="perPage" class="border-gray-300 rounded-md text-sm">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>

        </div>

        @if ($showColumnPicker)
            <div class="mt-2 p-3 bg-white border rounded-lg shadow-md">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-bold text-gray-700">Pilih Kolom yang Ditampilkan</span>
                    <x-ui.sccr-button type="button" wire:click="resetColumns"
                        class="bg-gray-200 text-gray-700 hover:bg-gray-300 text-xs">
                        Reset
                    </x-ui.sccr-button>
                </div>
                <div class="flex flex-wrap gap-4">
                    @foreach ($availableColumns as $col)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" wire:model.live="columnVisibility.{{ $col['key'] }}"
                                class="rounded border-gray-300">
                            <span class="text-gray-700">{{ $col['label'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <div class="flex-1 min-h-0 px-4 pb-2">
        <div class="h-full min-h-0 rounded-xl shadow border bg-white overflow-hidden flex flex-col">

            <div class="flex-1 min-h-0 overflow-auto">
                <table class="min-w-full divide-y divide-gray-900">
                    <thead class="bg-gray-700/80 text-white sticky top-0 z-10">
                        <tr>
                            <th class="px-2 py-3 text-center w-10">
                                <input type="checkbox" wire:model.live="selectAll" class="rounded border-gray-300">
                            </th>

                            @if ($columnVisibility['id'] ?? true)
                                <th wire:click="sortBy('id')"
                                    class="px-3 py-3 text-left text-xs font-bold cursor-pointer">
                                    ID {!! $sortField === 'id' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                                </th>
                            @endif

                            @if ($columnVisibility['reference_number'] ?? true)
                                <th wire:click="sortBy('reference_number')"
                                    class="px-3 py-3 text-left text-xs font-bold cursor-pointer">
                                    Reference {!! $sortField === 'reference_number' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                                </th>
                            @endif

                            @if ($columnVisibility['location_id'] ?? true)
                                <th wire:click="sortBy('location_id')"
                                    class="px-3 py-3 text-left text-xs font-bold cursor-pointer">
                                    Lokasi {!! $sortField === 'location_id' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                                </th>
                            @endif

                            @if ($columnVisibility['opname_date'] ?? true)
                                <th wire:click="sortBy('opname_date')"
                                    class="px-3 py-3 text-left text-xs font-bold cursor-pointer">
                                    Tanggal {!! $sortField === 'opname_date' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                                </th>
                            @endif

                            @if ($columnVisibility['status'] ?? true)
                                <th wire:click="sortBy('status')"
                                    class="px-3 py-3 text-center text-xs font-bold cursor-pointer">
                                    Status {!! $sortField === 'status' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                                </th>
                            @endif

                            @if ($columnVisibility['checker_name'] ?? true)
                                <th wire:click="sortBy('checker_name')"
                                    class="px-3 py-3 text-left text-xs font-bold cursor-pointer">
                                    Checker {!! $sortField === 'checker_name' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                                </th>
                            @endif

                            @if ($columnVisibility['witness_name'] ?? false)
                                <th class="px-3 py-3 text-left text-xs font-bold">Witness</th>
                            @endif

                            @if ($columnVisibility['is_frozen'] ?? false)
                                <th class="px-3 py-3 text-center text-xs font-bold">Frozen</th>
                            @endif

                            @if ($columnVisibility['remark'] ?? false)
                                <th class="px-3 py-3 text-left text-xs font-bold">Remark</th>
                            @endif

                            @if ($columnVisibility['created_at'] ?? false)
                                <th wire:click="sortBy('created_at')"
                                    class="px-3 py-3 text-left text-xs font-bold cursor-pointer">
                                    Created {!! $sortField === 'created_at' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                                </th>
                            @endif

                            <th class="px-4 py-3 text-center text-xs font-bold">
                                <div class="flex items-center justify-center gap-2">
                                    <span>Aksi</span>
                                    <x-ui.sccr-button type="button" variant="icon-circle" wire:click="openCreateOverlay"
                                        class="w-8 h-8 hover:scale-105" title="Tambah Data">
                                        <x-ui.sccr-icon name="plus" :size="18" />
                                    </x-ui.sccr-button>
                                </div>
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-gray-100">
                        @forelse ($data as $item)
                            <tr class="hover:bg-gray-200 transition {{ $item->deleted_at ? 'bg-red-50' : '' }}">
                                <td class="px-2 py-2 text-center">
                                    <input type="checkbox" value="{{ $item['id'] }}" wire:model.live="selectedItems"
                                        class="rounded border-gray-300">
                                </td>

                                @if ($columnVisibility['id'] ?? true)
                                    <td class="px-3 py-2 text-sm font-mono">{{ $item['id'] }}</td>
                                @endif

                                @if ($columnVisibility['reference_number'] ?? true)
                                    <td class="px-3 py-2 font-mono text-sm font-semibold text-teal-700">
                                        {{ $item['reference_number'] ?? '-' }}
                                    </td>
                                @endif

                                @if ($columnVisibility['location_id'] ?? true)
                                    <td class="px-3 py-2 text-sm">{{ $item->location?->name ?? '-' }}</td>
                                @endif

                                @if ($columnVisibility['opname_date'] ?? true)
                                    <td class="px-3 py-2 text-sm">{{ $item->opname_date?->format('Y-m-d') ?? '-' }}</td>
                                @endif

                                @if ($columnVisibility['status'] ?? true)
                                    <td class="px-3 py-2 text-center">
                                        @if ($item['status'] === 'draft')
                                            <span class="px-2 py-0.5 rounded bg-gray-200 text-gray-700 text-xs">Draft</span>
                                        @elseif ($item['status'] === 'requested')
                                            @php
                                                $level = $item['approval_level'] ?? 0;
                                                $levelNames = [0 => '', 1 => '> EC', 2 => '> RM', 3 => '> SPV'];
                                            @endphp
                                            <span class="px-2 py-0.5 rounded bg-yellow-100 text-yellow-800 text-xs">Requested{{ $levelNames[$level] ?? '' }}</span>
                                        @elseif($item['status'] === 'completed')
                                            <span class="px-2 py-0.5 rounded bg-green-100 text-green-800 text-xs">Completed</span>
                                        @elseif($item['status'] === 'rejected')
                                            <span class="px-2 py-0.5 rounded bg-red-100 text-red-800 text-xs">Rejected</span>
                                        @elseif($item['status'] === 'cancelled')
                                            <span class="px-2 py-0.5 rounded bg-orange-100 text-orange-800 text-xs">Cancelled</span>
                                        @else
                                            {{ $item['status'] }}
                                        @endif
                                    </td>
                                @endif

                                @if ($columnVisibility['checker_name'] ?? true)
                                    <td class="px-3 py-2 text-sm">{{ $item['checker_name'] ?? '-' }}</td>
                                @endif

                                @if ($columnVisibility['witness_name'] ?? false)
                                    <td class="px-3 py-2 text-sm">{{ $item['witness_name'] ?? '-' }}</td>
                                @endif

                                @if ($columnVisibility['is_frozen'] ?? false)
                                    <td class="px-3 py-2 text-center">
                                        @if ($item['is_frozen'])
                                            <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 text-xs">Frozen</span>
                                        @else
                                            <span class="text-gray-400 text-xs">-</span>
                                        @endif
                                    </td>
                                @endif

                                @if ($columnVisibility['remark'] ?? false)
                                    <td class="px-3 py-2 text-sm">{{ Str::limit($item['remark'] ?? '-', 30) }}</td>
                                @endif

                                @if ($columnVisibility['created_at'] ?? false)
                                    <td class="px-3 py-2 text-sm">{{ $item['created_at']?->format('Y-m-d H:i') ?? '-' }}</td>
                                @endif

                                <td class="px-3 py-2 text-center">
                                    <div class="flex justify-center gap-2">
                                        @if (! $item->deleted_at)
                                            <a href="{{ route('dashboard.resto.stock-opname.detail', $item['id']) }}"
                                                class="text-gray-700 hover:scale-125" title="Detail">
                                                <x-ui.sccr-icon name="eye" :size="18" />
                                            </a>

                                            <x-ui.sccr-button type="button" variant="icon"
                                                wire:click="cloneItem('{{ $item['id'] }}')"
                                                class="text-blue-600 hover:scale-125" title="Clone">
                                                <x-ui.sccr-icon name="copy" :size="18" />
                                            </x-ui.sccr-button>

                                            <x-ui.sccr-button type="button" variant="icon"
                                                wire:click="deleteItem('{{ $item['id'] }}')"
                                                class="text-red-600 hover:scale-125" title="Hapus">
                                                <x-ui.sccr-icon name="trash" :size="18" />
                                            </x-ui.sccr-button>

                                            @if ($item['status'] === 'draft')
                                                <x-ui.sccr-button type="button" variant="icon"
                                                    wire:click="submitOpname('{{ $item['id'] }}')"
                                                    class="text-green-600 hover:scale-125" title="Submit for Approval">
                                                    <x-ui.sccr-icon name="send" :size="18" />
                                                </x-ui.sccr-button>
                                            @endif

                                            @if ($item['status'] === 'requested')
                                                @php $approvalLevel = $item['approval_level'] ?? 0; @endphp

                                                @if ($approvalLevel == 0)
                                                    <x-ui.sccr-button type="button" variant="icon"
                                                        wire:click="excChefCanApprove('{{ $item['id'] }}')"
                                                        class="text-green-600 hover:scale-125" title="Approve (Exc Chef)">
                                                        <x-ui.sccr-icon name="approve" :size="18" />
                                                    </x-ui.sccr-button>
                                                @endif

                                                @if ($approvalLevel == 1)
                                                    <x-ui.sccr-button type="button" variant="icon"
                                                        wire:click="rmCanApprove('{{ $item['id'] }}')"
                                                        class="text-green-600 hover:scale-125" title="Approve (RM)">
                                                        <x-ui.sccr-icon name="approve" :size="18" />
                                                    </x-ui.sccr-button>
                                                @endif

                                                @if ($approvalLevel == 2)
                                                    <x-ui.sccr-button type="button" variant="icon"
                                                        wire:click="spvCanApprove('{{ $item['id'] }}')"
                                                        class="text-green-600 hover:scale-125" title="Approve (SPV)">
                                                        <x-ui.sccr-icon name="approve" :size="18" />
                                                    </x-ui.sccr-button>
                                                @endif

                                                @if ($approvalLevel == 3)
                                                    <x-ui.sccr-button type="button" variant="icon"
                                                        wire:click="finalizeOpname('{{ $item['id'] }}')"
                                                        class="text-teal-600 hover:scale-125" title="Finalize & Adjust">
                                                        <x-ui.sccr-icon name="check" :size="18" />
                                                    </x-ui.sccr-button>
                                                @endif

                                                <x-ui.sccr-button type="button" variant="icon"
                                                    wire:click="rejectOpname('{{ $item['id'] }}')"
                                                    class="text-red-600 hover:scale-125" title="Tolak">
                                                    <x-ui.sccr-icon name="no" :size="18" />
                                                </x-ui.sccr-button>
                                            @endif

                                            @if (in_array($item['status'], ['draft', 'requested']))
                                                <x-ui.sccr-button type="button" variant="icon"
                                                    wire:click="cancelOpname('{{ $item['id'] }}')"
                                                    class="text-orange-600 hover:scale-125" title="Cancel">
                                                    <x-ui.sccr-icon name="cancel" :size="18" />
                                                </x-ui.sccr-button>
                                            @endif
                                        @else
                                            <x-ui.sccr-button type="button" variant="icon"
                                                wire:click="restoreItem('{{ $item['id'] }}')"
                                                class="text-green-600 hover:scale-125" title="Restore">
                                                <x-ui.sccr-icon name="refresh" :size="18" />
                                            </x-ui.sccr-button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="py-10 text-center text-gray-400 italic">
                                    Data tidak ditemukan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex-none px-6 py-3 border-t bg-white flex flex-col md:flex-row justify-between items-center gap-3">
                <div class="text-sm text-gray-600 flex items-center">
                    <span class="font-bold text-gray-800 mr-1">{{ count($selectedItems) }}</span> item dipilih
                </div>

                <div>{{ $data->links() }}</div>
            </div>

        </div>
    </div>

    <x-ui.sccr-toast :show="$toast['show']" :type="$toast['type']" :message="$toast['message']" wire:key="toast-{{ microtime() }}" />

    @if ($overlayMode === 'create')
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click="closeOverlay">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden" wire:click.stop>
                <div class="px-6 py-4 border-b bg-teal-600 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-white">Buat Stock Opname</h3>
                    <button wire:click="closeOverlay" class="text-white hover:text-gray-200 text-2xl">&times;</button>
                </div>

                <div class="p-6 overflow-y-auto max-h-[70vh]">
                    <form wire:submit.prevent="processCreate" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi <span class="text-red-500">*</span></label>
                                <select wire:model.live="createLocationId" class="w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="0">-- Pilih Lokasi --</option>
                                    @foreach($this->getLocations() as $loc)
                                        <option value="{{ $loc['id'] }}">{{ $loc['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Opname <span class="text-red-500">*</span></label>
                                <input type="date" wire:model="createOpnameDate" class="w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Checker <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="createCheckerName" class="w-full border-gray-300 rounded-md shadow-sm" placeholder="Nama penanggung jawab">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Role Checker</label>
                                <input type="text" wire:model="createCheckerRole" class="w-full border-gray-300 rounded-md shadow-sm" placeholder="Sous Chef / Store Keeper">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Witness <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="createWitnessName" class="w-full border-gray-300 rounded-md shadow-sm" placeholder="Nama saksi">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Role Witness</label>
                                <input type="text" wire:model="createWitnessRole" class="w-full border-gray-300 rounded-md shadow-sm" placeholder="Store Keeper / Supervisor">
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <label class="block text-sm font-medium text-gray-700">Daftar Item</label>
                                @if(count($createItems) > 0)
                                    <button type="button" wire:click="addCreateItemRow" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                        + Tambah Item
                                    </button>
                                @endif
                            </div>

                            @if(count($createItems) === 0)
                                <p class="text-sm text-gray-500 text-center py-4">Pilih lokasi terlebih dahulu untuk menampilkan daftar item.</p>
                            @else
                                <div class="border rounded-lg overflow-hidden">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                                <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase w-24">Stok Sistem</th>
                                                <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase w-28">Stok Fisik</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Catatan</th>
                                                <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase w-12">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 bg-white">
                                            @foreach($createItems as $index => $item)
                                                <tr>
                                                    <td class="px-3 py-2">
                                                        <select wire:model="createItems.{{ $index }}.item_id" class="w-full border-gray-300 rounded-md text-sm">
                                                            <option value="0">-- Pilih Item --</option>
                                                            @foreach($this->getAvailableItems() as $availItem)
                                                                <option value="{{ $availItem['id'] }}">
                                                                    {{ $availItem['name'] }} ({{ $availItem['sku'] }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td class="px-3 py-2 text-center text-sm">
                                                        @if($item['item_id'] > 0)
                                                            {{ number_format($this->getSystemQty($item['item_id']), 2) }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        <input type="number" step="0.01" min="0" wire:model="createItems.{{ $index }}.physical_qty" class="w-full border-gray-300 rounded-md text-sm text-right" placeholder="0">
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        <input type="text" wire:model="createItems.{{ $index }}.remark" class="w-full border-gray-300 rounded-md text-sm" placeholder="Catatan">
                                                    </td>
                                                    <td class="px-3 py-2 text-center">
                                                        <button type="button" wire:click="removeCreateItemRow({{ $index }})" class="text-red-600 hover:text-red-800 text-sm" title="Hapus">✕</button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                            <textarea wire:model="createRemark" rows="2" class="w-full border-gray-300 rounded-md shadow-sm" placeholder="Catatan opsional..."></textarea>
                        </div>

                        <div class="flex gap-3 pt-4">
                            <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-md hover:bg-teal-700">
                                Simpan
                            </button>
                            <button type="button" wire:click="closeOverlay" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

</x-ui.sccr-card>
