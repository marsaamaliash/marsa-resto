<?php

namespace App\Livewire\Holdings\Hq\Sdm\Rt\Inventaris\Master;

use Livewire\Component;

class InvMasterIndex extends Component
{
    public array $cards = [];

    public function mount(): void
    {
        $u = auth()->user();
        abort_unless($u, 401);

        // Minimal: user harus punya minimal 1 master view
        $canAny =
            $u->hasPermission('INV_MASTER_LOKASI_VIEW')
            || $u->hasPermission('INV_MASTER_RUANGAN_VIEW')
            || $u->hasPermission('INV_MASTER_JENIS_VIEW')
            || $u->hasPermission('INV_MASTER_HOLDING_VIEW');

        abort_unless($canAny, 403, 'Tidak punya akses Master Inventaris');

        // Cards (ERP Style): Truth pages
        $this->cards = [
            [
                'perm' => 'INV_MASTER_LOKASI_VIEW',
                'route' => 'holdings.hq.sdm.rt.inventaris.master.lokasi.table',
                'title' => 'Master Lokasi',
                'desc' => 'CRUD + Approval Delete',
                'img' => 'images/tb-sdm-ga-inv-master-lokasi.png',
                'fallback' => 'images/tb-master.png',
            ],
            [
                'perm' => 'INV_MASTER_RUANGAN_VIEW',
                'route' => 'holdings.hq.sdm.rt.inventaris.master.ruangan.table',
                'title' => 'Master Ruangan',
                'desc' => 'CRUD + Approval Delete',
                'img' => 'images/tb-sdm-ga-inv-master-ruangan.png',
                'fallback' => 'images/tb-master.png',
            ],
            [
                'perm' => 'INV_MASTER_JENIS_VIEW',
                'route' => 'holdings.hq.sdm.rt.inventaris.master.jenis.table',
                'title' => 'Master Jenis Barang',
                'desc' => 'CRUD + Approval Delete',
                'img' => 'images/tb-sdm-ga-inv-master-jenis.png',
                'fallback' => 'images/tb-master.png',
            ],
            [
                'perm' => 'INV_MASTER_HOLDING_VIEW',
                'route' => 'holdings.hq.sdm.rt.inventaris.master.holding.table',
                'title' => 'Master Holding',
                'desc' => 'CRUD + Approval Delete',
                'img' => 'images/tb-sdm-ga-inv-master-holding.png',
                'fallback' => 'images/tb-master.png',
            ],
        ];
    }

    public function render()
    {
        return view('livewire.holdings.hq.sdm.rt.inventaris.master.inv-master-index')->layout('components.sccr-layout');
    }
}
