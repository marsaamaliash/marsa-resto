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

    public function render()
    {
        $query = Menu::where('is_active', true);

        if ($this->search) {
            $query->where('name', 'like', '%'.$this->search.'%');
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
