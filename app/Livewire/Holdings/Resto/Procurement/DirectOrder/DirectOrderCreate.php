<?php

namespace App\Livewire\Holdings\Resto\Procurement\DirectOrder;

use App\Models\Holdings\Resto\Master\Rst_MasterItem;
use App\Models\Holdings\Resto\Master\Rst_MasterLokasi;
use App\Models\Holdings\Resto\Master\Rst_MasterSatuan;
use App\Services\Resto\DirectOrderService;
use Livewire\Component;
use Livewire\WithFileUploads;

class DirectOrderCreate extends Component
{
    use WithFileUploads;

    public array $breadcrumbs = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public int $selectedLocationId = 0;

    public string $purchaseDate = '';

    public string $paymentBy = 'holding';

    public $proofFile = null;

    public string $doNotes = '';

    public array $locations = [];

    public array $items = [];

    public array $uoms = [];

    public array $rows = [];

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Procurement', 'route' => 'dashboard.resto.procurement', 'color' => 'text-gray-800'],
            ['label' => 'Direct Order', 'route' => 'dashboard.resto.direct-order', 'color' => 'text-gray-800'],
            ['label' => 'Create', 'color' => 'text-gray-900 font-semibold'],
        ];

        $locs = Rst_MasterLokasi::where('is_active', true)->get();
        $this->locations = $locs->map(fn ($loc) => ['id' => $loc->id, 'name' => $loc->name])->toArray();

        if (! empty($this->locations)) {
            $this->selectedLocationId = $this->locations[0]['id'];
        }

        $uoms = Rst_MasterSatuan::where('is_active', true)->get();
        $this->uoms = $uoms->map(fn ($u) => ['id' => $u->id, 'name' => $u->name])->toArray();

        $this->items = Rst_MasterItem::where('is_active', true)->with('uom')->get()->toArray();

        $this->purchaseDate = now()->format('Y-m-d');

        $this->addRow();
    }

    public function addRow(): void
    {
        $this->rows[] = [
            'item_id' => 0,
            'uom_id' => 0,
            'quantity' => 1,
            'unit_price' => 0,
            'notes' => '',
        ];
    }

    public function removeRow($index): void
    {
        if (count($this->rows) > 1) {
            unset($this->rows[$index]);
            $this->rows = array_values($this->rows);
        }
    }

    public function updatedRows(): void {}

    public function submitDO(): void
    {
        $this->validate([
            'selectedLocationId' => 'required|integer|min:1',
            'purchaseDate' => 'required|date',
            'paymentBy' => 'required|string',
            'proofFile' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'doNotes' => 'nullable|string',
            'rows' => 'required|array|min:1',
            'rows.*.item_id' => 'required|integer|min:1',
            'rows.*.quantity' => 'required|numeric|min:0.01',
            'rows.*.unit_price' => 'required|numeric|min:0',
        ]);

        foreach ($this->rows as $row) {
            if ((float) $row['unit_price'] <= 0) {
                $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Semua harga item harus lebih dari 0'];

                return;
            }
        }

        try {
            $do = DirectOrderService::createFromInput(
                $this->selectedLocationId,
                auth()->user()?->username ?? 'SYSTEM',
                $this->purchaseDate,
                $this->paymentBy,
                $this->proofFile,
                $this->rows,
                $this->doNotes ?: null
            );

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Direct Order berhasil dibuat'];

            redirect()->route('dashboard.resto.direct-order.detail', ['id' => $do->id]);
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Error: '.$e->getMessage()];
        }
    }

    public function render()
    {
        return view('livewire.holdings.resto.procurement.direct-order.direct-order-create')
            ->layout('components.sccr-layout');
    }
}
