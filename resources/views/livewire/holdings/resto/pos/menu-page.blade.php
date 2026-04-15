<div x-data="{
    cart: {},
    customerName: @js($editOrder?->customer_name ?? ''),
    tableNumber: @js($editOrder?->table_number ?? ''),
    isEditMode: @js((bool) $editOrderId),
    showConfirmModal: false,
    showNoteModal: false,
    noteItemId: null,
    noteText: '',
    toastShow: false,
    toastType: 'success',
    toastMessage: '',
    addToCart(id, name, price) {
        if (this.cart[id]) {
            this.cart[id].qty++;
        } else {
            this.cart[id] = { id, name, price: parseFloat(price), qty: 1, note: '' };
        }
    },
    get validationErrors() {
        const errors = [];
        if (!this.tableNumber.trim()) errors.push('Nomor Meja');
        if (!this.customerName.trim()) errors.push('Nama Pelanggan');
        return errors;
    },
    removeFromCart(id) {
        if (this.cart[id]) {
            if (this.cart[id].qty > 1) {
                this.cart[id].qty--;
            } else {
                delete this.cart[id];
            }
        }
    },
    deleteFromCart(id) {
        delete this.cart[id];
    },
    openNoteModal(id) {
        this.noteItemId = id;
        this.noteText = this.cart[id]?.note || '';
        this.showNoteModal = true;
    },
    saveNote() {
        if (this.noteItemId && this.cart[this.noteItemId]) {
            this.cart[this.noteItemId].note = this.noteText;
        }
        this.showNoteModal = false;
        this.noteItemId = null;
        this.noteText = '';
    },
    get cartItems() {
        return Object.values(this.cart);
    },
    get cartCount() {
        return this.cartItems.reduce((sum, item) => sum + item.qty, 0);
    },
    get cartTotal() {
        return this.cartItems.reduce((sum, item) => sum + (item.price * item.qty), 0);
    },
    formatRupiah(val) {
        return new Intl.NumberFormat('id-ID').format(val);
    },
    confirmOrder() {
        if (this.cartCount === 0) return;
        if (!this.isEditMode) {
            if (!this.tableNumber.trim()) {
                this.toastType = 'error';
                this.toastMessage = 'Nomor Meja wajib diisi';
                this.toastShow = true;
                setTimeout(() => this.toastShow = false, 3000);
                return;
            }
            if (!this.customerName.trim()) {
                this.toastType = 'error';
                this.toastMessage = 'Nama Pelanggan wajib diisi';
                this.toastShow = true;
                setTimeout(() => this.toastShow = false, 3000); 
                return;
            }
        }
        this.showConfirmModal = true;
    },
    submitOrder() {
        $wire.submitOrder(this.cartItems, this.customerName, this.tableNumber);
        this.showConfirmModal = false;
        this.cart = {};
        this.customerName = '';
        this.tableNumber = '';
        this.toastType = 'success';
        this.toastMessage = 'Order berhasil dikirim ke dapur';
        this.toastShow = true;
        setTimeout(() => this.toastShow = false, 3000);
    }
}">
    <div class="relative px-8 py-6 bg-yellow-500/60 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold mb-2">
                    @if ($editOrderId)
                        Tambah ke Order {{ $editOrder->order_number }}
                    @else
                        Daftar Menu
                    @endif
                </h1>
                <p class="text-lg text-gray-800">
                    @if ($editOrderId)
                        Menambahkan item ke order
                    @else
                        Pilih menu untuk menambahkan order pelanggan
                    @endif
                </p>
            </div>
            @if ($editOrderId)
                <a href="{{ route('dashboard.resto.orders') }}" class="px-4 py-2 bg-white/50 hover:bg-white/70 text-gray-800 rounded-xl font-medium transition">
                    ← Kembali
                </a>
            @endif
        </div>
        <div class="mt-4 flex justify-between items-center text-sm">
            <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
        </div>
    </div>

    <div class="fixed top-0 left-0 w-full h-full z-0 pointer-events-none">
        <img src="{{ asset('images/bg-gedung.jpg') }}" alt="Background" class="w-full h-full object-cover opacity-30">
    </div>

    <div class="max-w-7xl mx-auto px-4 lg:px-8 py-8">
        <div class="flex flex-col lg:flex-row gap-6">

            <div class="flex-1">
                <div class="flex flex-col sm:flex-row gap-3 mb-6">
                    <div class="relative flex-1">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari menu..."
                            class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 bg-white/80 backdrop-blur-sm">
                    </div>
                    <select wire:model.live="categoryFilter"
                        class="px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 bg-white/80 backdrop-blur-sm">
                        <option value="">Semua Kategori</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                @if ($menus->isEmpty())
                    <div class="text-center py-16 bg-white/60 backdrop-blur-sm rounded-2xl shadow">
                        <svg class="mx-auto w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-gray-500 text-lg">Belum ada menu tersedia</p>
                    </div>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                        @foreach ($menus as $menu)
                            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden group">
                                <div class="aspect-square bg-gray-100 relative overflow-hidden">
                                    @if ($menu->image)
                                        <img src="{{ asset('storage/' . $menu->image) }}" alt="{{ $menu->name }}"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-300">
                                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    @endif
                                    @if ($menu->discount > 0)
                                        <span class="absolute top-2 right-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-lg">
                                            -{{ number_format($menu->discount, 0) }}%
                                        </span>
                                    @endif
                                </div>
                                <div class="p-3">
                                    @if ($menu->category)
                                        <span class="text-xs text-yellow-700 bg-yellow-100 px-2 py-0.5 rounded-full font-medium">{{ $menu->category }}</span>
                                    @endif
                                    <h3 class="font-semibold text-gray-800 mt-1 text-sm leading-tight truncate">{{ $menu->name }}</h3>
                                    <p class="text-yellow-600 font-bold text-sm mt-1">Rp {{ number_format($menu->price, 0, ',', '.') }}</p>

                                    <template x-if="cart[{{ $menu->id }}]">
                                        <div class="mt-2 flex items-center justify-between bg-yellow-50 rounded-xl px-2 py-1">
                                            <button @click="removeFromCart({{ $menu->id }})"
                                                class="w-7 h-7 rounded-lg bg-red-100 hover:bg-red-200 text-red-600 flex items-center justify-center font-bold transition">
                                                -
                                            </button>
                                            <span class="font-semibold text-gray-800 text-sm" x-text="cart[{{ $menu->id }}]?.qty"></span>
                                            <button @click="addToCart({{ $menu->id }}, '{{ addslashes($menu->name) }}', '{{ $menu->price }}')"
                                                class="w-7 h-7 rounded-lg bg-green-100 hover:bg-green-200 text-green-600 flex items-center justify-center font-bold transition">
                                                +
                                            </button>
                                        </div>
                                    </template>
                                    <template x-if="!cart[{{ $menu->id }}]">
                                        <button @click="addToCart({{ $menu->id }}, '{{ addslashes($menu->name) }}', '{{ $menu->price }}')"
                                            class="mt-2 w-full py-1.5 bg-yellow-500 hover:bg-yellow-600 text-white rounded-xl text-sm font-semibold transition-colors duration-200">
                                            + Tambah
                                        </button>
                                    </template>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6">
                        {{ $menus->links() }}
                    </div>
                @endif
            </div>

            <div class="w-full lg:w-80 xl:w-96">
                <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg p-5 sticky top-24">
                    <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                        </svg>
                        @if ($editOrderId)
                            Tambah Order
                        @else
                            Order
                        @endif
                        <template x-if="cartCount > 0">
                            <span class="bg-yellow-500 text-white text-xs font-bold px-2 py-0.5 rounded-full" x-text="cartCount"></span>
                        </template>
                    </h2>

                    <div class="space-y-3 mb-4">
                        @if ($editOrderId)
                            <div class="bg-yellow-50 rounded-xl p-3 mb-3">
                                <p class="text-xs font-semibold text-yellow-700">Order: {{ $editOrder->order_number }}</p>
                            </div>
                        @endif
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Nama Pelanggan</label>
                            <input x-model="customerName" type="text" placeholder="Nama pelanggan..."
                                x-bind:readonly="isEditMode"
                                class="w-full px-3 py-2 rounded-lg border text-sm"
                                :class="isEditMode ? 'border-gray-200 bg-gray-50 text-gray-500' : 'border-gray-300 focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400'">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">No. Meja</label>
                            <input x-model="tableNumber" type="number" placeholder="Nomor meja..."
                                x-bind:readonly="isEditMode"
                                class="w-full px-3 py-2 rounded-lg border text-sm"
                                :class="isEditMode ? 'border-gray-200 bg-gray-50 text-gray-500' : 'border-gray-300 focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400'">
                        </div>
                    </div>

                    <template x-if="cartCount === 0">
                        <div class="text-center py-8 text-gray-400">
                            <svg class="mx-auto w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                            </svg>
                            <p class="text-sm">Belum ada item</p>
                        </div>
                    </template>

                    <template x-if="cartCount > 0">
                        <div>
                            <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                                <template x-for="item in cartItems" :key="item.id">
                                    <div class="flex items-center justify-between bg-gray-50 rounded-xl px-3 py-2">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-800 truncate" x-text="item.name"></p>
                                            <p class="text-xs text-gray-500">
                                                <span x-text="item.qty"></span> x Rp <span x-text="formatRupiah(item.price)"></span>
                                            </p>
                                            <template x-if="item.note">
                                                <p class="text-xs text-yellow-600 italic mt-1" x-text="'Catatan: ' + item.note"></p>
                                            </template>
                                        </div>
                                        <div class="flex items-center gap-1 ml-2">
                                            <button @click="openNoteModal(item.id)" class="text-yellow-500 hover:text-yellow-600" title="Tambah catatan">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>
                                            <span class="text-sm font-semibold text-gray-800">Rp <span x-text="formatRupiah(item.price * item.qty)"></span></span>
                                            <button @click="deleteFromCart(item.id)" class="text-red-400 hover:text-red-600 ml-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <div class="flex justify-between items-center mb-4">
                                    <span class="font-semibold text-gray-700">Total</span>
                                    <span class="text-lg font-bold text-yellow-600">Rp <span x-text="formatRupiah(cartTotal)"></span></span>
                                </div>
                                <button type="button"
                                    @click="confirmOrder()"
                                    class="w-full py-3 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-xl transition-colors">
                                    @if ($editOrderId)
                                        Tambah ke Order
                                    @else
                                        Kirim ke Dapur
                                    @endif
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

        </div>
    </div>

    <div x-show="showConfirmModal" x-transition.opacity.duration.300ms
        class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="absolute inset-0 bg-black/50" @click="showConfirmModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Konfirmasi Order</h3>
            <p class="text-gray-600 mb-2">Meja: <span x-text="tableNumber || '-'" class="font-medium"></span></p>
            <p class="text-gray-600 mb-4">Pelanggan: <span x-text="customerName || '-'" class="font-medium"></span></p>
            <div class="bg-gray-50 rounded-xl p-4 max-h-48 overflow-y-auto mb-4">
<template x-for="item in cartItems" :key="item.id">
                                    <div class="py-2 border-b border-gray-200 last:border-0">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <span class="font-medium text-gray-800" x-text="item.name"></span>
                                                <span class="text-sm text-gray-500 ml-2">x<span x-text="item.qty"></span></span>
                                            </div>
                                            <span class="font-medium text-gray-800">Rp <span x-text="formatRupiah(item.price * item.qty)"></span></span>
                                        </div>
                                        <template x-if="item.note">
                                            <p class="text-xs text-yellow-600 italic mt-1" x-text="'Catatan: ' + item.note"></p>
                                        </template>
                                    </div>
                                </template>
            </div>
            <div class="flex justify-between items-center mb-6">
                <span class="font-bold text-lg text-gray-700">Total</span>
                <span class="text-xl font-bold text-yellow-600">Rp <span x-text="formatRupiah(cartTotal)"></span></span>
            </div>
            <div class="flex gap-3">
                <button type="button" @click="showConfirmModal = false"
                    class="flex-1 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-xl transition-colors">
                    Batal
                </button>
                <button type="button" @click="submitOrder()"
                    class="flex-1 py-3 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-xl transition-colors">
                    Kirim
                </button>
            </div>
        </div>
    </div>

    <div x-show="showNoteModal" x-transition.opacity.duration.300ms
        class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="absolute inset-0 bg-black/50" @click="showNoteModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-2">Catatan Menu</h3>
            <p class="text-sm text-gray-500 mb-4" x-text="cart[noteItemId]?.name"></p>
            <textarea x-model="noteText" rows="3" placeholder="Tambahkan catatan khusus untuk menu ini..."
                class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 mb-4"></textarea>
            <div class="flex gap-3">
                <button type="button" @click="showNoteModal = false"
                    class="flex-1 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-xl transition-colors">
                    Batal
                </button>
                <button type="button" @click="saveNote()"
                    class="flex-1 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-medium rounded-xl transition-colors">
                    Simpan
                </button>
            </div>
        </div>
    </div>

    <div x-show="toastShow" x-transition.opacity.duration.300ms
        class="fixed top-[80px] right-6 z-50 px-5 py-3 rounded-xl shadow-lg"
        :class="toastType === 'success' ? 'bg-green-500' : 'bg-red-500'"
        style="display: none;">
        <div class="flex items-center gap-3">
            <span class="text-white font-medium" x-text="toastMessage"></span>
            
            <button @click="toastShow = false" class="text-white/80 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
</div>
