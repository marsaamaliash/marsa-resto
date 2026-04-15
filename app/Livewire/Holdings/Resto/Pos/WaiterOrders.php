<?php

namespace App\Livewire\Holdings\Resto\Pos;

use App\Models\Holdings\Resto\Pos\Rst_Order;
use Livewire\Component;
use Livewire\WithPagination;

class WaiterOrders extends Component
{
    use WithPagination;

    public array $breadcrumbs = [];

    public string $statusFilter = 'all';

    public bool $toastShow = false;

    public string $toastType = '';

    public string $toastMessage = '';

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Order Saya', 'color' => 'text-gray-900 font-semibold'],
        ];
    }

    public function render()
    {
        $orders = Rst_Order::with(['items.menu'])
            ->orderBy('created_at', 'desc')
            ->when($this->statusFilter !== 'all', fn ($q) => $q->where('status', $this->statusFilter))
            ->paginate(10);

        return view('livewire.holdings.resto.pos.waiter-orders', [
            'orders' => $orders,
        ])->layout('components.sccr-layout');
    }

    public function markDelivered(int $orderId): void
    {
        $order = Rst_Order::findOrFail($orderId);
        $order->update(['status' => 'deliver']);
        $order->items()->update(['status' => 'deliver']);

        $this->toastShow = true;
        $this->toastType = 'success';
        $this->toastMessage = 'Order ditandai sudah diantar';
    }

    public function hideToast(): void
    {
        $this->toastShow = false;
    }
}
