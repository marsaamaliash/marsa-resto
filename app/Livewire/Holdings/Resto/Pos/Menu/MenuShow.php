<?php

namespace App\Livewire\Holdings\Resto\Pos\Menu;

use App\Models\Holdings\Resto\Pos\Rst_Menu;
use Livewire\Attributes\On;
use Livewire\Component;

class MenuShow extends Component
{
    public array $breadcrumbs = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public bool $canWrite = false;

    public bool $canCreate = false;

    public bool $canUpdate = false;

    public bool $canDelete = false;

    public int $id;

    public ?string $overlayMode = null;

    private function syncCaps(): void
    {
        $u = auth()->user();

        $this->canCreate = (bool) ($u?->hasPermission('RECIPE_CREATE') ?? false);
        $this->canUpdate = (bool) ($u?->hasPermission('RECIPE_UPDATE') ?? false);
        $this->canDelete = (bool) ($u?->hasPermission('RECIPE_DELETE') ?? false);

        $this->canWrite = $this->canCreate || $this->canUpdate;
    }

    public function mount(int $id): void
    {
        $this->id = $id;

        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Master Menu', 'route' => 'dashboard.resto.menu', 'color' => 'text-gray-800'],
            ['label' => 'Detail', 'color' => 'text-gray-900 font-semibold'],
        ];

        $this->syncCaps();
    }

    public function hydrate(): void
    {
        $this->syncCaps();
    }

    public function getMenuProperty(): ?Rst_Menu
    {
        return Rst_Menu::with(['recipe', 'recipe.activeVersion', 'recipe.activeVersion.components.item', 'recipe.activeVersion.components.uom'])->find($this->id);
    }

    public function openAddRecipe(): void
    {
        if (! $this->canCreate) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin membuat resep.'];

            return;
        }

        $this->overlayMode = 'add-recipe';
    }

    public function closeOverlay(): void
    {
        $this->overlayMode = null;
    }

    #[On('recipe-created')]
    public function handleRecipeCreated(): void
    {
        $this->closeOverlay();
        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Resep berhasil dibuat dan ditautkan ke menu.'];

        // Force refresh menu data by reloading the page
        $this->redirect(route('dashboard.resto.menu.detail', $this->id));
    }

    public function goToRecipe(): void
    {
        $menu = $this->menu;
        if ($menu && $menu->recipe_id) {
            $this->redirect(route('dashboard.resto.resep.recipe.detail', $menu->recipe_id));
        }
    }

    public function render()
    {
        return view('livewire.holdings.resto.pos.menu.menu-show', [
            'menu' => $this->menu,
            'breadcrumbs' => $this->breadcrumbs,
        ])->layout('components.sccr-layout');
    }
}
