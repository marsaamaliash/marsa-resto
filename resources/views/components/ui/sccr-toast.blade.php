@props([
    'show' => false,
    'type' => 'success', // success, error, info, warning
    'message' => 'Berhasil!',
    'duration' => 3000,
])

<div data-toast-show="{{ $show ? '1' : '0' }}" data-toast-type="{{ $type }}"
    data-toast-message="{{ $message }}" x-data="{
        visible: false,
        timer: null,
        observer: null,
        toastType: 'success',
        toastMessage: '',
        lastFingerprint: '',
    
        readDataset() {
            return {
                show: this.$el.dataset.toastShow === '1',
                type: this.$el.dataset.toastType || 'success',
                message: this.$el.dataset.toastMessage || ''
            };
        },
    
        start(type, message, duration) {
            this.toastType = type || 'success';
            this.toastMessage = message || '';
            this.visible = true;
    
            if (this.timer) {
                clearTimeout(this.timer);
            }
    
            this.timer = setTimeout(() => {
                this.visible = false;
                this.lastFingerprint = '';
    
                try {
                    if (typeof $wire !== 'undefined' && $wire && typeof $wire.set === 'function') {
                        $wire.set('toast.show', false);
                    }
                } catch (e) {}
            }, duration);
        },
    
        syncToast() {
            const data = this.readDataset();
    
            if (!data.show) {
                return;
            }
    
            const fingerprint = [data.type, data.message].join('|');
    
            if (fingerprint === this.lastFingerprint && this.visible) {
                return;
            }
    
            this.lastFingerprint = fingerprint;
            this.start(data.type, data.message, {{ (int) $duration }});
        }
    }" x-init="syncToast();
    
    observer = new MutationObserver(() => {
        syncToast();
    });
    
    observer.observe($el, {
        attributes: true,
        attributeFilter: ['data-toast-show', 'data-toast-type', 'data-toast-message']
    });" x-show="visible" x-cloak
    x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-90"
    x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90"
    :class="{
        'bg-green-600 text-white': toastType === 'success',
        'bg-red-600 text-white': toastType === 'error',
        'bg-blue-600 text-white': toastType === 'info',
        'bg-yellow-500 text-black': toastType === 'warning'
    }"
    class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-[9999] px-6 py-4 rounded-xl shadow-2xl flex items-center gap-3 min-w-[300px] justify-center">
    <span x-show="toastType === 'success'" class="text-xl">✅</span>
    <span x-show="toastType === 'error'" class="text-xl">⛔</span>
    <span x-show="toastType === 'info'" class="text-xl">ℹ️</span>
    <span x-show="toastType === 'warning'" class="text-xl">⚠️</span>

    <span class="font-bold text-center" x-text="toastMessage"></span>
</div>
