<?php

namespace App\Livewire\Holdings\Resto\Resep\Recipe;

use App\Models\Holdings\Resto\Pos\Rst_Menu;
use App\Models\Holdings\Resto\Resep\Rst_Recipe;
use App\Models\Holdings\Resto\Resep\Rst_RecipeComponent;
use App\Models\Holdings\Resto\Resep\Rst_RecipeVersion;
use App\Services\Resto\RecipeService;
use App\Services\Resto\RecipeVersionService;
use Livewire\Attributes\On;
use Livewire\Component;

class RecipeShow extends Component
{
    public array $breadcrumbs = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public bool $canWrite = false;

    public bool $canCreate = false;

    public bool $canUpdate = false;

    public bool $canDelete = false;

    public int $id;

    public ?string $overlayMode = null;

    public ?string $overlayId = null;

    public string $activeTab = 'versions';

    public ?int $selectedVersionId = null;

    public string $newVersionNotes = '';

    public array $newVersionComponents = [];

    public array $kitchenItems = [];

    private function syncCaps(): void
    {
        $u = auth()->user();

        $this->canCreate = (bool) ($u?->hasPermission('RECIPE_VERSION_CREATE') ?? false);
        $this->canUpdate = (bool) ($u?->hasPermission('RECIPE_VERSION_UPDATE') ?? false);
        $this->canDelete = (bool) ($u?->hasPermission('RECIPE_VERSION_DELETE') ?? false);

        $this->canWrite = $this->canCreate || $this->canUpdate;
    }

    public function mount(int $id): void
    {
        $this->id = $id;

        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Resep', 'route' => 'dashboard.resto.resep', 'color' => 'text-gray-800'],
            ['label' => 'Recipe', 'route' => 'dashboard.resto.resep.recipe', 'color' => 'text-gray-800'],
            ['label' => 'Detail', 'color' => 'text-gray-900 font-semibold'],
        ];

        $this->syncCaps();

        // Load kitchen items for component selection
        $this->kitchenItems = app(RecipeService::class)->getKitchenItems();
    }

    public function hydrate(): void
    {
        $this->syncCaps();
    }

    public function getRecipeProperty(): ?Rst_Recipe
    {
        return Rst_Recipe::with(['menu', 'activeVersion', 'activeVersion.components.item', 'activeVersion.components.uom'])->find($this->id);
    }

    public function getVersionsProperty()
    {
        return Rst_RecipeVersion::where('recipe_id', $this->id)
            ->withCount('components')
            ->orderBy('version_no', 'desc')
            ->get();
    }

    public function getComponentsProperty()
    {
        if (! $this->selectedVersionId) {
            return collect();
        }

        return Rst_RecipeComponent::where('recipe_version_id', $this->selectedVersionId)
            ->with(['componentItem', 'uom'])
            ->orderBy('line_no')
            ->get();
    }

    public function getSelectedVersionProperty()
    {
        if (! $this->selectedVersionId) {
            return null;
        }

        return Rst_RecipeVersion::with(['components'])->find($this->selectedVersionId);
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function selectVersion(int $versionId): void
    {
        $this->selectedVersionId = $versionId;
    }

    public function activateVersion(int $versionId): void
    {
        try {
            app(RecipeVersionService::class)->activateVersion($versionId);
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Versi berhasil diaktifkan.'];
            $this->selectedVersionId = $versionId;
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function deleteVersion(int $versionId): void
    {
        if (! $this->canDelete) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin delete.'];

            return;
        }

        try {
            app(RecipeVersionService::class)->deleteVersion($versionId);
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Versi berhasil dihapus.'];
            if ($this->selectedVersionId === $versionId) {
                $this->selectedVersionId = null;
            }
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function toggleRecipeActive(): void
    {
        $recipe = $this->recipe;
        if (! $recipe) {
            return;
        }

        try {
            if ($recipe->is_active) {
                app(RecipeService::class)->deactivateRecipe($this->id);
                $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Resep berhasil dinonaktifkan.'];
            } else {
                // Activate recipe - will need to activate a version too
                $recipe->update(['is_active' => true, 'updated_by' => auth()->id()]);
                $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Resep berhasil diaktifkan.'];
            }
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function openCreateVersion(): void
    {
        $this->overlayMode = 'create-version';
        $this->newVersionNotes = '';
        $this->newVersionComponents = [];

        // Pre-populate with components from active version if exists
        $activeVersion = $this->recipe?->activeVersion;
        if ($activeVersion) {
            foreach ($activeVersion->components as $comp) {
                $this->newVersionComponents[] = [
                    'item_id' => $comp->component_item_id,
                    'qty' => $comp->qty_standard,
                    'uom_id' => $comp->uom_id,
                    'uom_name' => $comp->uom?->name ?? '',
                ];
            }
        }
    }

    public function addVersionComponent(): void
    {
        $this->newVersionComponents[] = [
            'item_id' => null,
            'qty' => '',
            'uom_id' => null,
            'uom_name' => '',
        ];
    }

    public function removeVersionComponent(int $index): void
    {
        unset($this->newVersionComponents[$index]);
        $this->newVersionComponents = array_values($this->newVersionComponents);
    }

    public function updatedNewVersionComponents($value, $key): void
    {
        if (str_contains($key, '.item_id')) {
            $index = explode('.', $key)[0];
            $itemId = $value;

            foreach ($this->kitchenItems as $item) {
                if ($item['item_id'] == $itemId) {
                    $this->newVersionComponents[$index]['uom_id'] = $item['uom_id'];
                    $this->newVersionComponents[$index]['uom_name'] = $item['uom_name'];
                    break;
                }
            }
        }
    }

    public function closeOverlay(): void
    {
        $this->reset(['overlayMode', 'overlayId', 'newVersionNotes', 'newVersionComponents']);
    }

    public function storeVersion(): void
    {
        $this->validate([
            'newVersionNotes' => ['nullable', 'string', 'max:65535'],
            'newVersionComponents' => ['required', 'array', 'min:1'],
            'newVersionComponents.*.item_id' => ['required', 'integer', 'exists:sccr_resto.items,id'],
            'newVersionComponents.*.qty' => ['required', 'numeric', 'min:0.01'],
            'newVersionComponents.*.uom_id' => ['required', 'integer', 'exists:sccr_resto.uoms,id'],
        ]);

        try {
            // Validate kitchen items
            $validationErrors = app(RecipeService::class)->validateComponents($this->newVersionComponents);
            if (! empty($validationErrors)) {
                $this->toast = ['show' => true, 'type' => 'error', 'message' => implode(', ', $validationErrors)];

                return;
            }

            $version = app(RecipeVersionService::class)->createVersion($this->id, [
                'notes' => $this->newVersionNotes,
                'components' => $this->newVersionComponents,
            ]);

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Versi baru berhasil dibuat. Silakan aktifkan versi ini.'];
            $this->closeOverlay();
            $this->selectedVersionId = $version->id;

        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function linkRecipeToMenu(int $menuId): void
    {
        $menu = Rst_Menu::find($menuId);
        if (! $menu) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Menu tidak ditemukan.'];

            return;
        }

        $menu->update(['recipe_id' => $this->id]);
        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Resep berhasil di-link ke menu "'.$menu->name.'".'];
    }

    public function unlinkRecipeFromMenu(int $menuId): void
    {
        $menu = Rst_Menu::find($menuId);
        if (! $menu) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Menu tidak ditemukan.'];

            return;
        }

        $menu->update(['recipe_id' => null]);
        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Resep berhasil di-unlink dari menu "'.$menu->name.'".'];
    }

    #[On('recipe-updated')]
    public function handleUpdated(): void
    {
        $this->closeOverlay();
        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Resep berhasil diperbarui.'];
    }

    #[On('recipe-overlay-close')]
    public function handleOverlayClose(): void
    {
        $this->closeOverlay();
    }

    public function render()
    {
        return view('livewire.holdings.resto.resep.recipe.recipe-show', [
            'recipe' => $this->recipe,
            'versions' => $this->versions,
            'components' => $this->components,
            'selectedVersion' => $this->selectedVersion,
            'breadcrumbs' => $this->breadcrumbs,
            'kitchenItems' => $this->kitchenItems,
        ])->layout('components.sccr-layout');
    }
}
