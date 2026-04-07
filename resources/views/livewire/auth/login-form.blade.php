<x-ui.sccr-card class="w-full max-w-md bg-white p-8 rounded-2xl shadow-lg">
    <div class="flex flex-col items-center mb-6">
        <img src="{{ asset('images/logoSCCR.png') }}" alt="Logo SCCR" class="h-24 w-auto mb-3">
    </div>

    <h2 class="text-2xl font-semibold text-center text-gray-800 mb-6">Login</h2>

    @if ($errors->any())
        <x-ui.sccr-alert type="danger" :message="$errors->first()" />
    @endif

    <form wire:submit.prevent="authenticate" class="space-y-4">
        <x-ui.sccr-input name="login" wire:model.defer="login" label="NIP atau Email" required autofocus />

        <div x-data="{ show: false }" class="space-y-1">
            <label class="text-sm font-semibold text-gray-700">Password</label>

            <div class="relative">
                <input wire:model.defer="password" :type="show ? 'text' : 'password'" required
                    class="w-full rounded-lg border-gray-300 text-sm px-3 py-2 pr-12" autocomplete="current-password" />
                <button type="button"
                    class="absolute right-2 top-1/2 -translate-y-1/2 w-9 h-9 rounded-md hover:bg-gray-100"
                    @click="show=!show" title="Show/Hide Password">
                    <span x-show="!show">👁️</span>
                    <span x-show="show">😎</span>
                </button>
            </div>
        </div>

        <x-ui.sccr-toggle name="remember" wire:model="remember" label="Ingat saya" />

        <x-ui.sccr-button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
            <span wire:loading.remove>Login</span>
            <span wire:loading>Memproses...</span>
        </x-ui.sccr-button>
    </form>
</x-ui.sccr-card>
