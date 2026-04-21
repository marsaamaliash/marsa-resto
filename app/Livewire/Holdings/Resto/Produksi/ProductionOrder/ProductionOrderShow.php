<?php

namespace App\Livewire\Holdings\Resto\Produksi\ProductionOrder;

use App\Models\Holdings\Resto\Master\Rst_MasterItem;
use App\Models\Holdings\Resto\Master\Rst_MasterSatuan;
use App\Models\Holdings\Resto\Produksi\Rst_ProductionOrder;
use App\Services\Resto\ProductionConsumeService;
use App\Services\Resto\ProductionExecutionService;
use App\Services\Resto\ProductionOrderService;
use Livewire\Component;

class ProductionOrderShow extends Component
{
    public array $breadcrumbs = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public bool $canWrite = false;

    public bool $canCreate = false;

    public bool $canUpdate = false;

    public bool $canDelete = false;

    public int $id;

    public string $activeTab = 'components';

    public ?string $overlayMode = null;

    public array $issueForm = [];

    public array $outputForm = [];

    public array $wasteForm = [];

    private function syncCaps(): void
    {
        $u = auth()->user();

        $this->canCreate = (bool) ($u?->hasPermission('PRODUCTION_CREATE') ?? false);
        $this->canUpdate = (bool) ($u?->hasPermission('PRODUCTION_UPDATE') ?? false);
        $this->canDelete = (bool) ($u?->hasPermission('PRODUCTION_DELETE') ?? false);

        $this->canWrite = $this->canCreate || $this->canUpdate;
    }

    public function mount(int $id): void
    {
        $this->id = $id;

        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Recipe', 'route' => 'dashboard.resto.resep', 'color' => 'text-gray-800'],
            ['label' => 'Production', 'route' => 'dashboard.resto.resep.production', 'color' => 'text-gray-800'],
            ['label' => 'Detail', 'color' => 'text-gray-900 font-semibold'],
        ];

        $this->syncCaps();

        $this->initIssueForm();
        $this->initOutputForm();
        $this->initWasteForm();
    }

    public function hydrate(): void
    {
        $this->syncCaps();
    }

    public function getOrderProperty(): ?Rst_ProductionOrder
    {
        return Rst_ProductionOrder::with([
            'recipe', 'recipeVersion', 'issueLocation', 'outputLocation', 'outputUom',
            'componentPlans.componentItem', 'componentPlans.componentRecipe', 'componentPlans.uom',
            'materialIssueLines.item', 'materialIssueLines.uom', 'materialIssueLines.issueLocation',
            'outputLines.outputItem', 'outputLines.uom', 'outputLines.outputLocation',
        ])->find($this->id);
    }

    private function initIssueForm(): void
    {
        $this->issueForm = [
            'plan_line_id' => null,
            'qty_issued' => '',
            'uom_id' => null,
            'notes' => '',
        ];
    }

    private function initOutputForm(): void
    {
        $this->outputForm = [
            'output_item_id' => null,
            'qty_output' => '',
            'uom_id' => null,
            'output_type' => 'main',
            'qc_status' => 'pending',
            'notes' => '',
        ];
    }

    private function initWasteForm(): void
    {
        $this->wasteForm = [
            'item_id' => null,
            'qty_waste' => '',
            'uom_id' => null,
            'waste_stage' => 'production',
            'waste_type' => 'normal',
            'charge_mode' => 'absorbed',
            'reason_code' => '',
            'notes' => '',
        ];
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function updateStatus(string $status): void
    {
        try {
            app(ProductionOrderService::class)->updateStatus($this->id, $status);
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Status changed to '.ucfirst(str_replace('_', ' ', $status)).'.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function issueMaterial(): void
    {
        $this->validate([
            'issueForm.plan_line_id' => ['required', 'integer'],
            'issueForm.qty_issued' => ['required', 'numeric', 'min:0.000001'],
        ]);

        try {
            app(ProductionExecutionService::class)->issueMaterial($this->id, $this->issueForm['plan_line_id'], [
                'qty_issued' => $this->issueForm['qty_issued'],
                'uom_id' => $this->issueForm['uom_id'],
                'notes' => $this->issueForm['notes'],
            ]);

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Material issued successfully.'];
            $this->initIssueForm();
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function recordOutput(): void
    {
        $this->validate([
            'outputForm.output_item_id' => ['required', 'integer'],
            'outputForm.qty_output' => ['required', 'numeric', 'min:0.000001'],
            'outputForm.uom_id' => ['required', 'integer'],
        ]);

        try {
            app(ProductionExecutionService::class)->recordOutput($this->id, [
                'output_item_id' => $this->outputForm['output_item_id'],
                'output_type' => $this->outputForm['output_type'],
                'qty_output' => $this->outputForm['qty_output'],
                'uom_id' => $this->outputForm['uom_id'],
                'output_location_id' => $this->order?->output_location_id,
                'qc_status' => $this->outputForm['qc_status'],
                'notes' => $this->outputForm['notes'],
            ]);

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Output recorded successfully.'];
            $this->initOutputForm();
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function postToInventory(int $outputLineId): void
    {
        try {
            app(ProductionExecutionService::class)->postOutputToInventory($outputLineId);
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Output posted to inventory successfully.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function recordWaste(): void
    {
        $this->validate([
            'wasteForm.item_id' => ['required', 'integer'],
            'wasteForm.qty_waste' => ['required', 'numeric', 'min:0.000001'],
            'wasteForm.uom_id' => ['required', 'integer'],
        ]);

        try {
            app(ProductionConsumeService::class)->recordWaste($this->id, [
                'item_id' => $this->wasteForm['item_id'],
                'qty_waste' => $this->wasteForm['qty_waste'],
                'uom_id' => $this->wasteForm['uom_id'],
                'waste_stage' => $this->wasteForm['waste_stage'] ?? 'production',
                'waste_type' => $this->wasteForm['waste_type'] ?? 'normal',
                'charge_mode' => $this->wasteForm['charge_mode'] ?? 'absorbed',
                'reason_code' => $this->wasteForm['reason_code'] ?? null,
                'notes' => $this->wasteForm['notes'] ?? null,
            ]);

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Waste recorded successfully.'];
            $this->wasteForm = ['item_id' => null, 'qty_waste' => '', 'uom_id' => null, 'waste_stage' => 'production', 'waste_type' => 'normal', 'charge_mode' => 'absorbed', 'reason_code' => '', 'notes' => ''];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function completeOrder(): void
    {
        try {
            app(ProductionConsumeService::class)->completeProduction($this->id);
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Production order completed. All outputs posted to inventory.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function getWasteLinesProperty()
    {
        return app(ProductionConsumeService::class)->getWasteLines($this->id);
    }

    public function getCostSummaryProperty()
    {
        return app(ProductionConsumeService::class)->getCostSummary($this->id);
    }

    public function render()
    {
        $order = $this->order;

        $items = Rst_MasterItem::where('is_active', true)->orderBy('name')->get()
            ->map(fn ($i) => ['value' => $i->id, 'label' => $i->name.' ('.$i->sku.')'])->toArray();

        $uoms = Rst_MasterSatuan::where('is_active', true)->orderBy('name')->get()
            ->map(fn ($u) => ['value' => $u->id, 'label' => $u->name.' ('.$u->symbols.')'])->toArray();

        return view('livewire.holdings.resto.produksi.production-order.production-order-show', [
            'order' => $order,
            'breadcrumbs' => $this->breadcrumbs,
            'items' => $items,
            'uoms' => $uoms,
            'wasteLines' => $this->wasteLines,
            'costSummary' => $this->costSummary,
        ])->layout('components.sccr-layout');
    }
}
