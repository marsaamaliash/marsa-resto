<?php

namespace App\Livewire\Holdings\Hq\Sdm\Rt\Inventaris;

use App\Models\Holdings\Hq\Sdm\Rt\Inventaris\InventarisList;
use Livewire\Component;

class InventarisShow extends Component
{
    public ?string $kodeLabel = null;

    public $data = null;

    public bool $asOverlay = false;

    public function mount(?string $kode_label = null, ?string $kodeLabel = null, bool $asOverlay = false): void
    {
        $this->asOverlay = $asOverlay;
        $this->kodeLabel = $kodeLabel ?? $kode_label;

        abort_unless($this->kodeLabel, 404, 'Kode label tidak ditemukan');

        $this->data = InventarisList::where('kode_label', $this->kodeLabel)->firstOrFail();
    }

    public function back(): mixed
    {
        return redirect()->route('holdings.hq.sdm.rt.inventaris.inventaris-table');
    }

    public function render()
    {
        $view = view('livewire.holdings.hq.sdm.rt.inventaris.inventaris-show');

        return $this->asOverlay
            ? $view
            : $view->layout('components.sccr-layout');
    }
}
