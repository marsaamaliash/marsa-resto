<div x-data="{
    cart: {},
    employee: null,
    allowanceRemaining: 0,
    showNoteModal: false,
    noteItemId: null,
    noteText: '',
    toastShow: false,
    toastType: 'success',
    toastMessage: '',
    showPaymentModal: false,
    paymentChoice: 'qris',
    addToCart(id, name, price) {
        if (!this.employee) {
            this.showToast('error', 'Scan QR code karyawan terlebih dahulu');
            return;
        }
        if (this.cart[id]) {
            this.cart[id].qty++;
        } else {
            this.cart[id] = { id: id, name: name, price: parseFloat(price), qty: 1, note: '' };
        }
        this.syncCart();
    },
    removeFromCart(id) {
        if (this.cart[id]) {
            if (this.cart[id].qty > 1) {
                this.cart[id].qty--;
            } else {
                delete this.cart[id];
            }
            this.syncCart();
        }
    },
    deleteFromCart(id) {
        delete this.cart[id];
        this.syncCart();
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
    syncCart() {
        this.cart = {...this.cart};
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
    get excessAmount() {
        if (!this.employee) return 0;
        return Math.max(0, this.cartTotal - this.allowanceRemaining);
    },
    get allowanceUsed() {
        if (!this.employee) return 0;
        return Math.min(this.cartTotal, this.allowanceRemaining);
    },
    formatRupiah(val) {
        return new Intl.NumberFormat('id-ID').format(val);
    },
    showToast(type, message) {
        this.toastType = type;
        this.toastMessage = message;
        this.toastShow = true;
        setTimeout(() => { this.toastShow = false; }, type === 'success' ? 3000 : 4000);
    },
    confirmOrder() {
        if (this.cartCount === 0) {
            this.showToast('error', 'Select Menu terlebih dahulu');
            return;
        }
        if (!this.employee) {
            this.showToast('error', 'Scan QR code karyawan terlebih dahulu');
            return;
        }
        if (this.excessAmount > 0) {
            this.showPaymentModal = true;
            return;
        }
        $wire.checkout(this.cartItems, 'allowance');
    },
    setPaymentAndPay() {
        $wire.checkout(this.cartItems, this.paymentChoice);
    }
}" x-on:employee-search-result.window="
    if ($event.Detail.found) {
        employee = $event.Detail.employee;
        allowanceRemaining = $event.Detail.allowanceRemaining;
        showToast('success', $event.Detail.message);
    } else {
        employee = null;
        allowanceRemaining = 0;
        showToast('error', $event.Detail.message);
    }
" x-on:transaction-complete.window="
    if ($event.Detail.success) {
        employee = null;
        allowanceRemaining = 0;
        cart = {};
        showPaymentModal = false;
        $wire.set('employeeNumber', '');
        showToast('success', $event.Detail.message);
    } else {
        showToast('error', $event.Detail.message);
    }
">
    <div class="relative px-8 py-6 bg-yellow-500/60 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold mb-2">Makan Siang Karyawan</h1>
                <p class="text-lg text-gray-800">Select Menu untuk jatah harian makan siang</p>
            </div>
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
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search Menu..."
                            class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 bg-white/80 backdrop-blur-sm">
                    </div>
                    <select wire:model.live="categoryFilter"
                        class="px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 bg-white/80 backdrop-blur-sm">
                        <option value="">All Category</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                @if ($Menus->isEmpty())
                    <div class="text-center py-16 bg-white/60 backdrop-blur-sm rounded-2xl shadow">
                        <svg class="mx-auto w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-gray-500 text-lg">Not Yet Menu Available</p>
                    </div>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                        @foreach ($Menus as $Menu)
                            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden group">
                                <div class="aspect-square bg-gray-100 relative overflow-hidden">
                                    @if ($Menu->image)
                                        <img src="{{ asset('storage/' . $Menu->image) }}" alt="{{ $Menu->name }}"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-300">
                                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    @endif
                                    @if ($Menu->discount > 0)
                                        <span class="absolute top-2 right-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-lg">
                                            -{{ number_format($Menu->discount, 0) }}%
                                        </span>
                                    @endif
                                </div>
                                <div class="p-3">
                                    @if ($Menu->category)
                                        <span class="text-xs text-yellow-700 bg-yellow-100 px-2 py-0.5 rounded-full font-medium">{{ $Menu->category }}</span>
                                    @endif
                                    <h3 class="font-semibold text-gray-800 mt-1 text-sm leading-tight truncate">{{ $Menu->name }}</h3>
                                    <p class="text-yellow-600 font-bold text-sm mt-1">Rp {{ number_format($Menu->price, 0, ',', '.') }}</p>

                                    <template x-if="cart[{{ $Menu->id }}]">
                                        <div class="mt-2 flex items-center justify-between bg-yellow-50 rounded-xl px-2 py-1">
                                            <button type="button" @click="removeFromCart({{ $Menu->id }})"
                                                class="w-7 h-7 rounded-lg bg-red-100 hover:bg-red-200 text-red-600 flex items-center justify-center font-bold transition">
                                                -
                                            </button>
                                            <span class="font-semibold text-gray-800 text-sm" x-text="cart[{{ $Menu->id }}]?.qty"></span>
                                            <button type="button" @click="addToCart({{ $Menu->id }}, '{{ addslashes($Menu->name) }}', '{{ $Menu->price }}')"
                                                class="w-7 h-7 rounded-lg bg-green-100 hover:bg-green-200 text-green-600 flex items-center justify-center font-bold transition">
                                                +
                                            </button>
                                        </div>
                                    </template>
                                    <template x-if="!cart[{{ $Menu->id }}]">
                                        <button type="button" @click="addToCart({{ $Menu->id }}, '{{ addslashes($Menu->name) }}', '{{ $Menu->price }}')"
                                            class="mt-2 w-full py-1.5 bg-yellow-500 hover:bg-yellow-600 text-white rounded-xl text-sm font-semibold transition-colors duration-200">
                                            + Add
                                        </button>
                                    </template>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6">
                        {{ $Menus->links() }}
                    </div>
                @endif
            </div>

            <div class="w-full lg:w-80 xl:w-96">
                <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg p-5 sticky top-24">
                    <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                        </svg>
                        Pesanan
                        <template x-if="cartCount > 0">
                            <span class="bg-yellow-500 text-white text-xs font-bold px-2 py-0.5 rounded-full" x-text="cartCount"></span>
                        </template>
                    </h2>

                    <div class="space-y-3 mb-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Scan QR Code / Nomor Induk Karyawan</label>
                            <div class="flex gap-2">
                                <input wire:model="employeeNumber" type="text"
                                    placeholder="e.g. EMP001"
                                    class="flex-1 px-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400">
                                <button type="button" wire:click="searchEmployee"
                                    class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg text-sm font-medium transition-colors">
                                    Search
                                </button>
                            </div>
                        </div>

                        <template x-if="employee">
                            <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-4 border border-green-200">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="w-12 h-12 rounded-full bg-green-500 text-white flex items-center justify-center font-bold text-lg">
                                        <span x-text="employee.name.charAt(0)"></span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-bold text-gray-800 truncate" x-text="employee.name"></p>
                                        <p class="text-xs text-gray-500 truncate" x-text="(employee.department || '-') + ' / ' + (employee.position || '-')"></p>
                                    </div>
                                </div>
                                <div class="space-y-1 text-xs">
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Jatah Harian</span>
                                        <span class="font-bold text-green-700" x-text="'Rp ' + formatRupiah(employee.daily_allowance)"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Terpakai Hari Ini</span>
                                        <span class="font-bold text-orange-600" x-text="'Rp ' + formatRupiah(employee.daily_allowance - allowanceRemaining)"></span>
                                    </div>
                                    <div class="flex justify-between border-t border-green-200 pt-1 mt-1">
                                        <span class="text-gray-600 font-medium">Sisa Jatah</span>
                                        <span class="font-bold text-green-600" x-text="'Rp ' + formatRupiah(allowanceRemaining)"></span>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template x-if="!employee">
                            <div class="bg-gray-50 rounded-xl p-4 text-center text-gray-400 text-sm">
                                <svg class="mx-auto w-8 h-8 mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                </svg>
                                Scan QR code atau masukkan nomor induk karyawan
                            </div>
                        </template>
                    </div>

                    <template x-if="cartCount === 0">
                        <div class="text-center py-8 text-gray-400">
                            <svg class="mx-auto w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                            </svg>
                            <p class="text-sm">Not Yet item</p>
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
                                                <p class="text-xs text-yellow-600 italic mt-1" x-text="'Notes: ' + item.note"></p>
                                            </template>
                                        </div>
                                        <div class="flex items-center gap-1 ml-2">
                                            <button type="button" @click="openNoteModal(item.id)" class="text-yellow-500 hover:text-yellow-600" title="Add Notes">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>
                                            <span class="text-sm font-semibold text-gray-800">Rp <span x-text="formatRupiah(item.price * item.qty)"></span></span>
                                            <button type="button" @click="deleteFromCart(item.id)" class="text-red-400 hover:text-red-600 ml-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <template x-if="employee && excessAmount > 0">
                                <div class="mt-3 p-3 bg-orange-50 border border-orange-200 rounded-xl">
                                    <div class="flex justify-between text-xs mb-1">
                                        <span class="text-gray-600">Menggunakan jatah</span>
                                        <span class="font-medium text-gray-700" x-text="'Rp ' + formatRupiah(allowanceUsed)"></span>
                                    </div>
                                    <div class="flex justify-between text-xs">
                                        <span class="text-gray-600">Kelebihan</span>
                                        <span class="font-bold text-orange-600" x-text="'Rp ' + formatRupiah(excessAmount)"></span>
                                    </div>
                                </div>
                            </template>

                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <div class="flex justify-between items-center mb-4">
                                    <span class="font-semibold text-gray-700">Total</span>
                                    <span class="text-lg font-bold text-yellow-600">Rp <span x-text="formatRupiah(cartTotal)"></span></span>
                                </div>
                                <template x-if="employee && excessAmount > 0">
                                    <div class="mb-2 p-2 bg-orange-100 rounded-lg text-center text-xs text-orange-700">
                                        Total melebihi jatah harian. Select metode pembayaran.
                                    </div>
                                </template>
                                <button type="button" @click="confirmOrder()"
                                    :disabled="!employee || cartCount === 0"
                                    class="w-full py-3 bg-orange-500 hover:bg-orange-600 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-semibold rounded-xl transition-colors">
                                    Bayar
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

        </div>
    </div>

    <div x-show="showPaymentModal" x-transition.opacity.duration.300ms
        class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="absolute inset-0 bg-black/50" @click="showPaymentModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Select Metode Pembayaran</h3>

            <template x-if="employee">
                <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-4 mb-4 border border-green-200">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600">Nama</span>
                        <span class="font-medium text-gray-800" x-text="employee.name"></span>
                    </div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600">Jatah Digunakan</span>
                        <span class="font-medium text-green-700" x-text="'Rp ' + formatRupiah(allowanceUsed)"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Kelebihan</span>
                        <span class="font-bold text-orange-600" x-text="'Rp ' + formatRupiah(excessAmount)"></span>
                    </div>
                </div>
            </template>

            <div class="space-y-3 mb-6">
                <label class="flex items-center gap-3 p-4 border-2 rounded-xl cursor-pointer transition-all"
                    :class="paymentChoice === 'qris' ? 'border-yellow-400 bg-yellow-50' : 'border-gray-200 hover:border-gray-300'">
                    <input type="radio" x-model="paymentChoice" value="qris" class="hidden">
                    <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-gray-800">QRIS</p>
                        <p class="text-xs text-gray-500">Bayar kelebihan via QR code</p>
                    </div>
                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center"
                        :class="paymentChoice === 'qris' ? 'border-yellow-400' : 'border-gray-300'">
                        <template x-if="paymentChoice === 'qris'">
                            <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                        </template>
                    </div>
                </label>

                <label class="flex items-center gap-3 p-4 border-2 rounded-xl cursor-pointer transition-all"
                    :class="paymentChoice === 'salary' ? 'border-yellow-400 bg-yellow-50' : 'border-gray-200 hover:border-gray-300'">
                    <input type="radio" x-model="paymentChoice" value="salary" class="hidden">
                    <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-gray-800">Potong Gaji</p>
                        <p class="text-xs text-gray-500">Kelebihan dipotong of gaji bulan ini</p>
                    </div>
                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center"
                        :class="paymentChoice === 'salary' ? 'border-yellow-400' : 'border-gray-300'">
                        <template x-if="paymentChoice === 'salary'">
                            <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                        </template>
                    </div>
                </label>
            </div>

            <div class="flex gap-3">
                <button type="button" @click="showPaymentModal = false"
                    class="flex-1 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-xl transition-colors">
                    Cancel
                </button>
                <button type="button" @click="setPaymentAndPay()"
                    class="flex-1 py-3 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-xl transition-colors">
                    Bayar
                </button>
            </div>
        </div>
    </div>

    <div x-show="showNoteModal" x-transition.opacity.duration.300ms
        class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="absolute inset-0 bg-black/50" @click="showNoteModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-2">Notes Menu</h3>
            <p class="text-sm text-gray-500 mb-4" x-text="cart[noteItemId]?.name"></p>
            <textarea x-model="noteText" rows="3" placeholder="Tambahkan Notes khusus untuk Menu ini..."
                class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 mb-4"></textarea>
            <div class="flex gap-3">
                <button type="button" @click="showNoteModal = false"
                    class="flex-1 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-xl transition-colors">
                    Cancel
                </button>
                <button type="button" @click="saveNote()"
                    class="flex-1 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-medium rounded-xl transition-colors">
                    Save
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
            <button type="button" @click="toastShow = false" class="text-white/80 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
</div>
