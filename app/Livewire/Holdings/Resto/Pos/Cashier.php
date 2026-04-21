<?php

namespace App\Livewire\Holdings\Resto\Pos;

use App\Models\Holdings\Resto\Pos\Rst_Order;
use App\Models\Holdings\Resto\Pos\Rst_OrderItem;
use App\Models\Holdings\Resto\Pos\Rst_Payment;
use Livewire\Component;
use Livewire\WithPagination;

class Cashier extends Component
{
    use WithPagination;

    public array $breadcrumbs = [];

    public string $search = '';

    public ?int $tableFilter = null;

    public string $statusFilter = 'unpaid';

    public bool $toastShow = false;

    public string $toastType = '';

    public string $toastMessage = '';

    public bool $isPolling = false;

    public bool $showOrderModal = false;

    public ?int $selectedOrderId = null;

    public bool $showPaymentModal = false;

    public bool $showReceiptModal = false;

    public ?int $receiptOrderId = null;

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Kasir', 'color' => 'text-gray-900 font-semibold'],
        ];
    }

    public function render()
    {
        $orders = Rst_Order::query()
            ->with(['items.menu'])
            ->when($this->statusFilter === 'unpaid', fn ($q) => $q->where('payment_status', '!=', 'paid'))
            ->when($this->statusFilter === 'paid', fn ($q) => $q->where('payment_status', 'paid'))
            ->when($this->tableFilter, fn ($q) => $q->where('table_number', $this->tableFilter))
            ->when($this->search, function ($q) {
                $q->where('order_number', 'like', '%'.$this->search.'%')
                    ->orWhere('customer_name', 'like', '%'.$this->search.'%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $tables = Rst_Order::query()
            ->when($this->statusFilter === 'unpaid', fn ($q) => $q->where('payment_status', '!=', 'paid'))
            ->orderBy('table_number')
            ->get(['id', 'table_number'])
            ->unique('table_number')
            ->values();

        return view('livewire.holdings.resto.pos.cashier', [
            'orders' => $orders,
            'tables' => $tables,
        ])->layout('components.sccr-layout');
    }

    public function getOrderTotals(int $orderId): array
    {
        $items = Rst_OrderItem::where('order_id', $orderId)->where('status', '!=', 'reject')->get();
        $subtotal = $items->sum(fn ($item) => $item->quantity * $item->unit_price);
        $tax = $subtotal * 0.10;
        $service = $subtotal * 0.05;
        $total = $subtotal + $tax + $service;

        return [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'service' => $service,
            'total' => $total,
        ];
    }

    public function openOrderDetail(int $orderId): void
    {
        $this->selectedOrderId = $orderId;
        $this->showOrderModal = true;
    }

    public function openPaymentModal(): void
    {
        $this->showOrderModal = false;
        $this->showPaymentModal = true;
    }

    public function processPayment(): void
    {
        if (! $this->selectedOrderId) {
            return;
        }

        $order = Rst_Order::findOrFail($this->selectedOrderId);
        $totals = $this->getOrderTotals($this->selectedOrderId);

        Rst_Payment::create([
            'order_id' => $this->selectedOrderId,
            'subtotal' => $totals['subtotal'],
            'tax_amount' => $totals['tax'],
            'service_amount' => $totals['service'],
            'total_amount' => $totals['total'],
            'payment_method' => 'QRIS',
            'paid_at' => now(),
        ]);

        $order->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);

        $this->showPaymentModal = false;
        $this->toastShow = true;
        $this->toastType = 'success';
        $this->toastMessage = 'Pembayaran berhasil';
    }

    public function openReceipt(int $orderId): void
    {
        $this->receiptOrderId = $orderId;
        $this->showReceiptModal = true;
    }

    public function printReceipt(): void
    {
        $this->showReceiptModal = false;
        $this->dispatch('print-receipt');
    }

    public function getReceiptData(int $orderId): array
    {
        $order = Rst_Order::with(['items.menu'])->findOrFail($orderId);
        $totals = $this->getOrderTotals($orderId);
        $payment = Rst_Payment::where('order_id', $orderId)->first();

        return [
            'order' => $order,
            'totals' => $totals,
            'payment' => $payment,
        ];
    }

    public function poll(): void
    {
        $this->isPolling = true;
        $this->render();
        $this->isPolling = false;
    }

    public function setFilter(string $filter): void
    {
        $this->statusFilter = $filter;
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTableFilter(): void
    {
        $this->resetPage();
    }
}
