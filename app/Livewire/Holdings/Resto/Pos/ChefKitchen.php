<?php

namespace App\Livewire\Holdings\Resto\Pos;

use App\Models\Holdings\Resto\Pos\Rst_Order;
use App\Models\Holdings\Resto\Pos\Rst_OrderItem;
use Livewire\Component;
use Livewire\WithPagination;

class ChefKitchen extends Component
{
    use WithPagination;

    public array $breadcrumbs = [];

    public string $statusFilter = 'pending';

    public bool $toastShow = false;

    public string $toastType = '';

    public string $toastMessage = '';

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Kitchen', 'color' => 'text-gray-900 font-semibold'],
        ];
    }

    public function render()
    {
        $orders = Rst_Order::with(['items.menu'])
            ->whereIn('status', ['pending', 'confirmed', 'processing'])
            ->when($this->statusFilter !== 'all', fn ($q) => $q->where('status', $this->statusFilter))
            ->orderBy('created_at', 'asc')
            ->paginate(10);

        return view('livewire.holdings.resto.pos.chef-kitchen', [
            'orders' => $orders,
        ])->layout('components.sccr-layout');
    }

    public function updateItemStatus(int $itemId, string $status): void
    {
        $item = Rst_OrderItem::findOrFail($itemId);
        $item->update(['status' => $status]);

        $order = $item->order;
        $allItems = $order->items()->get();

        $allReady = $allItems->every(fn ($i) => in_array($i->status, ['ready', 'served']));
        $anyProcessing = $allItems->some(fn ($i) => $i->status === 'processing');
        $anyPending = $allItems->some(fn ($i) => $i->status === 'pending');

        if ($allReady) {
            $order->update(['status' => 'ready']);
        } elseif ($anyProcessing) {
            $order->update(['status' => 'processing']);
        } elseif ($anyPending && ! $anyProcessing) {
            $order->update(['status' => 'confirmed']);
        }

        $this->toastShow = true;
        $this->toastType = 'success';
        $this->toastMessage = 'Status updated';
    }

    public function updateOrderStatus(int $orderId, string $status): void
    {
        $order = Rst_Order::findOrFail($orderId);
        $order->update(['status' => $status]);

        if (in_array($status, ['processing', 'ready'])) {
            $order->items()->update(['status' => $status]);
        }

        $this->toastShow = true;
        $this->toastType = 'success';
        $this->toastMessage = 'Order status updated';
    }

    public function hideToast(): void
    {
        $this->toastShow = false;
    }
}
