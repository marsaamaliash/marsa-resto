<?php

namespace App\Livewire\Holdings\Resto\Resep\Recipe;

use App\Models\Holdings\Resto\Master\Rst_MasterItem;
use App\Models\Holdings\Resto\Master\Rst_MasterSatuan;
use App\Models\Holdings\Resto\Resep\Rst_Recipe;
use App\Services\Resto\RecipeService;
use Livewire\Component;

class RecipeEdit extends Component
{
    public ?string $overlayId = null;

    public string $recipe_code = '';

    public string $recipe_name = '';

    public string $recipe_type = 'menu';

    public ?int $output_item_id = null;

    public ?int $default_uom_id = null;

    public string $issue_method = 'batch_actual';

    public string $yield_tracking_mode = 'strict';

    public bool $is_active = true;

    public string $notes = '';

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public array $items = [];

    public array $uoms = [];

    public function mount(string $id): void
    {
        $this->overlayId = $id;

        $recipe = Rst_Recipe::find($id);
        if ($recipe) {
            $this->recipe_code = $recipe->recipe_code ?? '';
            $this->recipe_name = $recipe->recipe_name;
            $this->recipe_type = $recipe->recipe_type;
            $this->output_item_id = $recipe->output_item_id;
            $this->default_uom_id = $recipe->default_uom_id;
            $this->issue_method = $recipe->issue_method;
            $this->yield_tracking_mode = $recipe->yield_tracking_mode;
            $this->is_active = $recipe->is_active;
            $this->notes = $recipe->notes ?? '';
        }

        $this->items = Rst_MasterItem::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn ($i) => ['value' => $i->id, 'label' => $i->name.' ('.$i->sku.')'])
            ->toArray();

        $this->uoms = Rst_MasterSatuan::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn ($u) => ['value' => $u->id, 'label' => $u->name.' ('.$u->symbols.')'])
            ->toArray();
    }

    public function update(): void
    {
        $this->validate([
            'recipe_code' => ['nullable', 'string', 'max:50'],
            'recipe_name' => ['required', 'string', 'max:255'],
            'recipe_type' => ['required', 'in:preparation,menu,additional'],
            'output_item_id' => ['required', 'integer', 'exists:sccr_resto.items,id'],
            'default_uom_id' => ['required', 'integer', 'exists:sccr_resto.uoms,id'],
            'issue_method' => ['required', 'in:batch_actual,manual,fifo'],
            'yield_tracking_mode' => ['required', 'in:strict,flexible'],
            'notes' => ['nullable', 'string', 'max:65535'],
        ]);

        try {
            app(RecipeService::class)->updateRecipe((int) $this->overlayId, [
                'recipe_code' => $this->recipe_code ?: null,
                'recipe_name' => $this->recipe_name,
                'recipe_type' => $this->recipe_type,
                'output_item_id' => $this->output_item_id,
                'default_uom_id' => $this->default_uom_id,
                'issue_method' => $this->issue_method,
                'yield_tracking_mode' => $this->yield_tracking_mode,
                'is_active' => $this->is_active,
                'notes' => $this->notes,
            ]);

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Resep berhasil diperbarui'];
            $this->dispatch('recipe-updated', $this->overlayId);
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
        return view('livewire.holdings.resto.resep.recipe.recipe-edit');
    }
}
