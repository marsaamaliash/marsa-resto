<?php

namespace App\Livewire\Holdings\Hq\Finance\Master;

use App\Models\Holdings\Hq\Finance\Fin_Account_List;
use Livewire\Component;

class FinMasterAccountShow extends Component
{
    public string $rowKey = '';

    public ?Fin_Account_List $row = null;

    public function mount(string $rowKey): void
    {
        $this->rowKey = $rowKey;

        $id = (int) $rowKey;
        if ($id <= 0) {
            abort(404, 'RowKey tidak valid (id).');
        }

        $this->row = Fin_Account_List::query()
            ->where('id', $id)
            ->firstOrFail();
    }

    public function render()
    {
        return view('livewire.holdings.hq.finance.master.fin-master-account-show', [
            'row' => $this->row,
        ])->layout('components.sccr-layout');
    }
}
