<x-ui.sccr-card class="max-w-4xl mx-auto p-4">
    @if ($row)
        @php
            $coaCode = (string) ($row->code ?? '');
            $coaName = (string) ($row->name ?? '');
            $type = (string) ($row->type ?? '');
            $status = (string) ($row->status ?? '');
            $active = (int) ($row->is_active ?? 0) === 1;

            $holdingName = (string) ($row->holding_name ?? '-');
            $departmentName = (string) ($row->department_name ?? '-');
            $divisionName = (string) ($row->division_name ?? '-');

            $requestedAt = $row->requested_at
                ? \Illuminate\Support\Carbon::parse($row->requested_at)->format('d M Y H:i')
                : null;
        @endphp

        <div class="space-y-4">

            {{-- HEADER --}}
            <div class="bg-slate-900 rounded-xl p-4 shadow-lg border-l-4 border-green-500 relative overflow-hidden">
                <div class="absolute top-0 right-0 p-4 opacity-10">
                    <i class="fas fa-file-invoice-dollar fa-6x text-white"></i>
                </div>

                <span class="text-[10px] font-bold text-green-400 uppercase tracking-[0.2em]">
                    Master Chart of Account • Detail
                </span>

                <h2 class="text-3xl font-mono font-extrabold text-white mt-1">
                    {{ $coaCode !== '' ? $coaCode : 'ID #' . $row->id }}
                </h2>

                <div class="flex flex-wrap justify-between items-center text-xs mt-2 gap-2">
                    <div class="text-gray-300 italic">
                        Holding: <span class="text-white font-semibold">{{ $holdingName }}</span>
                    </div>
                    <div class="text-gray-300 italic">
                        Dept: <span class="text-white font-semibold">{{ $departmentName }}</span>
                    </div>
                    <div class="text-gray-300 italic">
                        Div: <span class="text-white font-semibold">{{ $divisionName }}</span>
                    </div>
                </div>

                <div class="mt-3 flex flex-wrap gap-2">
                    <span
                        class="px-2 py-1 rounded-full text-[11px] font-bold {{ $active ? 'bg-green-200 text-green-900' : 'bg-gray-300 text-gray-800' }}">
                        {{ $active ? 'ACTIVE' : 'INACTIVE' }}
                    </span>

                    <span
                        class="px-2 py-1 rounded-full text-[11px] font-bold
                            {{ $status === 'approved' ? 'bg-emerald-200 text-emerald-900' : '' }}
                            {{ $status === 'pending' ? 'bg-yellow-200 text-yellow-900' : '' }}
                            {{ $status === 'pending_delete' ? 'bg-orange-200 text-orange-900' : '' }}
                            {{ $status === 'rejected' ? 'bg-red-200 text-red-900' : '' }}
                            {{ !in_array($status, ['approved', 'pending', 'pending_delete', 'rejected'], true) ? 'bg-gray-200 text-gray-800' : '' }}
                            ">
                        {{ $status !== '' ? strtoupper($status) : '-' }}
                    </span>

                    @if ($requestedAt)
                        <span class="px-2 py-1 rounded-full text-[11px] font-bold bg-blue-100 text-blue-900">
                            Requested: {{ $requestedAt }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- BODY --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

                {{-- LEFT: Summary --}}
                <div class="lg:col-span-5 space-y-4">
                    <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
                        <div
                            class="bg-gray-50 px-4 py-3 border-b text-sm font-bold text-gray-700 uppercase tracking-wider">
                            Ringkasan
                        </div>

                        <div class="p-4 space-y-3 text-sm">
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">ID</p>
                                <p class="font-semibold text-gray-800">#{{ $row->id }}</p>
                            </div>

                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">CoA Code</p>
                                <p class="font-mono font-bold text-green-700">{{ $coaCode !== '' ? $coaCode : '-' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Nama Akun</p>
                                <p class="font-semibold text-gray-800">{{ $coaName !== '' ? $coaName : '-' }}</p>
                            </div>

                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Type</p>
                                <p class="font-semibold text-gray-800">{{ $type !== '' ? $type : '-' }}</p>
                            </div>

                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Parent ID</p>
                                <p class="font-semibold text-gray-800">{{ $row->parent_id ?? '-' }}</p>
                            </div>

                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Subcategory ID
                                </p>
                                <p class="font-semibold text-gray-800">{{ $row->subcategory_id ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT: Info --}}
                <div class="lg:col-span-7 space-y-4">
                    <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
                        <div
                            class="bg-gray-50 px-4 py-3 border-b text-sm font-bold text-gray-700 uppercase tracking-wider">
                            Informasi
                        </div>

                        <div class="p-4 text-sm space-y-3">
                            <div class="p-3 rounded-lg bg-blue-50 border border-blue-100 text-blue-900 text-xs">
                                <div class="font-semibold mb-1">Catatan</div>
                                <ul class="list-disc ml-5 space-y-1">
                                    <li>Data ini adalah <b>Truth Master</b> (Master CoA).</li>
                                    <li>Perubahan master akan berdampak ke transaksi yang memakai akun ini.</li>
                                    <li>Delete dilakukan via <b>request delete + approval</b> (mengubah status ke
                                        pending_delete).</li>
                                </ul>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div class="p-3 rounded-lg bg-gray-50 border text-xs">
                                    <div class="text-gray-500 font-bold">Holding</div>
                                    <div class="text-gray-900 font-semibold">{{ $holdingName }}</div>
                                </div>
                                <div class="p-3 rounded-lg bg-gray-50 border text-xs">
                                    <div class="text-gray-500 font-bold">Department</div>
                                    <div class="text-gray-900 font-semibold">{{ $departmentName }}</div>
                                </div>
                                <div class="p-3 rounded-lg bg-gray-50 border text-xs">
                                    <div class="text-gray-500 font-bold">Division</div>
                                    <div class="text-gray-900 font-semibold">{{ $divisionName }}</div>
                                </div>
                                <div class="p-3 rounded-lg bg-gray-50 border text-xs">
                                    <div class="text-gray-500 font-bold">Active</div>
                                    <div class="text-gray-900 font-semibold">{{ $active ? 'Yes' : 'No' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- FOOTER ACTIONS --}}
            <div class="flex justify-end gap-3 pt-4 border-t">
                <x-ui.sccr-button variant="secondary" wire:click="$dispatch('fin-master-account-overlay-close')">
                    Kembali
                </x-ui.sccr-button>

                @if (auth()->user()?->hasPermission('FIN_MASTER_ACCOUNT_UPDATE'))
                    <x-ui.sccr-button variant="warning"
                        wire:click="$dispatch('fin-master-account-open-edit', { rowKey: @js((string) $row->id) })">
                        Edit Data
                    </x-ui.sccr-button>
                @endif
            </div>

        </div>
    @endif
</x-ui.sccr-card>
