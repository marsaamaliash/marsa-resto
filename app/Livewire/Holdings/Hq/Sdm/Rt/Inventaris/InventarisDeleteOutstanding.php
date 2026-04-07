<?php

namespace App\Livewire\Holdings\Hq\Sdm\Rt\Inventaris;

use App\Actions\Inventaris\ApproveDeleteInventarisAction;
use App\Actions\Inventaris\RejectDeleteInventarisAction;
use App\Models\Auth\AuthApproval;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class InventarisDeleteOutstanding extends Component
{
    use WithPagination;

    public array $breadcrumbs = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public string $search = '';

    public int $perPage = 10;

    // modal reject
    public bool $showRejectModal = false;

    public ?int $rejectingApprovalId = null;

    public string $rejectReason = '';

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard'],
            ['label' => 'Holding HQ', 'route' => 'dashboard.hq'],
            ['label' => 'SDM', 'route' => 'dashboard.sdm'],
            ['label' => 'Rumah Tangga', 'route' => 'dashboard.rt'],
            ['label' => 'Inventaris', 'route' => 'holdings.hq.sdm.rt.inventaris.inventaris-table'],
            ['label' => 'Delete Outstanding'],
        ];
    }

    protected function query()
    {
        $q = AuthApproval::query()
            ->with(['requester'])
            ->where('auth_approvals.module_code', '01005')
            ->where('auth_approvals.permission_code', 'INV_DELETE')
            ->where('auth_approvals.status', 'pending')

            // join inventaris untuk ambil nama_barang
            ->leftJoin('inventaris as inv', function ($join) {
                $join->on(
                    'inv.kode_label',
                    '=',
                    DB::raw("JSON_UNQUOTE(JSON_EXTRACT(auth_approvals.action_payload, '$.kode_label'))")
                );
            })

            // select approvals + nama_barang
            ->select('auth_approvals.*', 'inv.nama_barang as nama_barang')
            ->orderByDesc('auth_approvals.created_at');

        // SEARCH
        $s = trim($this->search);
        if ($s !== '') {
            $q->where(function ($sub) use ($s) {
                $sub->where('auth_approvals.id', 'like', "%{$s}%")
                    ->orWhere('auth_approvals.auth_user_id', 'like', "%{$s}%")
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(auth_approvals.action_payload, '$.kode_label')) LIKE ?", ["%{$s}%"])
                    ->orWhere('inv.nama_barang', 'like', "%{$s}%")
                    ->orWhereHas('requester', fn ($rq) => $rq->where('username', 'like', "%{$s}%"));
            });
        }

        return $q;
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'perPage'], true)) {
            $this->resetPage();
        }
    }

    public function approve(int $approvalId, ApproveDeleteInventarisAction $action): void
    {
        $approval = AuthApproval::where('id', $approvalId)
            ->where('status', 'pending')
            ->firstOrFail();

        $action->execute($approval, (int) auth()->id());

        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Permintaan delete berhasil di-approve.'];
        $this->resetPage();
    }

    public function openReject(int $approvalId): void
    {
        $this->rejectingApprovalId = $approvalId;
        $this->rejectReason = '';
        $this->showRejectModal = true;
    }

    public function closeReject(): void
    {
        $this->showRejectModal = false;
        $this->rejectingApprovalId = null;
        $this->rejectReason = '';
    }

    public function submitReject(RejectDeleteInventarisAction $action): void
    {
        $this->validate([
            'rejectingApprovalId' => ['required', 'integer'],
            'rejectReason' => ['required', 'string', 'max:255'],
        ]);

        $approval = AuthApproval::where('id', $this->rejectingApprovalId)
            ->where('status', 'pending')
            ->firstOrFail();

        $action->execute($approval, (int) auth()->id(), $this->rejectReason);

        $this->closeReject();

        $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Permintaan delete ditolak.'];
        $this->resetPage();
    }

    public function render()
    {
        $data = $this->query()->paginate($this->perPage);

        return view('livewire.holdings.hq.sdm.rt.inventaris.inventaris-delete-outstanding', [
            'data' => $data,
            'breadcrumbs' => $this->breadcrumbs,
        ])->layout('components.sccr-layout');
    }
}
