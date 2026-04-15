<?php

namespace App\Livewire\Holdings\Resto\Pos;

use App\Models\Holdings\Resto\Pos\Rst_FailedOrderItem;
use App\Models\Holdings\Resto\Pos\Rst_Order;
use App\Models\Holdings\Resto\Pos\Rst_OrderItem;
use Livewire\Component;
use Livewire\WithPagination;

class ChefKitchen extends Component
{
    use WithPagination;

    public array $breadcrumbs = [];

    public string $statusFilter = 'waiting';

    public string $sortField = 'order_created_at';

    public string $sortDirection = 'asc';

    public string $search = '';

    public bool $toastShow = false;

    public string $toastType = '';

    public string $toastMessage = '';

    public bool $showRejectModal = false;

    public int $rejectItemId = 0;

    public string $rejectReason = '';

    public bool $showRejectOrderModal = false;

    public int $rejectOrderId = 0;

    public bool $showFailedModal = false;

    public int $failedItemId = 0;

    public string $failedReason = '';

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
            ['label' => 'Kitchen', 'color' => 'text-gray-900 font-semibold'],
        ];
    }

    public function render()
    {
        $items = Rst_OrderItem::query()
            ->with(['menu', 'order'])
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('menus', 'order_items.menu_id', '=', 'menus.id')
            ->select('order_items.*')
            ->when($this->statusFilter !== 'all', fn ($q) => $q->where('order_items.status', $this->statusFilter))
            ->when($this->search, fn ($q) => $q->where('menus.name', 'like', '%'.$this->search.'%'));

        if ($this->sortField === 'menu_name') {
            $items->orderBy('menus.name', $this->sortDirection);
        } elseif ($this->sortField === 'table_number') {
            $items->orderBy('orders.table_number', 'asc');
        } else {
            // UBAH INI: Agar waktu yang dihitung adalah waktu spesifik item itu masuk dapur
            $items->orderBy('order_items.created_at', $this->sortDirection);
        }

        $items = $items->paginate(20);

        return view('livewire.holdings.resto.pos.chef-kitchen', [
            'items' => $items,
        ])->layout('components.sccr-layout');
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updateItemStatus(int $itemId, string $status): void
    {
        $item = Rst_OrderItem::findOrFail($itemId);
        $item->update(['status' => $status]);

        $order = $item->order;
        $allItems = $order->items()->get();

        $allReady = $allItems->every(fn ($i) => in_array($i->status, ['ready', 'deliver']));
        $allReject = $allItems->every(fn ($i) => $i->status === 'reject');
        $anyWaiting = $allItems->some(fn ($i) => $i->status === 'waiting');
        $anyReady = $allItems->some(fn ($i) => $i->status === 'ready');

        if ($allReject) {
            $order->update(['status' => 'reject']);
        } elseif ($allReady) {
            $order->update(['status' => 'ready']);
        } elseif ($anyReady) {
            $order->update(['status' => 'ready']);
        } elseif ($anyWaiting) {
            $order->update(['status' => 'waiting']);
        }

        $this->toastShow = true;
        $this->toastType = 'success';
        $this->toastMessage = 'Status updated';
    }

    public function openRejectModal(int $itemId): void
    {
        $this->rejectItemId = $itemId;
        $this->rejectReason = '';
        $this->showRejectModal = true;
    }

    public function openRejectOrderModal(int $orderId): void
    {
        $this->rejectOrderId = $orderId;
        $this->rejectReason = '';
        $this->showRejectOrderModal = true;
    }

    public function submitReject(): void
    {
        if (! $this->rejectReason) {
            $this->toastShow = true;
            $this->toastType = 'error';
            $this->toastMessage = 'Alasan penolakan wajib diisi';

            return;
        }

        $item = Rst_OrderItem::findOrFail($this->rejectItemId);

        Rst_FailedOrderItem::create([
            'original_order_item_id' => $item->id,
            'order_id' => $item->order_id,
            'menu_id' => $item->menu_id,
            'quantity' => $item->quantity,
            'unit_price' => $item->unit_price,
            'subtotal' => $item->subtotal,
            'notes' => $item->notes,
            'reject_reason' => $this->rejectReason,
        ]);

        $item->update(['status' => 'reject']);

        $this->recalculateOrderStatus($item->order);

        $this->showRejectModal = false;
        $this->toastShow = true;
        $this->toastType = 'success';
        $this->toastMessage = 'Item ditolak';
    }

    public function openFailedModal(int $itemId): void
    {
        $this->failedItemId = $itemId;
        $this->failedReason = '';
        $this->showFailedModal = true;
    }

    public function submitFailed(): void
    {
        if (! $this->failedReason) {
            $this->toastShow = true;
            $this->toastType = 'error';
            $this->toastMessage = 'Alasan gagal wajib diisi';

            return;
        }

        $item = Rst_OrderItem::findOrFail($this->failedItemId);

        Rst_FailedOrderItem::create([
            'original_order_item_id' => $item->id,
            'order_id' => $item->order_id,
            'menu_id' => $item->menu_id,
            'quantity' => $item->quantity,
            'unit_price' => $item->unit_price,
            'subtotal' => $item->subtotal,
            'notes' => $item->notes,
            'reject_reason' => $this->failedReason,
        ]);

        $this->showFailedModal = false;
        $this->toastShow = true;
        $this->toastType = 'success';
        $this->toastMessage = 'Item gagal masak disimpan';
    }

    private function recalculateOrderStatus($order): void
    {
        $allItems = $order->items()->get();

        $allReady = $allItems->every(fn ($i) => in_array($i->status, ['ready', 'deliver']));
        $allReject = $allItems->every(fn ($i) => $i->status === 'reject');
        $anyWaiting = $allItems->some(fn ($i) => $i->status === 'waiting');
        $anyReady = $allItems->some(fn ($i) => $i->status === 'ready');

        if ($allReject) {
            $order->update(['status' => 'reject']);
        } elseif ($allReady) {
            $order->update(['status' => 'ready']);
        } elseif ($anyReady) {
            $order->update(['status' => 'ready']);
        } elseif ($anyWaiting) {
            $order->update(['status' => 'waiting']);
        }
    }

    public function submitRejectOrder(): void
    {
        if (! $this->rejectReason) {
            $this->toastShow = true;
            $this->toastType = 'error';
            $this->toastMessage = 'Alasan penolakan wajib diisi';

            return;
        }

        $order = Rst_Order::findOrFail($this->rejectOrderId);

        foreach ($order->items as $item) {
            Rst_FailedOrderItem::create([
                'original_order_item_id' => $item->id,
                'order_id' => $item->order_id,
                'menu_id' => $item->menu_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'subtotal' => $item->subtotal,
                'notes' => $item->notes,
                'reject_reason' => $this->rejectReason,
            ]);
        }

        $order->update(['status' => 'reject']);

        $this->showRejectOrderModal = false;
        $this->toastShow = true;
        $this->toastType = 'success';
        $this->toastMessage = 'Order ditolak';
    }

    public function hideToast(): void
    {
        $this->toastShow = false;
    }
}
