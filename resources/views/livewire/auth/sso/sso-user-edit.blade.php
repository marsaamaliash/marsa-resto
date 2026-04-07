<div class="p-6">
    <h2 class="text-xl font-black text-slate-900">Edit SSO User</h2>
    <p class="text-sm text-slate-600 mt-1">Update profile/login dan scope identity sesuai permission.</p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
        <div>
            <label class="text-xs font-bold uppercase text-slate-600">Username</label>
            <x-ui.sccr-input wire:model.live="username" class="w-full" />
        </div>

        <div>
            <label class="text-xs font-bold uppercase text-slate-600">Email</label>
            <x-ui.sccr-input wire:model.live="email" class="w-full" />
        </div>

        <div class="flex items-center gap-4">
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" wire:model.live="is_locked" class="rounded border-gray-300">
                <span class="text-sm font-semibold">Locked</span>
            </label>

            <label class="inline-flex items-center gap-2">
                <input type="checkbox" wire:model.live="is_active" class="rounded border-gray-300">
                <span class="text-sm font-semibold">Identity Active</span>
            </label>
        </div>

        <div>
            <label class="text-xs font-bold uppercase text-slate-600">Identity (read-only)</label>
            <div class="text-sm p-3 rounded-lg bg-slate-50 border">
                <div><b>{{ $identity_type }}</b></div>
                <div class="font-mono text-slate-700">{{ $identity_key }}</div>
            </div>
        </div>

        <div>
            <label class="text-xs font-bold uppercase text-slate-600">Holding</label>
            <x-ui.sccr-select wire:model.live="holding_id" :options="$holdingOptions" class="w-full" />
        </div>

        <div>
            <label class="text-xs font-bold uppercase text-slate-600">Department</label>
            <x-ui.sccr-select wire:model.live="department_id" :options="$departmentOptions" class="w-full" />
        </div>

        <div>
            <label class="text-xs font-bold uppercase text-slate-600">Division</label>
            <x-ui.sccr-select wire:model.live="division_id" :options="$divisionOptions" class="w-full" />
        </div>
    </div>

    <div class="mt-8 flex justify-end gap-2">
        <x-ui.sccr-button type="button" variant="secondary" wire:click="$dispatch('sso-user-overlay-close')">
            Batal
        </x-ui.sccr-button>
        <x-ui.sccr-button type="button" variant="primary" wire:click="save"
            class="bg-slate-900 text-white hover:bg-slate-700">
            Simpan
        </x-ui.sccr-button>
    </div>

    <x-ui.sccr-toast :show="$toast['show']" :type="$toast['type']" :message="$toast['message']" wire:key="toast-edit-{{ microtime() }}" />
</div>
