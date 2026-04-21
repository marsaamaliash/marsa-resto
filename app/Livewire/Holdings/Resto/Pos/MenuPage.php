<?php

namespace App\Livewire\Holdings\Resto\Pos;

use App\Models\Holdings\Resto\Pos\Rst_Menu;
use App\Models\Holdings\Resto\Pos\Rst_Order;
use App\Models\Holdings\Resto\Pos\Rst_OrderItem;
use Livewire\Component;
use Livewire\WithPagination;

class MenuPage extends Component
{
    use WithPagination;

    public array $breadcrumbs = [];

    public string $search = '';

    public string $categoryFilter = '';

    protected array $cart = [];

    public string $customerName = '';

    public string $tableNumber = '';

    public bool $toastShow = false;

    public string $toastType = '';

    public string $toastMessage = '';

    public ?int $editOrderId = null;

    public ?Rst_Order $editOrder = null;

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Daftar Menu', 'color' => 'text-gray-900 font-semibold'],
        ];

        $orderId = request()->query('order_id');
        if ($orderId) {
            $this->editOrderId = (int) $orderId;
            $this->editOrder = Rst_Order::with(['items.menu'])->find($this->editOrderId);
            if ($this->editOrder) {
                $this->customerName = $this->editOrder->customer_name ?? '';
                $this->tableNumber = $this->editOrder->table_number ?? '';
            }
        }
    }

    public function updatedCart(array $cart): void
    {
        $this->cart = $cart;
    }

    public function submitOrder(array $cartData, string $customerName, string $tableNumber): void
    {
        if (empty($cartData)) {
            $this->toastShow = true;
            $this->toastType = 'error';
            $this->toastMessage = 'Keranjang kosong';

            return;
        }

        if ($this->editOrderId && $this->editOrder) {
            foreach ($cartData as $item) {
                $this->editOrder->items()->create([
                    'menu_id' => $item['id'],
                    'quantity' => $item['qty'],
                    'unit_price' => $item['price'],
                    'subtotal' => $item['price'] * $item['qty'],
                    'notes' => $item['note'] ?? null,
                    'status' => 'waiting',
                ]);
            }

            $this->editOrder->update([
                'total_amount' => $this->editOrder->items()->sum('subtotal'),
                'customer_name' => $customerName ?: $this->editOrder->customer_name,
                'table_number' => $tableNumber ?: $this->editOrder->table_number,
            ]);

            $this->toastShow = true;
            $this->toastType = 'success';
            $this->toastMessage = 'Item ditambahkan ke order '.$this->editOrder->order_number;

            $this->redirect(route('dashboard.resto.orders'));

            return;
        }

        $orderNumber = Rst_Order::generateOrderNumber();
        $totalAmount = 0;

        foreach ($cartData as $item) {
            $totalAmount += $item['price'] * $item['qty'];
        }

        $order = Rst_Order::create([
            'order_number' => $orderNumber,
            'customer_name' => $customerName ?: 'Guest',
            'table_number' => $tableNumber,
            'total_amount' => $totalAmount,
        ]);

        foreach ($cartData as $item) {
            Rst_OrderItem::create([
                'order_id' => $order->id,
                'menu_id' => $item['id'],
                'quantity' => $item['qty'],
                'unit_price' => $item['price'],
                'subtotal' => $item['price'] * $item['qty'],
                'notes' => $item['note'] ?? null,
                'status' => 'waiting',
            ]);
        }

        $this->toastShow = true;
        $this->toastType = 'success';
        $this->toastMessage = "Order {$orderNumber} dikirim ke kitchen";
    }

    public function hideToast(): void
    {
        $this->toastShow = false;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Rst_Menu::where('is_active', true);

        if ($this->search) {
            $query->where('name', 'like', '%'.$this->search.'%');
        }

        if ($this->categoryFilter) {
            $query->where('category', $this->categoryFilter);
        }

        $menus = $query->orderBy('category')->orderBy('name')->paginate(12);

        $categories = Rst_Menu::where('is_active', true)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        return view('livewire.holdings.resto.pos.menu-page', [
            'menus' => $menus,
            'categories' => $categories,
        ])->layout('components.sccr-layout');
    }
}
