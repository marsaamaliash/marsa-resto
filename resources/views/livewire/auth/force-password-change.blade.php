<x-ui.sccr-card class="w-full max-w-xl bg-white p-8 rounded-2xl shadow-lg">
    <form class="space-y-4" wire:submit.prevent="save" x-data="{ showOld: false, showNew: false, showConfirm: false }">

        <div>
            <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Security</div>
            <h2 class="text-2xl font-extrabold text-gray-900 mt-1">Wajib Ganti Password</h2>

            <p class="text-sm text-gray-600 mt-2">
                @if ($withoutOld)
                    Anda masih memakai password default <b>password123</b>. Silakan buat password baru.
                @else
                    Silakan masukkan password lama Anda, lalu buat password baru.
                @endif
            </p>
        </div>

        {{-- Password Lama --}}
        <div>
            <label class="text-sm font-semibold text-gray-700">Password Lama</label>

            <div class="relative mt-1">
                <input wire:model.defer="current_password" :type="showOld ? 'text' : 'password'"
                    class="w-full rounded-lg border-gray-300 text-sm px-3 py-2 pr-12 {{ $withoutOld ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                    placeholder="{{ $withoutOld ? 'Default password (tidak perlu diisi)' : 'Masukkan password lama' }}"
                    {{ $withoutOld ? 'disabled' : '' }} autocomplete="current-password" />

                <button type="button"
                    class="absolute right-2 top-1/2 -translate-y-1/2 w-10 h-10 rounded-md hover:bg-gray-100 flex items-center justify-center"
                    @click="showOld = !showOld" {{ $withoutOld ? 'disabled' : '' }} title="Show/Hide password lama">
                    <span x-show="!showOld">🫣</span>
                    <span x-show="showOld">😎</span>
                </button>
            </div>

            @if (!$withoutOld)
                @error('current_password')
                    <div class="text-xs text-rose-600 mt-1">{{ $message }}</div>
                @enderror
            @else
                <div class="text-[11px] text-gray-500 mt-1">
                    Password lama tidak diperlukan karena akun masih menggunakan password default.
                </div>
            @endif
        </div>

        {{-- Password Baru --}}
        <div>
            <label class="text-sm font-semibold text-gray-700">Password Baru</label>

            <div class="relative mt-1">
                <input wire:model.defer="password" :type="showNew ? 'text' : 'password'"
                    class="w-full rounded-lg border-gray-300 text-sm px-3 py-2 pr-12" autocomplete="new-password"
                    required />

                <button type="button"
                    class="absolute right-2 top-1/2 -translate-y-1/2 w-10 h-10 rounded-md hover:bg-gray-100 flex items-center justify-center"
                    @click="showNew = !showNew" title="Show/Hide password baru">
                    <span x-show="!showNew">🫣</span>
                    <span x-show="showNew">😎</span>
                </button>
            </div>

            @error('password')
                <div class="text-xs text-rose-600 mt-1">{{ $message }}</div>
            @enderror
        </div>

        {{-- Konfirmasi Password Baru --}}
        <div>
            <label class="text-sm font-semibold text-gray-700">Konfirmasi Password Baru</label>

            <div class="relative mt-1">
                <input wire:model.defer="password_confirmation" :type="showConfirm ? 'text' : 'password'"
                    class="w-full rounded-lg border-gray-300 text-sm px-3 py-2 pr-12" autocomplete="new-password"
                    required />

                <button type="button"
                    class="absolute right-2 top-1/2 -translate-y-1/2 w-10 h-10 rounded-md hover:bg-gray-100 flex items-center justify-center"
                    @click="showConfirm = !showConfirm" title="Show/Hide konfirmasi password">
                    <span x-show="!showConfirm">🫣</span>
                    <span x-show="showConfirm">😎</span>
                </button>
            </div>

            @error('password_confirmation')
                <div class="text-xs text-rose-600 mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="pt-4 flex items-center justify-between">
            <button type="button" wire:click="cancel"
                class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold">
                Batal & Kembali Login
            </button>

            <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold"
                wire:loading.attr="disabled" wire:target="save">
                Simpan Password Baru
            </button>
        </div>

    </form>
</x-ui.sccr-card>
