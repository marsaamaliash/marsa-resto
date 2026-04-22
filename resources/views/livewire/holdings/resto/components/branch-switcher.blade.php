<div class="relative inline-block" x-data="{ open: @entangle('showDropdown') }" @click.away="open = false">
    <button type="button" 
        @click="open = !open"
        class="w-full flex items-center gap-2 px-3 py-2 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 transition-colors text-left">
        <svg class="w-5 h-5 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        </svg>
        <div class="flex flex-col items-start flex-1 min-w-0">
            <span class="text-[10px] text-gray-500 uppercase font-semibold">Current Branch</span>
            <span class="text-sm font-medium text-gray-900 truncate">{{ $currentBranchName }}</span>
        </div>
        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" :class="{ 'transform rotate-180': open }">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div x-show="open" 
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute left-0 mt-1 w-full bg-white rounded-lg shadow-lg border border-gray-200 z-50"
        style="display: none;">
        
        <div class="p-3 border-b border-gray-100">
            <span class="text-xs font-semibold text-gray-500 uppercase">Select Branch</span>
        </div>

        @if(count($userBranches) > 0)
            <div class="max-h-64 overflow-y-auto py-1">
                @foreach($userBranches as $branch)
                    <button type="button"
                        wire:click="switchBranch({{ $branch['id'] }})"
                        class="w-full px-4 py-2 text-left hover:bg-gray-50 flex items-center justify-between {{ $currentBranchId === $branch['id'] ? 'bg-blue-50' : '' }}">
                        <div class="flex flex-col">
                            <span class="text-sm font-medium text-gray-900">{{ $branch['name'] }}</span>
                            <span class="text-xs text-gray-500">{{ $branch['code'] }}</span>
                        </div>
                        @if($currentBranchId === $branch['id'])
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        @endif
                    </button>
                @endforeach
            </div>
        @else
            <div class="p-4 text-center text-sm text-gray-500">
                No branches assigned
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('refresh-page', () => {
                window.location.reload();
            });
        });
    </script>
</div>
