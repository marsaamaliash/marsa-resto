<?php

namespace App\Livewire\Holdings\Resto\Pos;

use App\Models\Holdings\Resto\Pos\Rst_Order;
use App\Models\Holdings\Resto\Pos\Rst_OrderItem;
use Livewire\Component;
use Livewire\WithPagination;

class WaiterOrders extends Component
{
    use WithPagination;

    public array $breadcrumbs = [];

    public string $statusFilter = 'all';

    public array $statusFilters = ['all', 'waiting', 'ready', 'deliver', 'reject'];

    public ?int $mejaFilter = null;

    public string $search = '';

    public bool $toastShow = false;

    public string $toastType = '';

    public string $toastMessage = '';

    public bool $isPolling = false;

    public bool $showTambahModal = false;

    public bool $showEditModal = false;

    public ?int $selectTambahOrderId = null;

    public ?int $selectEditOrderId = null;

    public string $editCustomerName = '';

    public string $editTableNumber = '';

    public function setFilter(string $filter): void
    {
        $this->statusFilter = $filter;
        $this->resetPage();
    }

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Order List', 'color' => 'text-gray-900 font-semibold'],
        ];
    }

    public function render()
    {
        $items = Rst_OrderItem::query()
            ->with(['menu', 'order'])
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('menus', 'order_items.menu_id', '=', 'menus.id')
            ->select('order_items.*')
            ->where('orders.payment_status', '!=', 'paid')
            ->when($this->mejaFilter, fn ($q) => $q->where('orders.id', $this->mejaFilter))
            ->when($this->statusFilter !== 'all', fn ($q) => $q->where('order_items.status', $this->statusFilter))
            ->when($this->search, fn ($q) => $q->where('menus.name', 'like', '%'.$this->search.'%'))
            ->orderBy('orders.created_at', 'asc')
            ->orderBy('order_items.created_at', 'asc')
            ->paginate(20);

        $availableOrders = Rst_Order::where('payment_status', '!=', 'paid')
            ->orderBy('created_at', 'asc')
            ->get(['id', 'order_number', 'table_number', 'customer_name', 'created_at']);

        return view('livewire.holdings.resto.pos.waiter-orders', [
            'items' => $items,
            'availableOrders' => $availableOrders,
        ])->layout('components.sccr-layout');
    }

    public function poll(): void
    {
        $this->isPolling = true;
        $this->render();
        $this->isPolling = false;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedMejaFilter(): void
    {
        $this->resetPage();
    }

    public function openTambahModal(): void
    {
        $this->selectTambahOrderId = null;
        $this->showTambahModal = true;
    }

    public function selectTambahOrder(int $orderId): void
    {
        $this->selectTambahOrderId = $orderId;
    }

    public function submitTambahOrder(): void
    {
        if (! $this->selectTambahOrderId) {
            $this->toastShow = true;
            $this->toastType = 'error';
            $this->toastMessage = 'Pilih order terlebih dahulu';

            return;
        }

        $this->redirect(route('dashboard.resto.menu').'?order_id='.$this->selectTambahOrderId);
    }

    public function openEditModal(): void
    {
        $this->selectEditOrderId = null;
        $this->editCustomerName = '';
        $this->editTableNumber = '';
        $this->showEditModal = true;
    }

    public function selectEditOrder(int $orderId): void
    {
        $order = Rst_Order::findOrFail($orderId);
        $this->selectEditOrderId = $orderId;
        $this->editCustomerName = $order->customer_name;
        $this->editTableNumber = $order->table_number;
    }

    public function submitEditOrder(): void
    {
        if (! $this->selectEditOrderId) {
            $this->toastShow = true;
            $this->toastType = 'error';
            $this->toastMessage = 'Pilih order terlebih dahulu';

            return;
        }

        $order = Rst_Order::findOrFail($this->selectEditOrderId);

        $order->update([
            'customer_name' => $this->editCustomerName ?: $order->customer_name,
            'table_number' => $this->editTableNumber ?: $order->table_number,
        ]);

        $this->showEditModal = false;
        $this->toastShow = true;
        $this->toastType = 'success';
        $this->toastMessage = 'Data order berhasil diperbarui';
    }

    public function deliverItem(int $itemId): void
    {
        $item = Rst_OrderItem::findOrFail($itemId);
        $item->update(['status' => 'deliver']);

        $this->toastShow = true;
        $this->toastType = 'success';
        $this->toastMessage = 'Item diantar';
    }

    public function hideToast(): void
    {
        $this->toastShow = false;
    }
}
