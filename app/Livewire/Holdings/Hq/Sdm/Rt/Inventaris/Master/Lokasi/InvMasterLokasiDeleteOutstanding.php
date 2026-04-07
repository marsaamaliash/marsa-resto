<?php

namespace App\Livewire\Holdings\Hq\Sdm\Rt\Inventaris\Master\Lokasi;

use App\Models\Holdings\Hq\Sdm\Rt\Inventaris\InvMasterLokasiDeleteRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\WithPagination;

class InvMasterLokasiDeleteOutstanding extends Component
{
    use WithPagination;

    public array $breadcrumbs = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public string $search = '';

    public int $perPage = 10;

    public array $selected = []; // [id,id,...]

    public bool $selectAll = false;

    public bool $showRejectModal = false;

    public ?int $rejectingId = null;

    public string $rejectReason = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasPermission('INV_MASTER_LOKASI_DELETE_APPROVE'), 403);

        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard'],
            ['label' => 'Holding HQ', 'route' => 'dashboard.hq'],
            ['label' => 'SDM', 'route' => 'dashboard.sdm'],
            ['label' => 'Rumah Tangga', 'route' => 'dashboard.rt'],
            ['label' => 'Inventaris', 'route' => 'holdings.hq.sdm.rt.inventaris.inventaris-table'],
            ['label' => 'Master', 'route' => 'holdings.hq.sdm.rt.inventaris.master.index'],
            ['label' => 'Outstanding Delete - Lokasi'],
        ];
    }

    protected function baseQuery()
    {
        $s = trim($this->search);

        return InvMasterLokasiDeleteRequest::query()
            ->where('status', 'pending')
            ->when($s !== '', function ($q) use ($s) {
                $q->where(function ($sub) use ($s) {
                    $sub->where('holding_kode', 'like', "%{$s}%")
                        ->orWhere('lokasi_kode', 'like', "%{$s}%")
                        ->orWhere('reason', 'like', "%{$s}%");
                });
            })
            ->orderByDesc('requested_at');
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'perPage'], true)) {
            $this->resetPage();
        }
    }

    /* ===================== SELECTION ===================== */
    public function updatedSelectAll(bool $value): void
    {
        $visible = $this->baseQuery()
            ->paginate($this->perPage)
            ->pluck('id')
            ->map(fn ($v) => (int) $v)
            ->toArray();

        if ($value) {
            $this->selected = array_values(array_unique(array_merge($this->selected, $visible)));

            return;
        }

        $this->selected = array_values(array_diff($this->selected, $visible));
    }

    public function updatedSelected(): void
    {
        $visible = $this->baseQuery()
            ->paginate($this->perPage)
            ->pluck('id')
            ->map(fn ($v) => (int) $v)
            ->toArray();

        $this->selectAll = count($visible) > 0 && empty(array_diff($visible, $this->selected));
    }

    protected function resetSelection(): void
    {
        $this->selected = [];
        $this->selectAll = false;
    }

    /* ===================== APPROVE ===================== */
    public function approveOne(int $id): void
    {
        $this->approveMany([(int) $id]);
    }

    public function approveSelected(): void
    {
        if (empty($this->selected)) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Pilih request terlebih dahulu.'];

            return;
        }
        $this->approveMany(array_map('intval', $this->selected));
    }

    protected function approveMany(array $ids): void
    {
        $uid = (int) auth()->id();

        $ok = 0;
        $fail = 0;
        $failMessages = [];

        foreach ($ids as $id) {
            $id = (int) $id;

            try {
                DB::transaction(function () use ($id, $uid) {
                    $req = InvMasterLokasiDeleteRequest::lockForUpdate()->findOrFail($id);

                    if ($req->status !== 'pending') {
                        throw new \RuntimeException('Request sudah diproses.');
                    }

                    // hard/soft delete master lokasi
                    $q = DB::table('inv_lokasi')
                        ->where('holding_kode', $req->holding_kode)
                        ->where('kode', $req->lokasi_kode);

                    if ($q->exists()) {
                        if (Schema::hasColumn('inv_lokasi', 'deleted_at')) {
                            $q->update(['deleted_at' => now()]);
                        } else {
                            $q->delete();
                        }
                    }

                    $req->update([
                        'status' => 'approved',
                        'approved_by' => $uid,
                        'approved_at' => now(),
                    ]);
                });

                $ok++;
            } catch (\Throwable $e) {
                $fail++;
                $failMessages[] = "ID {$id}: ".$e->getMessage();
            }
        }

        $this->resetSelection();
        $this->resetPage();

        $msg = "Approve selesai: {$ok} request.";
        if ($fail > 0) {
            $msg .= " Gagal: {$fail}. Contoh: ".($failMessages[0] ?? 'unknown');
        }

        $this->toast = [
            'show' => true,
            'type' => $fail === 0 ? 'success' : 'warning',
            'message' => $msg,
        ];
    }

    /* ===================== REJECT ===================== */
    public function openRejectOne(int $id): void
    {
        $this->rejectingId = (int) $id;
        $this->rejectReason = '';
        $this->showRejectModal = true;
    }

    public function cancelReject(): void
    {
        $this->reset(['showRejectModal', 'rejectingId', 'rejectReason']);
    }

    public function submitReject(): void
    {
        if (! $this->rejectingId) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'ID reject tidak valid.'];

            return;
        }

        $uid = (int) auth()->id();
        $reason = trim($this->rejectReason);

        if (mb_strlen($reason) > 255) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Reject reason maks 255 karakter.'];

            return;
        }

        try {
            DB::transaction(function () use ($uid, $reason) {
                $req = InvMasterLokasiDeleteRequest::lockForUpdate()->findOrFail((int) $this->rejectingId);

                if ($req->status !== 'pending') {
                    throw new \RuntimeException('Request sudah diproses.');
                }

                $req->update([
                    'status' => 'rejected',
                    'rejected_by' => $uid,
                    'rejected_at' => now(),
                    'reject_reason' => $reason !== '' ? $reason : null,
                ]);
            });

            $this->cancelReject();

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Request berhasil di-reject.'];
        } catch (\Throwable $e) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => $e->getMessage()];
        }

        $this->resetSelection();
        $this->resetPage();
    }

    public function render()
    {
        $rows = $this->baseQuery()->paginate($this->perPage);

        $visible = $rows->pluck('id')->map(fn ($v) => (int) $v)->toArray();
        $this->selectAll = count($visible) > 0 && empty(array_diff($visible, $this->selected));

        return view('livewire.holdings.hq.sdm.rt.inventaris.master.lokasi.inv-master-lokasi-delete-outstanding', [
            'rows' => $rows,
            'breadcrumbs' => $this->breadcrumbs,
        ])->layout('components.sccr-layout');
    }
}
