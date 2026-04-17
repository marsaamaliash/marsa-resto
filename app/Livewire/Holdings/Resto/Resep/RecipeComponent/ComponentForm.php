<?php

namespace App\Livewire\Holdings\Resto\Resep\RecipeComponent;

use App\Models\Holdings\Resto\Master\Rst_MasterItem;
use App\Models\Holdings\Resto\Master\Rst_MasterSatuan;
use App\Models\Holdings\Resto\Resep\Rst_Recipe;
use App\Models\Holdings\Resto\Resep\Rst_RecipeComponent;
use App\Services\Resto\RecipeBomService;
use Livewire\Component;

class ComponentForm extends Component
{
    public int $versionId;

    public ?int $componentId = null;

    public string $componentKind = 'item';

    public ?int $component_item_id = null;

    public ?int $component_recipe_id = null;

    public string $stageCode = 'main';

    public string $qtyStandard = '';

    public ?int $uom_id = null;

    public string $wastagePctStandard = '0';

    public bool $isOptional = false;

    public bool $isModifierDriven = false;

    public string $notes = '';

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public array $items = [];

    public array $recipes = [];

    public array $uoms = [];

    public function mount(int $versionId, ?int $componentId = null): void
    {
        $this->versionId = $versionId;
        $this->componentId = $componentId;

        $this->items = Rst_MasterItem::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn ($i) => ['value' => $i->id, 'label' => $i->name.' ('.$i->sku.')'])
            ->toArray();

        $this->recipes = Rst_Recipe::where('is_active', true)
            ->orderBy('recipe_name')
            ->get()
            ->map(fn ($r) => ['value' => $r->id, 'label' => $r->recipe_code.' - '.$r->recipe_name.' ('.ucfirst($r->recipe_type).')'])
            ->toArray();

        $this->uoms = Rst_MasterSatuan::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn ($u) => ['value' => $u->id, 'label' => $u->name.' ('.$u->symbols.')'])
            ->toArray();

        if ($componentId) {
            $component = Rst_RecipeComponent::find($componentId);
            if ($component) {
                $this->componentKind = $component->component_kind;
                $this->component_item_id = $component->component_item_id;
                $this->component_recipe_id = $component->component_recipe_id;
                $this->stageCode = $component->stage_code ?? 'main';
                $this->qtyStandard = $component->qty_standard;
                $this->uom_id = $component->uom_id;
                $this->wastagePctStandard = $component->wastage_pct_standard;
                $this->isOptional = $component->is_optional;
                $this->isModifierDriven = $component->is_modifier_driven;
                $this->notes = $component->notes ?? '';
            }
        }
    }

    public function updatedComponentKind(): void
    {
        if ($this->componentKind === 'item') {
            $this->component_recipe_id = null;
        } else {
            $this->component_item_id = null;
        }
    }

    public function save(): void
    {
        $rules = [
            'componentKind' => ['required', 'in:item,recipe'],
            'qtyStandard' => ['required', 'numeric', 'min:0.000001'],
            'uom_id' => ['required', 'integer', 'exists:sccr_resto.uoms,id'],
            'wastagePctStandard' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'isOptional' => ['boolean'],
            'isModifierDriven' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:65535'],
        ];

        if ($this->componentKind === 'item') {
            $rules['component_item_id'] = ['required', 'integer', 'exists:sccr_resto.items,id'];
        } else {
            $rules['component_recipe_id'] = ['required', 'integer', 'exists:sccr_resto.rec_recipes,id'];
        }

        $this->validate($rules);

        try {
            $data = [
                'component_kind' => $this->componentKind,
                'component_item_id' => $this->componentKind === 'item' ? $this->component_item_id : null,
                'component_recipe_id' => $this->componentKind === 'recipe' ? $this->component_recipe_id : null,
                'stage_code' => $this->stageCode,
                'qty_standard' => $this->qtyStandard,
                'uom_id' => $this->uom_id,
                'wastage_pct_standard' => $this->wastagePctStandard ?? 0,
                'is_optional' => $this->isOptional,
                'is_modifier_driven' => $this->isModifierDriven,
                'notes' => $this->notes ?: null,
            ];

            if ($this->componentId) {
                app(RecipeBomService::class)->updateComponent($this->componentId, $data);
                $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Komponen berhasil diperbarui.'];
                $this->dispatch('component-saved');
            } else {
                app(RecipeBomService::class)->addComponent($this->versionId, $data);
                $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Komponen berhasil ditambahkan.'];
                $this->dispatch('component-saved');
            }
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function cancel(): void
    {
        $this->dispatch('component-overlay-close');
    }

    public function render()
    {
        return view('livewire.holdings.resto.resep.recipe-component.component-form');
    }
}
