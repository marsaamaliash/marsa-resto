<?php

namespace App\Livewire\Holdings\Resto\Procurement\PurchaseOrder;

use App\Models\Holdings\Resto\Master\Rst_MasterLokasi;
use App\Services\Resto\PurchaseOrderService;
use Livewire\Component;
use Livewire\WithFileUploads;

class PurchaseOrderCreate extends Component
{
    use WithFileUploads;

    public array $breadcrumbs = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public int $selectedLocationId = 0;

    public int $selectedPRId = 0;

    public string $vendorName = '';

    public int $selectedVendorId = 0;

    public string $paymentBy = 'holding';

    public string $poNotes = '';

    public $quotationFile = null;

    public array $approvedPRs = [];

    public array $vendors = [];

    public array $selectedPRItems = [];

    public array $locations = [];

    public array $itemPrices = [];

    public array $removedItems = [];

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Procurement', 'route' => 'dashboard.resto.procurement', 'color' => 'text-gray-800'],
            ['label' => 'Purchase Order', 'route' => 'dashboard.resto.purchase-order', 'color' => 'text-gray-800'],
            ['label' => 'Create', 'color' => 'text-gray-900 font-semibold'],
        ];

        $locs = Rst_MasterLokasi::where('is_active', true)
            ->where('type', 'warehouse')
            ->get();
        $this->locations = $locs->map(fn ($loc) => ['id' => $loc->id, 'name' => $loc->name])->toArray();

        if (! empty($this->locations)) {
            $this->selectedLocationId = $this->locations[0]['id'];
            $this->loadApprovedPRs();
        }

        $this->loadVendors();
    }

    public function updatedSelectedLocationId(): void
    {
        $this->loadApprovedPRs();
        $this->selectedPRId = 0;
        $this->selectedPRItems = [];
        $this->itemPrices = [];
        $this->removedItems = [];
    }

    public function loadApprovedPRs(): void
    {
        if ($this->selectedLocationId > 0) {
            $this->approvedPRs = PurchaseOrderService::getApprovedPurchaseRequests($this->selectedLocationId);
        }
    }

    public function updatedSelectedPRId(): void
    {
        if ($this->selectedPRId > 0) {
            $pr = collect($this->approvedPRs)->firstWhere('id', $this->selectedPRId);
            if ($pr && ! empty($pr['items'])) {
                $this->selectedPRItems = [];
                $this->itemPrices = [];
                $this->removedItems = [];
                foreach ($pr['items'] as $item) {
                    $this->selectedPRItems[$item['id']] = $item;
                    $this->itemPrices[$item['id']] = $item['unit_cost'] ?? 0;
                }
            } else {
                $this->selectedPRItems = [];
                $this->itemPrices = [];
                $this->removedItems = [];
                $this->toast = ['show' => true, 'type' => 'error', 'message' => 'All items from this PR have already been ordered'];
            }
        } else {
            $this->selectedPRItems = [];
            $this->itemPrices = [];
            $this->removedItems = [];
        }
    }

    public function loadVendors(): void
    {
        $this->vendors = PurchaseOrderService::getActiveVendors();
    }

    public function removeItem(int $prItemId): void
    {
        $item = $this->selectedPRItems[$prItemId] ?? null;
        if ($item) {
            $this->removedItems[$prItemId] = [
                'id' => $item['id'],
                'item' => $item,
                'price' => $this->itemPrices[$prItemId] ?? 0,
            ];
            unset($this->selectedPRItems[$prItemId]);
            unset($this->itemPrices[$prItemId]);
        }
    }

    public function restoreItem(int $prItemId): void
    {
        $removed = $this->removedItems[$prItemId] ?? null;
        if ($removed) {
            $this->selectedPRItems[$prItemId] = $removed['item'];
            $this->itemPrices[$prItemId] = $removed['price'];
            unset($this->removedItems[$prItemId]);
        }
    }

    public function saveDraft(): void
    {
        $this->validate([
            'selectedPRId' => 'required|integer|min:1',
            'paymentBy' => 'required|in:holding,resto',
            'itemPrices' => 'required|array',
            'itemPrices.*' => 'required|numeric|min:0',
        ]);

        try {
            $quotationPath = null;
            if ($this->quotationFile) {
                $quotationPath = $this->quotationFile->store('po/quotations', 'public');
            }

            $vendor = collect($this->vendors)->firstWhere('id', $this->selectedVendorId);
            $vendorName = $vendor['name'] ?? null;

            $selectedItemIds = array_keys($this->selectedPRItems);

            $po = PurchaseOrderService::createFromPurchaseRequest(
                $this->selectedPRId,
                $vendorName,
                $this->selectedVendorId ?: null,
                $this->paymentBy,
                $quotationPath,
                $this->poNotes ?: null,
                $this->itemPrices,
                $selectedItemIds
            );

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Draft PO saved successfully'];

            redirect()->route('dashboard.resto.purchase-order');
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Error: '.$e->getMessage()];
        }
    }

    public function submitPO(): void
    {
        $this->validate([
            'selectedPRId' => 'required|integer|min:1',
            'selectedVendorId' => 'required|integer|min:1',
            'quotationFile' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'paymentBy' => 'required|in:holding,resto',
            'itemPrices' => 'required|array',
            'itemPrices.*' => 'required|numeric|min:0',
        ]);

        foreach ($this->itemPrices as $price) {
            if ($price <= 0) {
                $this->toast = ['show' => true, 'type' => 'error', 'message' => 'All item prices must be filled'];

                return;
            }
        }

        try {
            $quotationPath = $this->quotationFile->store('po/quotations', 'public');

            $vendor = collect($this->vendors)->firstWhere('id', $this->selectedVendorId);
            $vendorName = $vendor['name'] ?? '';

            $selectedItemIds = array_keys($this->selectedPRItems);

            $po = PurchaseOrderService::createFromPurchaseRequest(
                $this->selectedPRId,
                $vendorName,
                $this->selectedVendorId,
                $this->paymentBy,
                $quotationPath,
                $this->poNotes ?: null,
                $this->itemPrices,
                $selectedItemIds
            );

            PurchaseOrderService::submitForApproval($po->id);

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'PO successfully submitted to RM for approval'];

            redirect()->route('dashboard.resto.purchase-order');
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Error: '.$e->getMessage()];
        }
    }

    public function render()
    {
        $this->loadVendors();

        return view('livewire.holdings.resto.procurement.purchase-order.purchase-order-create')
            ->layout('components.sccr-layout');
    }
}
