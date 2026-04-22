<div class="p-6">
    <h2 class="text-xl font-bold mb-4">Create Production Order</h2>

    <form wire:submit.prevent="store" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Recipe <span class="text-red-500">*</span></label>
                <select wire:model.live="recipe_id"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">-- Select Recipe --</option>
                    @foreach ($recipes as $recipe)
                        <option value="{{ $recipe['value'] }}">{{ $recipe['label'] }}</option>
                    @endforeach
                </select>
                @error('recipe_id')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Recipe version <span class="text-red-500">*</span></label>
                <select wire:model.defer="recipe_version_id"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">-- Select version --</option>
                    @foreach ($versions as $version)
                        <option value="{{ $version['value'] }}">{{ $version['label'] }}</option>
                    @endforeach
                </select>
                @error('recipe_version_id')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Issue Location <span class="text-red-500">*</span></label>
                <select wire:model.defer="issue_location_id"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">-- Select Location --</option>
                    @foreach ($locations as $loc)
                        <option value="{{ $loc['value'] }}">{{ $loc['label'] }}</option>
                    @endforeach
                </select>
                @error('issue_location_id')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Output Location <span class="text-red-500">*</span></label>
                <select wire:model.defer="output_location_id"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">-- Select Location --</option>
                    @foreach ($locations as $loc)
                        <option value="{{ $loc['value'] }}">{{ $loc['label'] }}</option>
                    @endforeach
                </select>
                @error('output_location_id')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Planned Output Qty <span class="text-red-500">*</span></label>
                <input type="number" step="0.000001" min="0" wire:model.defer="planned_output_qty"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="0">
                @error('planned_output_qty')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Unit Output</label>
                <select wire:model.defer="output_uom_id"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">-- Select Unit --</option>
                    @foreach ($uoms as $uom)
                        <option value="{{ $uom['value'] }}" {{ $uom['value'] == $output_uom_id ? 'selected' : '' }}>{{ $uom['label'] }}</option>
                    @endforeach
                </select>
                @error('output_uom_id')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Production Type</label>
                <select wire:model.defer="prod_type"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="standard">Standard</option>
                    <option value="Batch">Batch</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Business Date <span class="text-red-500">*</span></label>
                <input type="date" wire:model.defer="business_date"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('business_date')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea wire:model.defer="notes"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                rows="2" placeholder="Optional"></textarea>
        </div>

        <div class="flex gap-3 pt-4">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Create Production Order
            </button>
            <a href="{{ route('dashboard.resto.recipe.production') }}"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                Cancel
            </a>
        </div>
    </form>

    @if ($toast['show'])
        <div class="mt-4 p-3 rounded {{ $toast['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
            {{ $toast['message'] }}
        </div>
    @endif
</div>
