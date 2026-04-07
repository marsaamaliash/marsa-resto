<div class="p-6">
    <h2 class="text-xl font-black text-slate-900">Create / Link SSO User</h2>
    <p class="text-sm text-slate-600 mt-1">Buat identity + user login. Username default = identity_key.</p>

    {{-- ✅ FORM submit (paling stabil) --}}
    <form wire:submit.prevent="save" class="mt-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="text-xs font-bold uppercase text-slate-600">Identity Type</label>
                <x-ui.sccr-select wire:model.defer="identity_type" :options="[
                    'employee' => 'employee',
                    'lecturer' => 'lecturer',
                    'student' => 'student',
                ]" class="w-full" />
                @error('identity_type')
                    <div class="text-xs text-rose-600 mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label class="text-xs font-bold uppercase text-slate-600">Identity Key</label>
                <x-ui.sccr-input wire:model.defer="identity_key" placeholder="ex: 20231111 1 001 / 202002011001"
                    class="w-full" />
                @error('identity_key')
                    <div class="text-xs text-rose-600 mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label class="text-xs font-bold uppercase text-slate-600">Username (optional)</label>
                <x-ui.sccr-input wire:model.defer="username" placeholder="kosong = pakai identity_key" class="w-full" />
                @error('username')
                    <div class="text-xs text-rose-600 mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label class="text-xs font-bold uppercase text-slate-600">Email (optional)</label>
                <x-ui.sccr-input wire:model.defer="email" placeholder="name@domain.com" class="w-full" />
                @error('email')
                    <div class="text-xs text-rose-600 mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label class="text-xs font-bold uppercase text-slate-600">Holding</label>
                <x-ui.sccr-select wire:model.defer="holding_id" :options="$holdingOptions" class="w-full" />
                @error('holding_id')
                    <div class="text-xs text-rose-600 mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label class="text-xs font-bold uppercase text-slate-600">Department</label>
                <x-ui.sccr-select wire:model.defer="department_id" :options="$departmentOptions" class="w-full" />
                @error('department_id')
                    <div class="text-xs text-rose-600 mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label class="text-xs font-bold uppercase text-slate-600">Division</label>
                <x-ui.sccr-select wire:model.defer="division_id" :options="$divisionOptions" class="w-full" />
                @error('division_id')
                    <div class="text-xs text-rose-600 mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="flex items-center gap-4">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" wire:model.defer="is_active" class="rounded border-gray-300">
                    <span class="text-sm font-semibold">Identity Active</span>
                </label>

                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" wire:model.defer="is_locked" class="rounded border-gray-300">
                    <span class="text-sm font-semibold">Locked</span>
                </label>
            </div>
        </div>

        <div class="mt-6">
            <label class="text-xs font-bold uppercase text-slate-600">Assign Roles (optional)</label>
            <div class="mt-2 grid grid-cols-2 md:grid-cols-3 gap-2">
                @foreach ($roleOptions as $id => $label)
                    <label class="flex items-center gap-2 p-2 rounded-lg bg-slate-50 border">
                        <input type="checkbox" value="{{ $id }}" wire:model.defer="role_ids"
                            class="rounded border-gray-300">
                        <span class="text-sm">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
            @error('role_ids')
                <div class="text-xs text-rose-600 mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mt-8 flex justify-end gap-2">
            <button type="button"
                class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold"
                wire:click="$dispatch('sso-user-overlay-close')">
                Batal
            </button>

            <button type="submit" class="px-4 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-700 font-bold"
                wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save">Simpan</span>
                <span wire:loading wire:target="save">Menyimpan...</span>
            </button>
        </div>

        <x-ui.sccr-toast :show="$toast['show']" :type="$toast['type']" :message="$toast['message']"
            wire:key="toast-create-{{ microtime() }}" />
    </form>
</div>
