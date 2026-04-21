<div class="p-6">
    <h2 class="text-xl font-bold mb-4">Edit Vendor</h2>

    <form wire:submit.prevent="update" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Vendor Code <span class="text-red-500">*</span></label>
                <input type="text" wire:model.defer="code"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="e.g. VND001">
                @error('code')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Vendor Name <span class="text-red-500">*</span></label>
                <input type="text" wire:model.defer="name"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="e.g. PT Maju Bersama">
                @error('name')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
            <input type="Email" wire:model.defer="Email"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="e.g. vendor@company.com">
            @error('Email')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">PIC</label>
                <input type="text" wire:model.defer="pic"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="e.g. Budi Santoso">
                @error('pic')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                <input type="text" wire:model.defer="no_telp"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="e.g. 081234567890">
                @error('no_telp')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
            <textarea wire:model.defer="address"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="e.g. Jl. Raya No. 123, Jakarta" rows="2"></textarea>
            @error('address')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea wire:model.defer="description"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="e.g. Main raw material supplier for restaurant" rows="2"></textarea>
            @error('description')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Default Terms</label>
                <select wire:model.defer="default_terms"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">-- Select Terms --</option>
                    @foreach ($termsOptions as $opt)
                        <option value="{{ $opt['value'] }}" {{ $opt['value'] === $default_terms ? 'selected' : '' }}>{{ $opt['label'] }}</option>
                    @endforeach
                </select>
                @error('default_terms')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex items-center gap-2 pt-6">
                <input type="checkbox" wire:model.defer="is_active" id="edit_is_active"
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <label for="edit_is_active" class="text-sm text-gray-700">Active</label>
            </div>
        </div>

        <div class="flex gap-3 pt-4">
            <button type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Save
            </button>
            <button type="button" wire:click="saveDraft"
                class="px-4 py-2 bg-amber-500 text-white rounded-md hover:bg-amber-600">
                Save as Draft
            </button>
            <button type="button" wire:click="cancel"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                Cancel
            </button>
        </div>
    </form>

    @if ($toast['show'])
        <div class="mt-4 p-3 rounded {{ $toast['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
            {{ $toast['message'] }}
        </div>
    @endif
</div>
