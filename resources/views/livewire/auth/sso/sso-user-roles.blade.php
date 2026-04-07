<div class="p-6">
    <div class="flex items-start justify-between gap-4">
        <div>
            <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Role Assignment</div>
            <h2 class="text-xl font-extrabold text-gray-900">
                {{ $userInfo['username'] }}
                @if (!empty($userInfo['email']))
                    <span class="text-sm font-normal text-gray-500">({{ $userInfo['email'] }})</span>
                @endif
            </h2>
            <div class="text-xs text-gray-600 mt-1 font-mono">
                {{ $userInfo['identity_type'] }} · {{ $userInfo['identity_key'] }}
            </div>
        </div>

        <x-ui.sccr-button type="button" variant="icon" wire:click="close" class="text-gray-400 hover:text-red-600"
            title="Tutup">
            <span class="text-xl leading-none">✕</span>
        </x-ui.sccr-button>
    </div>

    <div class="mt-4 flex flex-wrap items-end justify-between gap-3">
        <div class="flex-1 min-w-[240px]">
            <label class="text-xs font-bold text-gray-700">Cari Role</label>
            <input type="text" wire:model.live="searchRole" class="w-full border-gray-300 rounded-lg text-sm mt-1"
                placeholder="DEV / STAFF / MGR / HEAD..." />
        </div>

        <div class="text-xs text-gray-600">
            Terpilih: <b>{{ count(array_filter($selectedRoleIds ?? [])) }}</b>
        </div>
    </div>

    <div class="mt-4 border rounded-2xl overflow-hidden">
        <div class="bg-slate-800 text-white px-4 py-2 text-sm font-bold">
            Daftar Role
        </div>

        <div class="max-h-[55vh] overflow-auto bg-white p-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                @forelse ($roles as $r)
                    @php($rid = (int) $r['id'])
                    <label class="flex items-start gap-3 p-2 rounded-xl border hover:bg-gray-50">
                        <input type="checkbox" class="rounded border-gray-300 mt-1"
                            wire:model.live="selectedRoleIds.{{ $rid }}">
                        <div class="min-w-0">
                            <div class="text-sm font-mono font-bold text-gray-900">{{ $r['code'] }}</div>
                            <div class="text-xs text-gray-500">{{ $r['name'] }}</div>
                        </div>
                    </label>
                @empty
                    <div class="py-8 text-center text-gray-400 italic col-span-2">Role tidak ditemukan</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="mt-5 flex justify-end gap-2">
        <x-ui.sccr-button type="button" variant="secondary" wire:click="close">
            Batal
        </x-ui.sccr-button>

        <x-ui.sccr-button type="button" variant="success" wire:click="save">
            Simpan Roles
        </x-ui.sccr-button>
    </div>
</div>
