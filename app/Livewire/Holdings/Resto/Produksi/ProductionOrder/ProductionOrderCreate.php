<?php

namespace App\Livewire\Holdings\Resto\Produksi\ProductionOrder;

use App\Models\Holdings\Resto\Master\Rst_MasterLokasi;
use App\Models\Holdings\Resto\Resep\Rst_Recipe;
use App\Models\Holdings\Resto\Resep\Rst_RecipeVersion;
use App\Services\Resto\ProductionOrderService;
use Livewire\Component;

class ProductionOrderCreate extends Component
{
    public ?int $recipe_id = null;

    public ?int $recipe_version_id = null;

    public ?int $issue_location_id = null;

    public ?int $output_location_id = null;

    public string $planned_output_qty = '';

    public ?int $output_uom_id = null;

    public string $prod_type = 'standard';

    public string $business_date = '';

    public string $notes = '';

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public array $recipes = [];

    public array $versions = [];

    public array $locations = [];

    public array $uoms = [];

    public function mount(): void
    {
        $this->recipes = Rst_Recipe::where('is_active', true)
            ->orderBy('recipe_name')
            ->get()
            ->map(fn ($r) => ['value' => $r->id, 'label' => $r->recipe_code.' - '.$r->recipe_name.' ('.ucfirst($r->recipe_type).')'])
            ->toArray();

        $this->locations = Rst_MasterLokasi::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn ($l) => ['value' => $l->id, 'label' => $l->name.' ('.$l->code.')'])
            ->toArray();

        $this->business_date = now()->toDateString();
    }

    public function updatedRecipeId(): void
    {
        $this->versions = [];
        $this->recipe_version_id = null;

        if ($this->recipe_id) {
            $this->versions = Rst_RecipeVersion::where('recipe_id', $this->recipe_id)
                ->where('is_active', true)
                ->orderBy('version_no', 'desc')
                ->get()
                ->map(fn ($v) => ['value' => $v->id, 'label' => 'V'.$v->version_no.' - '.($v->notes ?? 'No description')])
                ->toArray();

            $recipe = Rst_Recipe::find($this->recipe_id);
            if ($recipe) {
                $this->output_uom_id = $recipe->default_uom_id;
                $this->uoms = \App\Models\Holdings\Resto\Master\Rst_MasterSatuan::where('is_active', true)
                    ->orderBy('name')
                    ->get()
                    ->map(fn ($u) => ['value' => $u->id, 'label' => $u->name.' ('.$u->symbols.')'])
                    ->toArray();
            }
        }
    }

    public function store(): void
    {
        $this->validate([
            'recipe_id' => ['required', 'integer', 'exists:sccr_resto.rec_recipes,id'],
            'recipe_version_id' => ['required', 'integer', 'exists:sccr_resto.rec_recipe_versions,id'],
            'issue_location_id' => ['required', 'integer', 'exists:sccr_resto.locations,id'],
            'output_location_id' => ['required', 'integer', 'exists:sccr_resto.locations,id'],
            'planned_output_qty' => ['required', 'numeric', 'min:0.000001'],
            'output_uom_id' => ['required', 'integer', 'exists:sccr_resto.uoms,id'],
            'business_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:65535'],
        ]);

        try {
            $order = app(ProductionOrderService::class)->createFromRecipe($this->recipe_id, $this->recipe_version_id, [
                'issue_location_id' => $this->issue_location_id,
                'output_location_id' => $this->output_location_id,
                'planned_output_qty' => $this->planned_output_qty,
                'output_uom_id' => $this->output_uom_id,
                'prod_type' => $this->prod_type,
                'business_date' => $this->business_date,
                'notes' => $this->notes,
            ]);

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Production Order created successfully.'];

            $this->redirect(route('dashboard.resto.resep.production.detail', $order->id));
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function render()
    {
        return view('livewire.holdings.resto.produksi.production-order.production-order-create');
    }
}
