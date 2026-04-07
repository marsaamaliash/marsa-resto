<x-ui.sccr-card transparent wire:key="sso-approval-inbox" class="h-full min-h-0 flex flex-col">

    {{-- HEADER --}}
    <div class="relative px-8 py-6 bg-slate-900/80 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">Approval Inbox</h1>
                <p class="text-slate-200 text-sm">
                    Persetujuan / penolakan request lintas modul (Inventaris, SSO, dll)
                </p>
            </div>
        </div>

        <div class="mt-4 flex justify-between items-center text-sm">
            <x-ui.sccr-breadcrumb :items="$breadcrumbs ?? []" />
            <div class="text-white">
                Menampilkan <span class="font-bold text-black">{{ $rows?->total() ?? 0 }}</span> request
            </div>
        </div>
    </div>

    {{-- CONTENT --}}
    <div class="flex-1 min-h-0 px-4 py-4">
        <div class="h-full min-h-0 rounded-xl shadow border bg-white overflow-hidden flex flex-col">

            <div class="flex-1 min-h-0 overflow-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-700/80 text-white sticky top-0 z-10">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-bold">Module</th>
                            <th class="px-4 py-3 text-left text-xs font-bold">Permission</th>
                            <th class="px-4 py-3 text-left text-xs font-bold">Requester</th>
                            <th class="px-4 py-3 text-left text-xs font-bold">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-bold">Created</th>
                            <th class="px-4 py-3 text-center text-xs font-bold">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-gray-50">
                        @forelse(($rows ?? []) as $r)
                            @php
                                $payload = (array) ($r->action_payload ?? []);
                                $reqUser = $r->requester?->username ?? ($r->auth_user_id ?? '-');
                            @endphp

                            <tr class="hover:bg-gray-100 transition">
                                <td class="px-4 py-2 text-xs font-mono">{{ $r->id }}</td>
                                <td class="px-4 py-2 text-xs font-mono">{{ $r->module_code }}</td>
                                <td class="px-4 py-2 text-xs font-mono">{{ $r->permission_code }}</td>
                                <td class="px-4 py-2 text-xs">{{ $reqUser }}</td>

                                <td class="px-4 py-2 text-xs">
                                    <span
                                        class="px-2 py-1 rounded-full text-[11px] font-bold
                                        {{ $r->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $r->status === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $r->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}
                                    ">
                                        {{ strtoupper($r->status ?? '-') }}
                                    </span>
                                </td>

                                <td class="px-4 py-2 text-xs text-gray-700">
                                    {{ $r->created_at ? \Illuminate\Support\Carbon::parse($r->created_at)->format('d M Y H:i') : '-' }}
                                </td>

                                <td class="px-4 py-2 text-center">
                                    <div class="flex justify-center gap-2">
                                        {{-- tombol ini hanya placeholder agar view aman.
                                             Sesuaikan dengan method Livewire kamu (approve/reject/openDetail) --}}
                                        <x-ui.sccr-button type="button" variant="icon"
                                            wire:click="openShow('{{ $r->id }}')"
                                            class="text-gray-700 hover:scale-125" title="Detail">
                                            <x-ui.sccr-icon name="eye" :size="20" />
                                        </x-ui.sccr-button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-10 text-center text-gray-400 italic">
                                    Tidak ada data approval.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- FOOTER PAGINATION --}}
            <div class="flex-none px-6 py-3 border-t bg-white flex justify-end">
                {{ $rows?->links() }}
            </div>
        </div>
    </div>

    {{-- TOAST --}}
    <x-ui.sccr-toast :show="$toast['show'] ?? false" :type="$toast['type'] ?? 'success'" :message="$toast['message'] ?? ''" />

</x-ui.sccr-card>
