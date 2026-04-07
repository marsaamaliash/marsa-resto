<?php

namespace App\Livewire\Holdings\Hq\Finance\Master;

use App\Models\Holdings\Hq\Finance\Fin_Account_List;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class FinMasterAccountEdit extends Component
{
    public string $rowKey = '';

    public int $rowId = 0;

    // Untuk ditampilkan (disabled) di blade
    public string $holdingName = '';

    public string $departmentName = '';

    public string $divisionName = '';

    public string $code = '';

    // Editable
    public string $name = '';

    public function mount(string $rowKey): void
    {
        $this->rowKey = $rowKey;

        $id = (int) $rowKey;
        if ($id <= 0) {
            abort(404, 'RowKey tidak valid (id).');
        }
        $this->rowId = $id;

        // ambil dari VIEW agar dapat holding_name/department_name/division_name
        $row = Fin_Account_List::query()
            ->where('id', $this->rowId)
            ->firstOrFail();

        $this->holdingName = (string) ($row->holding_name ?? '');
        $this->departmentName = (string) ($row->department_name ?? '');
        $this->divisionName = (string) ($row->division_name ?? '');
        $this->code = (string) ($row->code ?? '');
        $this->name = (string) ($row->name ?? '');
    }

    public function save(): void
    {
        if (! auth()->user()?->hasPermission('FIN_MASTER_ACCOUNT_UPDATE')) {
            $this->addError('name', 'Tidak punya izin update Master Account.');

            return;
        }

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        DB::table('fin_accounts')
            ->where('id', $this->rowId)
            ->update([
                'name' => $this->name,
            ]);

        // Table yang handle close overlay + toast
        $this->dispatch('fin-master-account-updated', rowKey: (string) $this->rowId);
    }

    public function render()
    {
        return view('livewire.holdings.hq.finance.master.fin-master-account-edit');
    }
}
