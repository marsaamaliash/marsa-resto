<x-ui.sccr-card>
    <h2 class="text-xl font-bold mb-4">Pending Approvals</h2>

    @foreach ($approvals as $item)
        <div class="flex justify-between border-b py-2">
            <div>
                <strong>{{ $item->permission_code }}</strong>
                <div class="text-xs text-gray-500">
                    {{ json_encode($item->payload) }}
                </div>
            </div>

            <div class="flex gap-2">
                <x-ui.sccr-button wire:click="approve({{ $item->id }})" variant="success">
                    Approve
                </x-ui.sccr-button>

                <x-ui.sccr-button wire:click="reject({{ $item->id }})" variant="danger">
                    Reject
                </x-ui.sccr-button>
            </div>
        </div>
    @endforeach
</x-ui.sccr-card>
