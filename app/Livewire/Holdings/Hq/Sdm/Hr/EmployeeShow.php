<?php

namespace App\Livewire\Holdings\Hq\Sdm\Hr;

use App\Models\Holdings\Hq\Sdm\Hr\Emp_Employee;
use Livewire\Component;

class EmployeeShow extends Component
{
    public string $nip = '';

    public bool $asOverlay = false;

    public $employee = null;

    public function mount(?string $nip = null, bool $asOverlay = false): void
    {
        $this->asOverlay = $asOverlay;

        $key = trim((string) $nip);
        abort_unless($key !== '', 404, 'NIP tidak ditemukan');

        $this->nip = $key;

        $this->employee = Emp_Employee::query()
            ->with([
                'holding',
                'department',
                'division',
                'position',
                'jobTitleMaster', // ✅ sesuai model
                // 'jobTitles',      // ✅ optional (pivot)
            ])
            ->where('nip', $this->nip)
            ->firstOrFail();
    }

    public function render()
    {
        $view = view('livewire.holdings.hq.sdm.hr.employee-show', [
            'employee' => $this->employee,
            'asOverlay' => $this->asOverlay,
        ]);

        return $this->asOverlay
            ? $view
            : $view->layout('components.sccr-layout');
    }
}
