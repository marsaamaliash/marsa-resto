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

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Procurement', 'route' => 'dashboard.resto.procurement', 'color' => 'text-gray-800'],
            ['label' => 'Purchase Order', 'route' => 'dashboard.resto.purchase-order', 'color' => 'text-gray-800'],
            ['label' => 'Create', 'color' => 'text-gray-900 font-semibold'],
        ];

        $locs = Rst_MasterLokasi::where('is_active', true)->get();
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
            if ($pr) {
                $this->selectedPRItems = $pr['items'] ?? [];
                $this->itemPrices = [];
                foreach ($this->selectedPRItems as $index => $item) {
                    $this->itemPrices[$index] = $item['unit_cost'] ?? 0;
                }
            }
        }
    }

    public function loadVendors(): void
    {
        $this->vendors = PurchaseOrderService::getActiveVendors();
    }

    public function updatedItemPrices(): void {}

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
                $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Semua harga item harus diisi'];

                return;
            }
        }

        try {
            $quotationPath = $this->quotationFile->store('po/quotations', 'public');

            $vendor = collect($this->vendors)->firstWhere('id', $this->selectedVendorId);
            $vendorName = $vendor['name'] ?? '';

            $po = PurchaseOrderService::createFromPurchaseRequest(
                $this->selectedPRId,
                $vendorName,
                $this->selectedVendorId,
                $this->paymentBy,
                $quotationPath,
                $this->poNotes ?: null,
                $this->itemPrices
            );

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'PO berhasil dibuat'];

            redirect()->route('dashboard.resto.purchase-order.detail', ['id' => $po->id]);
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
