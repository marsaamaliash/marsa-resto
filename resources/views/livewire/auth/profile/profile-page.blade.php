<div class="h-full min-h-0">
    {{-- Header --}}
    <div class="relative px-8 py-6 bg-slate-800/90 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">Profile</h1>
                <p class="text-slate-200 text-sm">Ubah email dan ganti password secara mandiri.</p>
            </div>
        </div>

        <div class="mt-4 flex justify-between items-center text-sm">
            <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-6 lg:px-8 py-8 space-y-6">

        {{-- CARD: Profile --}}
        <x-ui.sccr-card class="p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Account</div>
                    <div class="text-xl font-extrabold text-gray-800 mt-1">Informasi Dasar</div>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-semibold text-gray-700">Username</label>
                    <input value="{{ $username }}" disabled
                        class="w-full border-gray-200 bg-gray-50 rounded-lg text-sm mt-1" />
                    <div class="text-[11px] text-gray-500 mt-1">Username mengikuti sistem (tidak bisa diubah di sini).
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold text-gray-700">Email</label>
                    <input type="email" wire:model.defer="email"
                        class="w-full border-gray-300 rounded-lg text-sm mt-1" placeholder="contoh: nama@sccr.id" />
                    @error('email')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mt-5 flex justify-end">
                <x-ui.sccr-button type="button" variant="success" wire:click="saveProfile">
                    Simpan Profile
                </x-ui.sccr-button>
            </div>
        </x-ui.sccr-card>

        {{-- Anchor security --}}
        <div id="security"></div>

        {{-- CARD: Change Password --}}
        <x-ui.sccr-card class="p-6">
            <div class="space-y-4" x-data="{ showOld: false, showNew: false, showConfirm: false }">
                <div>
                    <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Security</div>
                    <div class="text-xl font-extrabold text-gray-800 mt-1">Ganti Password</div>
                    <div class="text-sm text-gray-600 mt-1">
                        Wajib isi password lama. Password baru tidak boleh mengandung <b>password123</b>.
                    </div>
                </div>

                {{-- pakai form biar enter = submit, dan aksi simpan lebih rapi --}}
                <form wire:submit.prevent="changePassword" class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    {{-- OLD --}}
                    <div class="md:col-span-2">
                        <label class="text-sm font-semibold text-gray-700">Password Lama</label>
                        <div class="flex gap-2 items-center mt-1">
                            <input :type="showOld ? 'text' : 'password'" wire:model.defer="current_password"
                                class="w-full border-gray-300 rounded-lg text-sm" />
                            <button type="button" class="text-xs px-3 h-9 rounded-lg border" @click="showOld=!showOld">
                                <span x-show="!showOld">Lihat</span>
                                <span x-show="showOld" x-cloak>Sembunyi</span>
                            </button>
                        </div>
                        @error('current_password')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- NEW --}}
                    <div>
                        <label class="text-sm font-semibold text-gray-700">Password Baru</label>
                        <div class="flex gap-2 items-center mt-1">
                            <input :type="showNew ? 'text' : 'password'" wire:model.defer="password"
                                class="w-full border-gray-300 rounded-lg text-sm" />
                            <button type="button" class="text-xs px-3 h-9 rounded-lg border" @click="showNew=!showNew">
                                <span x-show="!showNew">Lihat</span>
                                <span x-show="showNew" x-cloak>Sembunyi</span>
                            </button>
                        </div>
                        @error('password')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- CONFIRM --}}
                    <div>
                        <label class="text-sm font-semibold text-gray-700">Konfirmasi Password Baru</label>
                        <div class="flex gap-2 items-center mt-1">
                            <input :type="showConfirm ? 'text' : 'password'" wire:model.defer="password_confirmation"
                                class="w-full border-gray-300 rounded-lg text-sm" />
                            <button type="button" class="text-xs px-3 h-9 rounded-lg border"
                                @click="showConfirm=!showConfirm">
                                <span x-show="!showConfirm">Lihat</span>
                                <span x-show="showConfirm" x-cloak>Sembunyi</span>
                            </button>
                        </div>
                        @error('password_confirmation')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- ACTIONS --}}
                    <div class="md:col-span-2 flex justify-end gap-3 pt-2">
                        {{-- ✅ pakai button native agar wire:click pasti jalan --}}
                        <button type="button" wire:click="cancelPassword"
                            class="h-10 px-4 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-100">
                            Batal
                        </button>

                        <button type="submit"
                            class="h-10 px-4 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">
                            Simpan Password Baru
                        </button>
                    </div>
                </form>
            </div>
        </x-ui.sccr-card>
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('profile-scroll-top', () => {
                    // buang hash #security (kalau ada)
                    if (window.location.hash) {
                        history.replaceState(null, '', window.location.pathname + window.location.search);
                    }
                    // scroll ke atas
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                });
            });
        </script>
        <x-ui.sccr-toast :show="$toast['show']" :type="$toast['type']" :message="$toast['message']" wire:key="toast-{{ microtime() }}" />
    </div>
</div>
