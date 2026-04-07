<x-ui.sccr-card transparent wire:key="approval-inbox-table" class="h-full min-h-0 flex flex-col">

    <div class="relative px-8 py-6 bg-gray-900/90 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">Approval Inbox</h1>
                <p class="text-gray-200 text-sm">Approve / Reject semua request lintas module (Inventaris, SSO, dll)</p>
            </div>
        </div>

        <div class="mt-4 flex justify-between items-center text-sm">
            <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
            <div class="text-white">
                Menampilkan <span class="font-bold text-yellow-300">{{ $approvals->total() }}</span> request
            </div>
        </div>
    </div>

    <div class="px-4 pt-3 pb-1">
        <div class="flex flex-wrap items-center justify-between gap-2">

            <div class="flex flex-wrap items-center gap-2 flex-grow">
                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Cari</span>
                    <x-ui.sccr-input wire:model.live="search"
                        placeholder="module / permission / kode_label / target_user_id" class="w-72" />
                </div>

                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Status</span>
                    <x-ui.sccr-select wire:model.live="status" :options="[
                        '' => 'All',
                        'pending' => 'pending',
                        'approved' => 'approved',
                        'rejected' => 'rejected',
                    ]" class="w-44" />
                </div>

                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Module</span>
                    <x-ui.sccr-input wire:model.live="moduleCode" placeholder="contoh: 01005 / 00000" class="w-36" />
                </div>

                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Permission</span>
                    <x-ui.sccr-input wire:model.live="permissionCode" placeholder="INV_DELETE / SSO_USER_DEACTIVATE"
                        class="w-64" />
                </div>
            </div>

            <div class="flex items-end gap-2 ml-auto">
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
    </div>

    <div class="flex-1 min-h-0 px-4 pb-2">
        <div class="h-full min-h-0 rounded-xl shadow border bg-white overflow-hidden flex flex-col">

            <div class="flex-1 min-h-0 overflow-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-700/90 text-white sticky top-0 z-10">
                        <tr>
                            <th wire:click="sortBy('created_at')"
                                class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                                Waktu {!! $sortField === 'created_at' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>
                            <th wire:click="sortBy('module_code')"
                                class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                                Module {!! $sortField === 'module_code' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>
                            <th wire:click="sortBy('permission_code')"
                                class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                                Permission {!! $sortField === 'permission_code' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-bold">Requester</th>
                            <th class="px-4 py-3 text-left text-xs font-bold">Payload</th>
                            <th wire:click="sortBy('status')"
                                class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                                Status {!! $sortField === 'status' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-bold">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-gray-50">
                        @forelse ($approvals as $a)
                            <tr class="hover:bg-gray-100 transition">
                                <td class="px-4 py-2 text-xs text-gray-700">
                                    {{ optional($a->created_at)->format('Y-m-d H:i:s') ?? '-' }}
                                </td>

                                <td class="px-4 py-2 text-sm font-mono font-semibold">
                                    {{ $a->module_code }}
                                </td>

                                <td class="px-4 py-2 text-sm font-mono">
                                    {{ $a->permission_code }}
                                </td>

                                <td class="px-4 py-2 text-xs">
                                    <div class="font-semibold">
                                        {{ $a->requester?->username ?? 'user#' . $a->auth_user_id }}</div>
                                    <div class="text-gray-500">
                                        {{ $a->requester?->email ?? '' }}
                                    </div>
                                </td>

                                <td class="px-4 py-2 text-xs">
                                    <div class="font-mono text-gray-700">
                                        {{ $this->payloadSummary($a) }}
                                    </div>
                                </td>

                                <td class="px-4 py-2 text-xs">
                                    <x-ui.sccr-badge :type="$a->status === 'pending' ? 'warning' : ($a->status === 'approved' ? 'success' : 'danger')">
                                        {{ $a->status }}
                                    </x-ui.sccr-badge>
                                    @if ($a->status === 'rejected' && $a->rejected_reason)
                                        <div class="text-[11px] text-gray-500 mt-1">Reason: {{ $a->rejected_reason }}
                                        </div>
                                    @endif
                                </td>

                                <td class="px-4 py-2 text-center">
                                    <div class="flex justify-center gap-2">
                                        @if ($a->status === 'pending')
                                            @permission('APPROVAL_APPROVE')
                                                <x-ui.sccr-button type="button" wire:click="approve({{ (int) $a->id }})"
                                                    class="bg-emerald-600 hover:bg-emerald-700 text-white h-[30px] px-3 text-xs">
                                                    Approve
                                                </x-ui.sccr-button>

                                                <x-ui.sccr-button type="button"
                                                    wire:click="openReject({{ (int) $a->id }})"
                                                    class="bg-red-600 hover:bg-red-700 text-white h-[30px] px-3 text-xs">
                                                    Reject
                                                </x-ui.sccr-button>
                                            @endpermission
                                        @else
                                            <span class="text-xs text-gray-400 italic">-</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-10 text-center text-gray-400 italic">
                                    Tidak ada data
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if ($showRejectModal)
                    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
                        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800">Reject Approval</h3>
                                    <p class="text-xs text-gray-500 mt-1">Wajib isi alasan (maks 255 karakter).</p>
                                </div>

                                <x-ui.sccr-button type="button" variant="icon" wire:click="cancelReject"
                                    class="text-gray-500 hover:text-gray-800" title="Tutup">
                                    <span class="text-xl leading-none">×</span>
                                </x-ui.sccr-button>
                            </div>

                            <div class="mt-4">
                                <label class="text-sm font-bold text-gray-700">Alasan Reject</label>
                                <textarea wire:model.live="rejectReason" class="w-full border-gray-300 rounded-lg text-sm mt-1" rows="3"
                                    placeholder="Contoh: data belum lengkap / tidak sesuai scope / salah input"></textarea>
                            </div>

                            <div class="mt-6 flex justify-end gap-3">
                                <x-ui.sccr-button type="button" variant="secondary" wire:click="cancelReject">
                                    Batal
                                </x-ui.sccr-button>

                                <x-ui.sccr-button type="button" variant="danger" wire:click="submitReject">
                                    Reject
                                </x-ui.sccr-button>
                            </div>
                        </div>
                    </div>
                @endif

            </div>

            <div
                class="flex-none px-6 py-3 border-t bg-white flex flex-col md:flex-row justify-between items-center gap-3">
                <div class="text-sm text-gray-600">
                    {{ $approvals->firstItem() ?? 0 }}-{{ $approvals->lastItem() ?? 0 }} dari
                    {{ $approvals->total() }}
                </div>
                <div>
                    {{ $approvals->links() }}
                </div>
            </div>

        </div>
    </div>

    <x-ui.sccr-toast :show="$toast['show']" :type="$toast['type']" :message="$toast['message']" wire:key="toast-{{ microtime() }}" />

</x-ui.sccr-card>
