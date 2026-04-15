<?php

namespace App\Livewire\Holdings\Resto\Pos;

use App\Models\Holdings\Resto\Pos\Menu;
use Livewire\Component;
use Livewire\WithPagination;

class MenuPage extends Component
{
    use WithPagination;

    public array $breadcrumbs = [];

    public string $search = '';

    public string $categoryFilter = '';

    public array $cart = [];

    public string $customerName = '';

    public string $tableNumber = '';

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Daftar Menu', 'color' => 'text-gray-900 font-semibold'],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function addToCart(int $menuId): void
    {
        $menu = Menu::find($menuId);

        if (!$menu) {
            return;
        }

        if (isset($this->cart[$menuId])) {
            $this->cart[$menuId]['qty']++;
        } else {
            $this->cart[$menuId] = [
                'id' => $menu->id,
                'name' => $menu->name,
                'price' => (float) $menu->price,
                'qty' => 1,
            ];
        }
    }

    public function removeFromCart(int $menuId): void
    {
        if (isset($this->cart[$menuId])) {
            if ($this->cart[$menuId]['qty'] > 1) {
                $this->cart[$menuId]['qty']--;
            } else {
                unset($this->cart[$menuId]);
            }
        }
    }

    public function deleteFromCart(int $menuId): void
    {
        unset($this->cart[$menuId]);
    }

    public function getCartTotalProperty(): float
    {
        return collect($this->cart)->sum(fn($item) => $item['price'] * $item['qty']);
    }

    public function getCartCountProperty(): int
    {
        return collect($this->cart)->sum('qty');
    }

    public function render()
    {
        $query = Menu::where('is_active', true);

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        if ($this->categoryFilter) {
            $query->where('category', $this->categoryFilter);
        }

        $menus = $query->orderBy('category')->orderBy('name')->paginate(12);

        $categories = Menu::where('is_active', true)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        return view('livewire.holdings.resto.pos.menu-page', [
            'menus' => $menus,
            'categories' => $categories,
        ])->layout('components.sccr-layout');
    }
}
