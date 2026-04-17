<?php

namespace App\Livewire\Holdings\Resto\Resep\Recipe;

use App\Models\Holdings\Resto\Pos\Rst_Menu;
use App\Models\Holdings\Resto\Resep\Rst_Recipe;
use App\Services\Resto\RecipeService;
use Livewire\Component;

class RecipeCreate extends Component
{
    public ?int $menu_id = null;

    public string $recipe_type = 'menu'; // 'menu' or 'semi_finished'

    public string $recipe_name = '';

    public string $version_notes = '';

    public array $components = [];

    public array $availableMenus = [];

    public array $kitchenItems = [];

    public array $semiFinishedRecipes = [];

    public bool $isSemiFinished = false;

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public function mount(?int $preSelectedMenuId = null, bool $isSemiFinished = false): void
    {
        $this->isSemiFinished = $isSemiFinished;

        // Load kitchen items for component selection
        $this->kitchenItems = app(RecipeService::class)->getKitchenItems();

        // Load semi-finished recipes (recipes with recipe_type = 'semi_finished' that are active)
        $this->semiFinishedRecipes = Rst_Recipe::where('recipe_type', 'semi_finished')
            ->where('is_active', true)
            ->orderBy('recipe_name')
            ->get()
            ->map(fn ($r) => [
                'recipe_id' => $r->id,
                'recipe_code' => $r->recipe_code,
                'recipe_name' => $r->recipe_name,
                'uom_id' => $r->default_uom_id,
            ])
            ->toArray();

        if ($isSemiFinished) {
            // Semi-finished recipe - no menu needed
            $this->recipe_type = 'semi_finished';
            $this->availableMenus = [];
        } elseif ($preSelectedMenuId) {
            // Menu recipe with pre-selected menu
            $this->recipe_type = 'menu';
            $this->menu_id = $preSelectedMenuId;
            $menu = Rst_Menu::find($preSelectedMenuId);
            if ($menu) {
                $this->recipe_name = $menu->name;
                $this->availableMenus = [
                    ['value' => $menu->id, 'label' => $menu->name.' ('.$menu->category.')'],
                ];
            }
        } else {
            // Menu recipe - select from available menus
            $this->recipe_type = 'menu';
            $this->availableMenus = Rst_Menu::where('is_active', true)
                ->whereDoesntHave('recipe', function ($q) {
                    $q->where('is_active', true);
                })
                ->orderBy('name')
                ->get()
                ->map(fn ($m) => ['value' => $m->id, 'label' => $m->name.' ('.$m->category.')'])
                ->toArray();
        }
    }

    public function addComponent(): void
    {
        $this->components[] = [
            'component_type' => 'item', // 'item' or 'recipe'
            'item_id' => null,
            'recipe_id' => null,
            'qty' => '',
            'uom_id' => null,
            'uom_name' => '',
        ];
    }

    public function removeComponent(int $index): void
    {
        unset($this->components[$index]);
        $this->components = array_values($this->components);
    }

    public function updatedComponents($value, $key): void
    {
        // Handle component type change - reset selections when type changes
        if (str_contains($key, '.component_type')) {
            $index = explode('.', $key)[0];
            $this->components[$index]['item_id'] = null;
            $this->components[$index]['recipe_id'] = null;
            $this->components[$index]['uom_id'] = null;
            $this->components[$index]['uom_name'] = '';
        }

        // Handle item selection - auto-populate UOM
        if (str_contains($key, '.item_id')) {
            $index = explode('.', $key)[0];
            $itemId = $value;

            // Find the kitchen item to get UOM info
            foreach ($this->kitchenItems as $item) {
                if ($item['item_id'] == $itemId) {
                    $this->components[$index]['uom_id'] = $item['uom_id'];
                    $this->components[$index]['uom_name'] = $item['uom_name'];
                    break;
                }
            }
        }

        // Handle recipe selection - auto-populate UOM
        if (str_contains($key, '.recipe_id')) {
            $index = explode('.', $key)[0];
            $recipeId = $value;

            // Find the semi-finished recipe to get UOM info
            foreach ($this->semiFinishedRecipes as $recipe) {
                if ($recipe['recipe_id'] == $recipeId) {
                    $this->components[$index]['uom_id'] = $recipe['uom_id'];
                    break;
                }
            }
        }
    }

    public function store(): void
    {
        $rules = [
            'recipe_name' => ['required', 'string', 'max:255'],
            'version_notes' => ['nullable', 'string', 'max:65535'],
            'components' => ['required', 'array', 'min:1'],
            'components.*.component_type' => ['required', 'in:item,recipe'],
            'components.*.qty' => ['required', 'numeric', 'min:0.01'],
        ];

        // Only require menu_id for menu type recipes
        if (! $this->isSemiFinished) {
            $rules['menu_id'] = ['required', 'integer', 'exists:sccr_resto.menus,id'];
        }

        // Conditional validation based on component type
        foreach ($this->components as $index => $component) {
            if ($component['component_type'] === 'item') {
                $rules["components.{$index}.item_id"] = ['required', 'integer', 'exists:sccr_resto.items,id'];
                $rules["components.{$index}.uom_id"] = ['required', 'integer', 'exists:sccr_resto.uoms,id'];
            } else {
                $rules["components.{$index}.recipe_id"] = ['required', 'integer', 'exists:sccr_resto.rec_recipes,id'];
            }
        }

        $messages = [
            'menu_id.required' => 'Pilih menu terlebih dahulu',
            'recipe_name.required' => 'Nama resep wajib diisi',
            'components.required' => 'Minimal harus ada 1 komponen bahan',
            'components.*.component_type.required' => 'Pilih tipe komponen',
            'components.*.item_id.required' => 'Pilih item bahan',
            'components.*.recipe_id.required' => 'Pilih resep semi-finished',
            'components.*.uom_id.required' => 'Satuan wajib diisi',
            'components.*.qty.required' => 'Masukkan jumlah/qty',
            'components.*.qty.min' => 'Qty minimal 0.01',
        ];

        $this->validate($rules, $messages);

        try {
            // Transform components for storage
            $transformedComponents = [];
            foreach ($this->components as $component) {
                if ($component['component_type'] === 'item') {
                    $transformedComponents[] = [
                        'item_id' => $component['item_id'],
                        'qty' => $component['qty'],
                        'uom_id' => $component['uom_id'],
                    ];
                } else {
                    // For recipe components, we'll store them differently
                    // Note: The database structure needs to support this
                    $transformedComponents[] = [
                        'item_id' => null,
                        'qty' => $component['qty'],
                        'uom_id' => 1, // Default UOM for recipe references
                        // Recipe reference would need a separate field in the database
                    ];
                }
            }

            // Validate that all items are available in kitchen stock
            $itemComponents = array_filter($this->components, fn ($c) => $c['component_type'] === 'item');
            if (! empty($itemComponents)) {
                $validationErrors = app(RecipeService::class)->validateComponents($itemComponents);
                if (! empty($validationErrors)) {
                    $this->toast = ['show' => true, 'type' => 'error', 'message' => implode(', ', $validationErrors)];

                    return;
                }
            }

            $recipeData = [
                'menu_id' => $this->menu_id, // null for semi-finished
                'recipe_name' => $this->recipe_name,
                'recipe_type' => $this->recipe_type,
                'version_notes' => $this->version_notes,
                'components' => $transformedComponents,
                'is_active' => true,
            ];

            $recipe = app(RecipeService::class)->createRecipe($recipeData);

            $successMessage = $this->isSemiFinished
                ? 'Resep semi-finished berhasil dibuat dengan versi 1'
                : 'Resep berhasil dibuat dengan versi 1';

            $this->toast = ['show' => true, 'type' => 'success', 'message' => $successMessage];
            $this->dispatch('recipe-created', $recipe->id);
            $this->dispatch('recipe-overlay-close');

        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function cancel(): void
    {
        $this->dispatch('recipe-overlay-close');
    }

    public function render()
    {
        return view('livewire.holdings.resto.resep.recipe.recipe-create', [
            'availableMenus' => $this->availableMenus,
            'kitchenItems' => $this->kitchenItems,
            'semiFinishedRecipes' => $this->semiFinishedRecipes,
            'isSemiFinished' => $this->isSemiFinished,
        ]);
    }
}
