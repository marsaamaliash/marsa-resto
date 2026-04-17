<?php

namespace App\Livewire\Holdings\Resto\Resep\RecipeOutput;

use App\Models\Holdings\Resto\Master\Rst_MasterItem;
use App\Models\Holdings\Resto\Master\Rst_MasterSatuan;
use App\Models\Holdings\Resto\Resep\Rst_RecipeOutput;
use Livewire\Component;

class OutputForm extends Component
{
    public int $versionId;

    public ?int $outputId = null;

    public string $outputType = 'main';

    public ?int $output_item_id = null;

    public string $plannedQty = '';

    public ?int $uom_id = null;

    public string $costAllocationPct = '100';

    public bool $isInventoryItem = true;

    public string $notes = '';

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public array $items = [];

    public array $uoms = [];

    public function mount(int $versionId, ?int $outputId = null): void
    {
        $this->versionId = $versionId;
        $this->outputId = $outputId;

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

        if ($outputId) {
            $output = Rst_RecipeOutput::find($outputId);
            if ($output) {
                $this->outputType = $output->output_type;
                $this->output_item_id = $output->output_item_id;
                $this->plannedQty = $output->planned_qty;
                $this->uom_id = $output->uom_id;
                $this->costAllocationPct = $output->cost_allocation_pct;
                $this->isInventoryItem = $output->is_inventory_item;
                $this->notes = $output->notes ?? '';
            }
        }
    }

    public function save(): void
    {
        $this->validate([
            'outputType' => ['required', 'in:main,by_product,co_product,waste'],
            'output_item_id' => ['required', 'integer', 'exists:sccr_resto.items,id'],
            'plannedQty' => ['required', 'numeric', 'min:0.000001'],
            'uom_id' => ['required', 'integer', 'exists:sccr_resto.uoms,id'],
            'costAllocationPct' => ['required', 'numeric', 'min:0', 'max:100'],
            'isInventoryItem' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:65535'],
        ]);

        try {
            $maxLineNo = Rst_RecipeOutput::where('recipe_version_id', $this->versionId)
                ->withTrashed()
                ->max('line_no') ?? 0;

            $data = [
                'recipe_version_id' => $this->versionId,
                'line_no' => $maxLineNo + 10,
                'output_type' => $this->outputType,
                'output_item_id' => $this->output_item_id,
                'planned_qty' => $this->plannedQty,
                'uom_id' => $this->uom_id,
                'cost_allocation_pct' => $this->costAllocationPct,
                'is_inventory_item' => $this->isInventoryItem,
                'notes' => $this->notes ?: null,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ];

            if ($this->outputId) {
                unset($data['line_no'], $data['created_by']);
                $data['updated_by'] = auth()->id();
                Rst_RecipeOutput::findOrFail($this->outputId)->update($data);
                $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Output berhasil diperbarui.'];
            } else {
                Rst_RecipeOutput::create($data);
                $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Output berhasil ditambahkan.'];
            }

            $this->dispatch('output-saved');
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function cancel(): void
    {
        $this->dispatch('output-overlay-close');
    }

    public function render()
    {
        return view('livewire.holdings.resto.resep.recipe-output.output-form');
    }
}
