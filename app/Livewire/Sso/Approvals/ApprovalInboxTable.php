<?php

namespace App\Livewire\Sso\Approvals;

use App\Models\Auth\AuthApproval;
use App\Services\ApprovalService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ApprovalInboxTable extends Component
{
    use WithPagination;

    public array $breadcrumbs = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public string $search = '';

    public string $status = 'pending';

    public string $moduleCode = '';

    public string $permissionCode = '';

    public int $perPage = 10;

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public bool $showRejectModal = false;

    public ?int $rejectingId = null;

    public string $rejectReason = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => 'pending'],
        'moduleCode' => ['except' => ''],
        'permissionCode' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-white'],
            ['label' => 'SSO Governance', 'route' => 'dashboard.sso', 'color' => 'text-white font-semibold'],
            ['label' => 'Approval Inbox', 'color' => 'text-white font-semibold'],
        ];
    }

    protected function myRoleIds(): array
    {
        $u = auth()->user();
        if (! $u) {
            return [];
        }

        return DB::table('auth_user_roles')
            ->where('auth_user_id', (int) $u->id)
            ->pluck('role_id')
            ->map(fn ($x) => (int) $x)
            ->toArray();
    }

    protected function approvalsQuery()
    {
        $u = auth()->user();
        abort_unless($u, 401);

        $sortField = in_array($this->sortField, ['created_at', 'status', 'module_code', 'permission_code'], true)
            ? $this->sortField
            : 'created_at';

        $sortDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        $roleIds = $this->myRoleIds();

        return AuthApproval::query()
            ->with(['requester.identity'])
            // ✅ scope approver_role_id
            ->when(! $u->isSuperAdmin(), function ($q) use ($roleIds) {
                $q->where(function ($w) use ($roleIds) {
                    $w->whereNull('approver_role_id');
                    if (! empty($roleIds)) {
                        $w->orWhereIn('approver_role_id', $roleIds);
                    }
                });
            })
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->when($this->moduleCode !== '', fn ($q) => $q->where('module_code', $this->moduleCode))
            ->when($this->permissionCode !== '', fn ($q) => $q->where('permission_code', $this->permissionCode))
            ->when($this->search !== '', function ($q) {
                $s = $this->search;

                $q->where(function ($sub) use ($s) {
                    $sub->where('module_code', 'like', "%{$s}%")
                        ->orWhere('permission_code', 'like', "%{$s}%")
                        ->orWhereRaw("JSON_EXTRACT(action_payload, '$.kode_label') LIKE ?", ["%{$s}%"])
                        ->orWhereRaw("JSON_EXTRACT(action_payload, '$.target_user_id') LIKE ?", ["%{$s}%"]);
                });
            })
            ->orderBy($sortField, $sortDirection);
    }

    protected function assertScope(AuthApproval $a): void
    {
        $u = auth()->user();
        abort_unless($u, 401);

        if ($u->isSuperAdmin()) {
            return;
        }

        if ($a->approver_role_id === null) {
            return;
        }

        $my = $this->myRoleIds();
        abort_unless(in_array((int) $a->approver_role_id, $my, true), 403, 'Out of scope approval.');
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, ['created_at', 'status', 'module_code', 'permission_code'], true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';

            return;
        }

        $this->sortField = $field;
        $this->sortDirection = 'asc';
        $this->resetPage();
    }

    public function updated($prop): void
    {
        if (in_array($prop, ['search', 'status', 'moduleCode', 'permissionCode', 'perPage'], true)) {
            $this->resetPage();
        }
    }

    public function approve(int $approvalId, ApprovalService $service): void
    {
        try {
            $a = AuthApproval::findOrFail($approvalId);
            $this->assertScope($a);

            $service->approveById($approvalId, auth()->user());

            $this->toast = [
                'show' => true,
                'type' => 'success',
                'message' => "Approval #{$approvalId} berhasil di-approve ({$a->module_code}:{$a->permission_code}).",
            ];
            $this->resetPage();
        } catch (\Throwable $e) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => $e->getMessage()];
        }
    }

    public function openReject(int $approvalId): void
    {
        $this->rejectingId = $approvalId;
        $this->rejectReason = '';
        $this->showRejectModal = true;
    }

    public function cancelReject(): void
    {
        $this->reset(['showRejectModal', 'rejectingId', 'rejectReason']);
    }

    public function submitReject(ApprovalService $service): void
    {
        if (! $this->rejectingId) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Approval ID tidak valid.'];

            return;
        }

        try {
            $a = AuthApproval::findOrFail($this->rejectingId);
            $this->assertScope($a);

            $service->rejectById($this->rejectingId, auth()->user(), $this->rejectReason);

            $this->toast = [
                'show' => true,
                'type' => 'success',
                'message' => "Approval #{$this->rejectingId} berhasil di-reject ({$a->module_code}:{$a->permission_code}).",
            ];
            $this->cancelReject();
            $this->resetPage();
        } catch (\Throwable $e) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => $e->getMessage()];
        }
    }

    public function payloadSummary(AuthApproval $a): string
    {
        $p = (array) ($a->action_payload ?? []);

        if ($a->module_code === '01005' && $a->permission_code === 'INV_DELETE') {
            return 'kode_label: '.($p['kode_label'] ?? '-');
        }

        if ($a->module_code === '00000' && $a->permission_code === 'SSO_USER_DEACTIVATE') {
            return 'target_user_id: '.($p['target_user_id'] ?? '-');
        }

        if ($a->module_code === '00000' && $a->permission_code === 'SSO_USER_PASSWORD_RESET') {
            return 'target_user_id: '.($p['target_user_id'] ?? '-');
        }

        return json_encode($p, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '-';
    }

    public function render()
    {
        $approvals = $this->approvalsQuery()->paginate($this->perPage);

        return view('livewire.sso.approvals.approval-inbox-table', [
            'approvals' => $approvals,
            'breadcrumbs' => $this->breadcrumbs,
        ])->layout('components.sccr-layout');
    }
}
