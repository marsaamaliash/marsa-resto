<?php

namespace App\Livewire\Auth\Sso;

use App\Models\Auth\AuthUser;
use App\Services\Auth\AccessCloner;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SsoUserTable extends Component
{
    use WithPagination;

    /* ===================== UI GLOBAL ===================== */
    public array $breadcrumbs = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    /* ===================== CAPABILITIES ===================== */
    public bool $canWrite = false;

    public bool $canView = false;

    public bool $canCreate = false;

    public bool $canUpdate = false;

    public bool $canIdentityUpdate = false;

    public bool $canLock = false;

    public bool $canRoleAssign = false;

    public bool $canExport = false;

    public bool $canDeactivateRequest = false;

    // ✅ password reset capability
    public bool $canPasswordResetDirect = false;   // reset langsung user biasa (non-superadmin)

    public bool $canPasswordResetRequest = false;  // request approval reset super admin

    /* ===================== FILTER & SORT ===================== */
    public string $search = '';

    public string $filterType = '';      // employee|lecturer|student

    public string $filterHolding = '';   // holding_id

    public string $filterActive = '';    // 1|0

    public string $filterLocked = '';    // 1|0

    public string $filterRole = '';      // role_id

    public int $perPage = 10;

    public string $sortField = 'username';

    public string $sortDirection = 'asc';

    protected array $allowedSortFields = [
        'username',
        'email',
        'identity_type',
        'identity_key',
        'holding_name',
        'department_name',
        'division_name',
        'is_active',
        'is_locked',
        'last_login_at',
    ];

    /* ===================== SELECTION ===================== */
    public array $selected = []; // user_id list

    public bool $selectAll = false;

    /* ===================== DEACTIVATE REQUEST (ERP) ===================== */
    public bool $showConfirmModal = false;

    public ?int $confirmingUserId = null;

    public bool $isBulk = false;

    public string $reason = '';

    /* ===================== OVERLAY ===================== */
    public ?string $overlayMode = null; // null|'create'|'show'|'edit'|'roles'|'access'

    public ?int $overlayUserId = null;

    /* ===================== RESET PASSWORD (CONFIRM MODAL) ===================== */
    public bool $showResetConfirmModal = false;

    public ?int $resetTargetUserId = null;

    public string $resetTargetLabel = '';

    /* ===================== RESET PASSWORD SUPER ADMIN (APPROVAL MODAL) ===================== */
    public bool $showResetApprovalModal = false;

    public ?int $resetApprovalTargetUserId = null;

    public string $resetApprovalReason = '';

    public ?int $resetArmUserId = null;

    public int $resetArmExpiresAt = 0;

    /* ===================== BULK CLONE ACCESS ===================== */
    public bool $canCloneAccess = false;

    public bool $showCloneModal = false;

    public array $cloneUserOptions = [];     // [id => "username (email) · employee:identity_key"]

    public ?int $cloneFromUserId = null;

    public string $cloneMode = 'replace';     // replace|merge

    // ✅ preset cepat
    public string $clonePreset = 'all';       // all|roles_only|overrides_only

    // ✅ scope strategy lintas holding/identity
    public string $cloneScopeStrategy = 'rebase_to_target'; // keep|rebase_to_target|to_global

    // ✅ granular
    public bool $cloneRoles = true;

    public bool $cloneModuleOverrides = true;

    public bool $clonePermissionOverrides = true;

    public bool $cloneOnlyActiveOverrides = true;

    /* ===================== QUERY STRING ===================== */
    protected $queryString = [
        'search' => ['except' => ''],
        'filterType' => ['except' => ''],
        'filterHolding' => ['except' => ''],
        'filterActive' => ['except' => ''],
        'filterLocked' => ['except' => ''],
        'filterRole' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'username'],
        'sortDirection' => ['except' => 'asc'],
    ];

    /* ===================== CAPS ===================== */
    private function syncCaps(): void
    {
        $u = auth()->user();

        $this->canView = (bool) ($u?->hasPermission('SSO_USER_VIEW') ?? false);
        $this->canCreate = (bool) ($u?->hasPermission('SSO_USER_CREATE') ?? false);
        $this->canUpdate = (bool) ($u?->hasPermission('SSO_USER_UPDATE') ?? false);
        $this->canIdentityUpdate = (bool) ($u?->hasPermission('SSO_IDENTITY_UPDATE') ?? false);
        $this->canLock = (bool) ($u?->hasPermission('SSO_USER_LOCK') ?? false);
        $this->canRoleAssign = (bool) ($u?->hasPermission('SSO_USER_ROLE_ASSIGN') ?? false);
        $this->canExport = (bool) ($u?->hasPermission('SSO_USER_EXPORT') ?? false);
        $this->canDeactivateRequest = (bool) ($u?->hasPermission('SSO_USER_DEACTIVATE') ?? false);

        // ✅ reset password user biasa (direct)
        $this->canPasswordResetDirect = (bool) (
            ($u?->isSuperAdmin() ?? false) ||
            ($u?->hasPermission('SSO_USER_PASSWORD_RESET_DIRECT') ?? false)
        );

        // ✅ request reset password super admin (via approval)
        $this->canPasswordResetRequest = (bool) (
            ($u?->isSuperAdmin() ?? false) ||
            ($u?->hasPermission('SSO_USER_PASSWORD_RESET') ?? false)
        );

        // ✅ bulk clone capability
        $this->canCloneAccess = (bool) (
            ($u?->isSuperAdmin() ?? false) ||
            ($u?->hasPermission('SSO_USER_ROLE_ASSIGN') ?? false) ||
            ($u?->hasPermission('SSO_USER_UPDATE') ?? false)
        );

        $this->canWrite = $this->canCreate || $this->canUpdate || $this->canIdentityUpdate || $this->canLock || $this->canRoleAssign;
    }

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-white'],
            ['label' => 'Auth', 'route' => 'dashboard.sso', 'color' => 'text-white'],
            ['label' => 'SSO Users', 'color' => 'text-white font-semibold'],
        ];

        $this->syncCaps();

        if (! $this->canView) {
            abort(403, 'Forbidden');
        }

        // default clone settings organisasi
        $this->clonePreset = $this->defaultClonePreset();
        $this->cloneScopeStrategy = $this->defaultCloneScopeStrategy();
        $this->updatedClonePreset($this->clonePreset);
    }

    public function hydrate(): void
    {
        $this->syncCaps();
    }

    /* ===================== DEFAULT CLONE CONFIG (ORG) ===================== */
    protected function defaultClonePreset(): string
    {
        // all | roles_only | overrides_only
        return 'all';
    }

    protected function defaultCloneScopeStrategy(): string
    {
        // keep | rebase_to_target | to_global
        return 'rebase_to_target';
    }

    /* ===================== PRESET HOOK ===================== */
    public function updatedClonePreset(string $value): void
    {
        $v = in_array($value, ['all', 'roles_only', 'overrides_only'], true) ? $value : 'all';

        if ($v === 'roles_only') {
            $this->cloneRoles = true;
            $this->cloneModuleOverrides = false;
            $this->clonePermissionOverrides = false;

            return;
        }

        if ($v === 'overrides_only') {
            $this->cloneRoles = false;
            $this->cloneModuleOverrides = true;
            $this->clonePermissionOverrides = true;

            return;
        }

        // all
        $this->cloneRoles = true;
        $this->cloneModuleOverrides = true;
        $this->clonePermissionOverrides = true;
    }

    /* ===================== RESET ARM HELPERS ===================== */
    private function resetArmClear(): void
    {
        $this->resetArmUserId = null;
        $this->resetArmExpiresAt = 0;
    }

    private function resetArmIsActiveFor(int $userId): bool
    {
        if ($this->resetArmUserId === null) {
            return false;
        }
        if ((int) $this->resetArmUserId !== (int) $userId) {
            return false;
        }
        if ($this->resetArmExpiresAt <= 0) {
            return false;
        }

        if (time() > (int) $this->resetArmExpiresAt) {
            $this->resetArmClear();

            return false;
        }

        return true;
    }

    /* ===================== SMALL HELPERS ===================== */
    protected function userLabel(object $u): string
    {
        $email = (string) ($u->email ?? '');

        return (string) $u->username.($email !== '' ? " ({$email})" : '');
    }

    protected function closeAllModals(): void
    {
        // deactivate modal
        $this->reset(['showConfirmModal', 'confirmingUserId', 'isBulk', 'reason']);

        // reset password direct modal
        $this->reset(['showResetConfirmModal', 'resetTargetUserId', 'resetTargetLabel']);

        // reset password approval modal
        $this->reset(['showResetApprovalModal', 'resetApprovalTargetUserId', 'resetApprovalReason']);

        // ✅ clone modal
        $this->reset([
            'showCloneModal',
            'cloneFromUserId',
            'cloneMode',
            'clonePreset',
            'cloneScopeStrategy',
            'cloneRoles',
            'cloneModuleOverrides',
            'clonePermissionOverrides',
            'cloneOnlyActiveOverrides',
        ]);

        // default clone settings
        $this->cloneMode = 'replace';
        $this->clonePreset = $this->defaultClonePreset();
        $this->cloneScopeStrategy = $this->defaultCloneScopeStrategy();
        $this->updatedClonePreset($this->clonePreset);
        $this->cloneOnlyActiveOverrides = true;
    }

    /* ===================== CORE QUERY ===================== */
    protected function baseQuery()
    {
        $requested = (string) $this->sortField;
        $sortField = in_array($requested, $this->allowedSortFields, true) ? $requested : 'username';
        $sortDir = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        $q = DB::table('auth_users as u')
            ->join('auth_identities as i', 'i.id', '=', 'u.identity_id')
            ->leftJoin('holdings as h', 'h.id', '=', 'i.holding_id')
            ->leftJoin('departments as d', 'd.id', '=', 'i.department_id')
            ->leftJoin('divisions as dv', 'dv.id', '=', 'i.division_id')
            ->select([
                'u.id',
                'u.username',
                'u.email',
                'u.last_login_at',
                'u.is_locked',
                'u.is_super_admin',
                'u.is_super_scope',

                'i.id as identity_id',
                'i.identity_type',
                'i.identity_key',
                'i.is_active',
                'i.holding_id',
                'i.department_id',
                'i.division_id',

                DB::raw("COALESCE(h.name,'-') as holding_name"),
                DB::raw("COALESCE(h.alias,'-') as holding_alias"),
                DB::raw("COALESCE(d.name,'-') as department_name"),
                DB::raw("COALESCE(dv.name,'-') as division_name"),

                // ERP-ready: pending deactivate approvals count
                DB::raw("(SELECT COUNT(*) FROM auth_approvals ap
                          WHERE ap.auth_user_id = u.id
                            AND ap.module_code = '00000'
                            AND ap.permission_code = 'SSO_USER_DEACTIVATE'
                            AND ap.status = 'pending') as pending_deactivate"),
            ])
            ->when(trim($this->search) !== '', function ($qq) {
                $s = trim($this->search);
                $qq->where(function ($w) use ($s) {
                    $w->where('u.username', 'like', "%{$s}%")
                        ->orWhere('u.email', 'like', "%{$s}%")
                        ->orWhere('i.identity_key', 'like', "%{$s}%");
                });
            })
            ->when($this->filterType !== '', fn ($qq) => $qq->where('i.identity_type', $this->filterType))
            ->when($this->filterHolding !== '', fn ($qq) => $qq->where('i.holding_id', (int) $this->filterHolding))
            ->when($this->filterActive !== '', fn ($qq) => $qq->where('i.is_active', (int) $this->filterActive))
            ->when($this->filterLocked !== '', fn ($qq) => $qq->where('u.is_locked', (int) $this->filterLocked))
            ->when($this->filterRole !== '', function ($qq) {
                $rid = (int) $this->filterRole;
                $qq->whereExists(function ($sub) use ($rid) {
                    $sub->selectRaw('1')
                        ->from('auth_user_roles as ur')
                        ->whereColumn('ur.auth_user_id', 'u.id')
                        ->where('ur.role_id', $rid);
                });
            });

        // Sorting mapping (aliases)
        $sortMap = [
            'holding_name' => DB::raw("COALESCE(h.name,'')"),
            'department_name' => DB::raw("COALESCE(d.name,'')"),
            'division_name' => DB::raw("COALESCE(dv.name,'')"),
        ];

        if (isset($sortMap[$sortField])) {
            $q->orderBy($sortMap[$sortField], $sortDir);
        } else {
            $q->orderBy($sortField, $sortDir);
        }

        // Stabil
        $q->orderBy('u.id', 'desc');

        return $q;
    }

    protected function visibleIds(): array
    {
        $p = $this->baseQuery()->paginate($this->perPage);

        return $p->getCollection()->pluck('id')->map(fn ($x) => (int) $x)->toArray();
    }

    protected function rolesMapFor(array $userIds): array
    {
        if (empty($userIds)) {
            return [];
        }

        $rows = DB::table('auth_user_roles as ur')
            ->join('auth_roles as r', 'r.id', '=', 'ur.role_id')
            ->whereIn('ur.auth_user_id', $userIds)
            ->orderBy('r.code', 'asc')
            ->get(['ur.auth_user_id', 'r.id as role_id', 'r.code', 'r.name']);

        $map = [];
        foreach ($rows as $r) {
            $uid = (int) $r->auth_user_id;
            $map[$uid] ??= [];
            $map[$uid][] = [
                'id' => (int) $r->role_id,
                'code' => (string) $r->code,
                'name' => (string) $r->name,
            ];
        }

        return $map;
    }

    /* ===================== SORT ===================== */
    public function sortBy(string $field): void
    {
        $this->closeAllModals();

        if (! in_array($field, $this->allowedSortFields, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
            $this->resetPage();

            return;
        }

        $this->sortField = $field;
        $this->sortDirection = 'asc';
        $this->resetPage();
    }

    /* ===================== FILTER ===================== */
    public function applyFilter(): void
    {
        $this->closeAllModals();

        $this->resetPage();
        $this->selected = [];
        $this->selectAll = false;
    }

    public function clearFilters(): void
    {
        $this->closeAllModals();

        $this->reset(['search', 'filterType', 'filterHolding', 'filterActive', 'filterLocked', 'filterRole']);
        $this->applyFilter();
    }

    public function updated($property): void
    {
        // ✅ Jangan tutup modal untuk perubahan field di modal clone/reset/deactivate
        $ignore = [
            'cloneFromUserId', 'cloneMode', 'clonePreset', 'cloneScopeStrategy',
            'cloneRoles', 'cloneModuleOverrides', 'clonePermissionOverrides', 'cloneOnlyActiveOverrides',
            'showCloneModal',

            'showConfirmModal', 'confirmingUserId', 'isBulk', 'reason',
            'showResetConfirmModal', 'resetTargetUserId', 'resetTargetLabel',
            'showResetApprovalModal', 'resetApprovalTargetUserId', 'resetApprovalReason',
        ];

        if (in_array($property, $ignore, true)) {
            return;
        }

        // hanya close modal kalau yang berubah adalah filter/sort/pagination
        if (in_array($property, [
            'search', 'perPage', 'sortField', 'sortDirection',
            'filterType', 'filterHolding', 'filterActive', 'filterLocked', 'filterRole',
        ], true)) {
            $this->closeAllModals();
            $this->resetPage();
            $this->selected = [];
            $this->selectAll = false;
        }
    }

    /* ===================== SELECTION ===================== */
    public function updatedSelectAll(bool $value): void
    {
        $this->closeAllModals();

        $visible = $this->visibleIds();

        if ($value) {
            $this->selected = array_values(array_unique(array_merge($this->selected, $visible)));

            return;
        }

        $this->selected = array_values(array_diff($this->selected, $visible));
    }

    public function updatedSelected(): void
    {
        $this->closeAllModals();

        $visible = $this->visibleIds();
        $this->selectAll = count($visible) > 0 && empty(array_diff($visible, $this->selected));
    }

    /* ===================== ACTION: LOCK/UNLOCK ===================== */
    public function toggleLock(int $userId): void
    {
        $this->closeAllModals();

        if (! $this->canLock) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin lock/unlock.'];

            return;
        }

        $row = DB::table('auth_users')->where('id', $userId)->first(['id', 'is_locked', 'username', 'email', 'is_super_admin']);
        if (! $row) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'User tidak ditemukan.'];

            return;
        }

        // safety: super admin only by super admin
        $actor = auth()->user();
        if ((int) $row->is_super_admin === 1 && ! ($actor?->isSuperAdmin() ?? false)) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak boleh lock/unlock super admin.'];

            return;
        }

        $new = ((int) $row->is_locked === 1) ? 0 : 1;
        DB::table('auth_users')->where('id', $userId)->update(['is_locked' => $new, 'updated_at' => now()]);

        $label = $this->userLabel($row);

        $this->toast = [
            'show' => true,
            'type' => 'success',
            'message' => ($new ? 'User dikunci: ' : 'User dibuka: ').$label,
        ];
    }

    /* ===================== RESET PASSWORD (1 klik -> MODAL KONFIRMASI) ===================== */
    public function openResetPassword(int $userId): void
    {
        $this->closeAllModals();

        $target = DB::table('auth_users')->where('id', $userId)->first(['id', 'username', 'email', 'is_super_admin']);
        if (! $target) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'User tidak ditemukan.'];

            return;
        }

        // super admin → wajib approval
        if ((int) $target->is_super_admin === 1) {
            if (! $this->canPasswordResetRequest) {
                $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin request reset password super admin.'];

                return;
            }
            $this->openResetPasswordApproval((int) $target->id);

            return;
        }

        // non-superadmin → direct reset (butuh permission direct)
        if (! $this->canPasswordResetDirect) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin reset password.'];

            return;
        }

        $this->resetTargetUserId = (int) $target->id;
        $this->resetTargetLabel = $this->userLabel($target);
        $this->showResetConfirmModal = true;
    }

    public function cancelResetPassword(): void
    {
        $this->reset(['showResetConfirmModal', 'resetTargetUserId', 'resetTargetLabel']);
    }

    public function confirmResetPassword(): void
    {
        if (! $this->canPasswordResetDirect) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin reset password.'];

            return;
        }

        $uid = (int) ($this->resetTargetUserId ?? 0);
        if ($uid <= 0) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Target tidak valid.'];

            return;
        }

        try {
            DB::transaction(function () use ($uid) {
                $target = DB::table('auth_users')
                    ->where('id', $uid)
                    ->lockForUpdate()
                    ->first(['id', 'username', 'email', 'is_super_admin']);

                if (! $target) {
                    abort(404, 'User tidak ditemukan.');
                }

                // safety: non-superadmin only
                if ((int) $target->is_super_admin === 1) {
                    abort(422, 'Super admin harus lewat approval.');
                }

                $defaultPlain = 'password123';

                DB::table('auth_users')->where('id', $uid)->update([
                    'password' => Hash::make($defaultPlain),
                    'must_change_password' => 1,
                    'password_changed_at' => null,
                    'remember_token' => null,
                    'updated_at' => now(),
                ]);

                // revoke sanctum tokens
                DB::table('auth_personal_access_tokens')
                    ->where('tokenable_type', AuthUser::class)
                    ->where('tokenable_id', $uid)
                    ->delete();

                // audit log
                DB::table('auth_audit_logs')->insert([
                    'user_id' => (int) auth()->id(),
                    'module_code' => '00000',
                    'action' => 'SSO_USER_PASSWORD_RESET_DIRECT',
                    'payload' => json_encode(['target_user_id' => $uid], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'ip' => request()->ip(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

            $this->toast = [
                'show' => true,
                'type' => 'success',
                'message' => "Password berhasil di-reset untuk {$this->resetTargetLabel}. Default: password123 (wajib ganti saat login).",
            ];

            $this->cancelResetPassword();
            $this->resetPage();
        } catch (\Throwable $e) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => $e->getMessage()];
        }
    }

    /* ===================== RESET PASSWORD SUPER ADMIN (APPROVAL) ===================== */
    public function openResetPasswordApproval(int $userId): void
    {
        $this->reset(['showResetApprovalModal', 'resetApprovalTargetUserId', 'resetApprovalReason']);

        if (! $this->canPasswordResetRequest) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin request reset password super admin.'];

            return;
        }

        $target = DB::table('auth_users')->where('id', $userId)->first(['id', 'username', 'email', 'is_super_admin']);
        if (! $target) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'User tidak ditemukan.'];

            return;
        }

        if ((int) $target->is_super_admin !== 1) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Target bukan super admin. Gunakan reset langsung.'];

            return;
        }

        $this->resetApprovalTargetUserId = (int) $target->id;
        $this->resetApprovalReason = '';
        $this->showResetApprovalModal = true;
    }

    public function cancelResetPasswordApproval(): void
    {
        $this->reset(['showResetApprovalModal', 'resetApprovalTargetUserId', 'resetApprovalReason']);
    }

    public function submitResetPasswordApproval(): void
    {
        if (! $this->canPasswordResetRequest) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin request reset password super admin.'];

            return;
        }

        $uid = (int) ($this->resetApprovalTargetUserId ?? 0);
        if ($uid <= 0) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Target tidak valid.'];

            return;
        }

        $reason = trim($this->resetApprovalReason);
        if ($reason === '' || mb_strlen($reason) > 255) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Alasan wajib diisi (maks 255 karakter).'];

            return;
        }

        $target = DB::table('auth_users')->where('id', $uid)->first(['id', 'username', 'email', 'is_super_admin']);
        if (! $target) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'User tidak ditemukan.'];

            return;
        }
        if ((int) $target->is_super_admin !== 1) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Target bukan super admin.'];

            return;
        }

        try {
            $action = app(\App\Actions\Auth\RequestResetPasswordApprovalAction::class);
            $action->execute((int) $uid, $reason, (int) auth()->id());

            $label = $this->userLabel($target);

            $this->toast = [
                'show' => true,
                'type' => 'success',
                'message' => "Request reset password (Approval) dikirim untuk {$label}. Approver: DEV.",
            ];

            $this->cancelResetPasswordApproval();
            $this->resetPage();
        } catch (\Throwable $e) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => $e->getMessage()];
        }
    }

    /* ===================== ERP: REQUEST DEACTIVATE ===================== */
    public function openDeactivateRequestSingle(int $userId): void
    {
        $this->closeAllModals();

        if (! $this->canDeactivateRequest) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin request deactivate.'];

            return;
        }

        $this->confirmingUserId = $userId;
        $this->isBulk = false;
        $this->reason = '';
        $this->showConfirmModal = true;
    }

    public function openDeactivateRequestSelected(): void
    {
        $this->closeAllModals();

        if (! $this->canDeactivateRequest) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin request deactivate.'];

            return;
        }
        if (empty($this->selected)) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Pilih user terlebih dahulu.'];

            return;
        }

        $this->confirmingUserId = null;
        $this->isBulk = true;
        $this->reason = '';
        $this->showConfirmModal = true;
    }

    public function cancelDeactivateRequest(): void
    {
        $this->reset(['showConfirmModal', 'confirmingUserId', 'isBulk', 'reason']);
    }

    public function submitDeactivateRequest(): void
    {
        $this->closeAllModals();

        if (! $this->canDeactivateRequest) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin request deactivate.'];

            return;
        }

        $reason = trim($this->reason);
        if ($reason === '' || mb_strlen($reason) > 255) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Alasan wajib diisi (maks 255 karakter).'];

            return;
        }

        $action = app(\App\Actions\Auth\RequestDeactivateUserAction::class);
        $requesterId = (int) auth()->id();

        $ok = 0;
        $fail = 0;
        $failMsg = null;

        if (! $this->isBulk) {
            if (! $this->confirmingUserId) {
                $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Target tidak valid.'];

                return;
            }
            try {
                $action->execute((int) $this->confirmingUserId, $reason, $requesterId);
                $ok++;
            } catch (\Throwable $e) {
                $fail++;
                $failMsg = $e->getMessage();
            }

            $this->finishAfterDeactivateRequest($ok, $fail, $failMsg);

            return;
        }

        foreach ($this->selected as $uid) {
            try {
                $action->execute((int) $uid, $reason, $requesterId);
                $ok++;
            } catch (\Throwable $e) {
                $fail++;
                $failMsg ??= ((int) $uid).': '.$e->getMessage();
            }
        }

        $this->finishAfterDeactivateRequest($ok, $fail, $failMsg);
    }

    protected function finishAfterDeactivateRequest(int $ok, int $fail, ?string $failMsg): void
    {
        $this->cancelDeactivateRequest();
        $this->selected = [];
        $this->selectAll = false;
        $this->resetPage();

        $msg = "Request deactivate dikirim: {$ok} user.";
        if ($fail > 0) {
            $msg .= " Gagal: {$fail}. Contoh: ".($failMsg ?? 'unknown');
        }

        $this->toast = [
            'show' => true,
            'type' => $fail === 0 ? 'success' : 'warning',
            'message' => $msg,
        ];
    }

    public function openAccess(int $userId): void
    {
        $this->resetArmClear();

        if (! $this->canView) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Forbidden'];

            return;
        }

        $this->overlayMode = 'access';
        $this->overlayUserId = $userId;
    }

    /* ===================== EXPORT ===================== */
    public function exportFiltered()
    {
        $this->closeAllModals();

        if (! $this->canExport) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin export.'];

            return null;
        }

        $rows = $this->baseQuery()->get();

        return $this->generateExcel($rows, 'Filtered');
    }

    public function exportSelected()
    {
        $this->closeAllModals();

        if (! $this->canExport) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin export.'];

            return null;
        }

        if (empty($this->selected)) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Pilih user terlebih dahulu.'];

            return null;
        }

        $ids = array_values(array_unique(array_map('intval', $this->selected)));
        $rows = $this->baseQuery()->whereIn('u.id', $ids)->get();

        return $this->generateExcel($rows, 'Selected');
    }

    private function generateExcel($data, string $type)
    {
        $sheet = new Spreadsheet;
        $ws = $sheet->getActiveSheet();

        $ws->fromArray([[
            'User ID', 'Username', 'Email', 'Identity Type', 'Identity Key',
            'Holding', 'Department', 'Division', 'Identity Active', 'Locked', 'Last Login', 'Pending Deactivate',
        ]], null, 'A1');

        $row = 2;
        foreach ($data as $u) {
            $last = $u->last_login_at ? Carbon::parse($u->last_login_at)->format('Y-m-d H:i:s') : '';
            $ws->fromArray([
                (int) $u->id,
                (string) $u->username,
                (string) ($u->email ?? ''),
                (string) $u->identity_type,
                (string) $u->identity_key,
                (string) $u->holding_name,
                (string) $u->department_name,
                (string) $u->division_name,
                (int) $u->is_active,
                (int) $u->is_locked,
                (string) $last,
                (int) ($u->pending_deactivate ?? 0),
            ], null, 'A'.$row++);
        }

        $filename = "SSO_Users_{$type}_".now()->format('Ymd_His').'.xlsx';
        $writer = new Xlsx($sheet);
        $tmp = tempnam(sys_get_temp_dir(), 'SSO_');
        $writer->save($tmp);

        return response()->download($tmp, $filename)->deleteFileAfterSend(true);
    }

    /* ===================== OVERLAY CONTROL ===================== */
    public function openCreate(): void
    {
        $this->closeAllModals();

        if (! $this->canCreate) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin create.'];

            return;
        }

        $this->selected = [];
        $this->selectAll = false;

        $this->overlayMode = 'create';
        $this->overlayUserId = null;
    }

    public function openShow(int $userId): void
    {
        $this->closeAllModals();
        $this->overlayMode = 'show';
        $this->overlayUserId = $userId;
    }

    public function openEdit(int $userId): void
    {
        $this->closeAllModals();

        if (! $this->canUpdate && ! $this->canIdentityUpdate && ! $this->canLock) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin edit.'];

            return;
        }

        $this->overlayMode = 'edit';
        $this->overlayUserId = $userId;
    }

    public function openRoles(int $userId): void
    {
        $this->closeAllModals();

        if (! $this->canRoleAssign) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin assign role.'];

            return;
        }

        $this->overlayMode = 'roles';
        $this->overlayUserId = $userId;
    }

    public function closeOverlay(): void
    {
        $this->reset(['overlayMode', 'overlayUserId']);
    }

    /* ===================== EVENTS ===================== */
    #[On('sso-user-overlay-close')]
    public function handleOverlayClose(): void
    {
        $this->closeOverlay();
    }

    #[On('sso-user-created')]
    public function handleCreated(): void
    {
        $this->closeOverlay();
        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'SSO user berhasil dibuat/ditautkan.'];
        $this->resetPage();
    }

    #[On('sso-user-updated')]
    public function handleUpdated(): void
    {
        $this->closeOverlay();
        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'SSO user berhasil diperbarui.'];
        $this->resetPage();
    }

    #[On('sso-user-roles-updated')]
    public function handleRolesUpdated(): void
    {
        $this->closeOverlay();
        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Roles user berhasil diperbarui.'];
        $this->resetPage();
    }

    #[On('sso-user-open-edit')]
    public function handleOpenEditFromShow(int $userId): void
    {
        $this->openEdit($userId);
    }

    #[On('sso-user-open-roles')]
    public function handleOpenRolesFromShow(int $userId): void
    {
        $this->openRoles($userId);
    }

    #[\Livewire\Attributes\On('sso-user-access-updated')]
    public function handleAccessUpdated(int $userId = 0, string $username = '', string $email = ''): void
    {
        $this->closeOverlay();

        $label = trim($username.($email !== '' ? " ({$email})" : ''));

        $this->toast = [
            'show' => true,
            'type' => 'success',
            'message' => $label !== '' ? "Akses (roles) diperbarui untuk {$label}." : 'Akses (roles) diperbarui.',
        ];

        $this->resetPage();
    }

    /* ===================== OPTIONS ===================== */
    protected function holdingOptions(): array
    {
        return DB::table('holdings')
            ->orderBy('name')
            ->get(['id', 'name', 'alias'])
            ->mapWithKeys(fn ($h) => [(string) $h->id => ($h->name.($h->alias ? ' - '.$h->alias : ''))])
            ->toArray();
    }

    protected function roleOptions(): array
    {
        return DB::table('auth_roles')
            ->orderBy('code')
            ->get(['id', 'code', 'name'])
            ->mapWithKeys(fn ($r) => [(string) $r->id => ($r->code.' - '.$r->name)])
            ->toArray();
    }

    protected function getCloneUserOptions(): array
    {
        return DB::table('auth_users as u')
            ->join('auth_identities as i', 'i.id', '=', 'u.identity_id')
            ->orderBy('u.username')
            ->get(['u.id', 'u.username', 'u.email', 'i.identity_type', 'i.identity_key'])
            ->mapWithKeys(function ($r) {
                $label = (string) $r->username;
                if (! empty($r->email)) {
                    $label .= ' ('.$r->email.')';
                }
                $label .= ' · '.(string) $r->identity_type.':'.(string) $r->identity_key;

                return [(int) $r->id => $label];
            })
            ->toArray();
    }

    /* ===================== BULK CLONE UI ===================== */
    public function openCloneAccessSelected(): void
    {
        $this->closeAllModals();

        if (! $this->canCloneAccess) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin bulk clone access.'];

            return;
        }

        if (empty($this->selected)) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Pilih user target terlebih dahulu.'];

            return;
        }

        $this->cloneUserOptions = $this->getCloneUserOptions();

        $this->cloneFromUserId = null;
        $this->cloneMode = 'replace';

        $this->clonePreset = $this->defaultClonePreset();
        $this->cloneScopeStrategy = $this->defaultCloneScopeStrategy();
        $this->updatedClonePreset($this->clonePreset);

        $this->cloneOnlyActiveOverrides = true;
        $this->showCloneModal = true;
    }

    public function cancelCloneAccess(): void
    {
        $this->reset([
            'showCloneModal',
            'cloneFromUserId',
            'cloneMode',
            'clonePreset',
            'cloneScopeStrategy',
            'cloneRoles',
            'cloneModuleOverrides',
            'clonePermissionOverrides',
            'cloneOnlyActiveOverrides',
        ]);

        $this->cloneMode = 'replace';
        $this->clonePreset = $this->defaultClonePreset();
        $this->cloneScopeStrategy = $this->defaultCloneScopeStrategy();
        $this->updatedClonePreset($this->clonePreset);
        $this->cloneOnlyActiveOverrides = true;
    }

    public function submitCloneAccess(): void
    {
        if (! $this->canCloneAccess) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin bulk clone access.'];

            return;
        }

        $src = (int) ($this->cloneFromUserId ?? 0);
        if ($src <= 0 || empty($this->cloneUserOptions) || ! isset($this->cloneUserOptions[$src])) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Pilih source user (template) terlebih dahulu.'];

            return;
        }

        $targets = array_values(array_unique(array_map('intval', $this->selected)));
        if (empty($targets)) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Target kosong.'];

            return;
        }

        // jangan clone ke diri sendiri
        $targets = array_values(array_filter($targets, fn ($id) => (int) $id !== $src));
        if (empty($targets)) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Target hanya source user, tidak ada yang perlu di-clone.'];

            return;
        }

        $mode = in_array($this->cloneMode, ['replace', 'merge'], true) ? $this->cloneMode : 'replace';
        $preset = in_array($this->clonePreset, ['all', 'roles_only', 'overrides_only'], true) ? $this->clonePreset : 'all';
        $scopeStrategy = in_array($this->cloneScopeStrategy, ['keep', 'rebase_to_target', 'to_global'], true)
            ? $this->cloneScopeStrategy
            : 'rebase_to_target';

        $ok = 0;
        $fail = 0;
        $skip = 0;
        $firstFail = null;

        $actor = auth()->user();
        $actorId = (int) auth()->id();

        foreach ($targets as $tid) {
            try {
                // safety: super admin target hanya boleh oleh super admin
                $isTargetSuper = (int) DB::table('auth_users')->where('id', $tid)->value('is_super_admin');
                if ($isTargetSuper === 1 && ! ($actor?->isSuperAdmin() ?? false)) {
                    $skip++;
                    $firstFail ??= "Skip user {$tid}: target super admin.";

                    continue;
                }

                $res = AccessCloner::cloneOne($src, $tid, [
                    'mode' => $mode,

                    // ✅ baru
                    'preset' => $preset,
                    'scope_strategy' => $scopeStrategy,

                    // advanced switches (tetap dikirim)
                    'clone_roles' => (bool) $this->cloneRoles,
                    'clone_module_overrides' => (bool) $this->cloneModuleOverrides,
                    'clone_permission_overrides' => (bool) $this->clonePermissionOverrides,
                    'only_active_overrides' => (bool) $this->cloneOnlyActiveOverrides,

                    'actor_user_id' => $actorId,
                ]);

                if (($res['ok'] ?? false) === true) {
                    $ok++;
                } else {
                    $fail++;
                    $firstFail ??= "Fail user {$tid}: ".($res['message'] ?? 'unknown');
                }
            } catch (\Throwable $e) {
                $fail++;
                $firstFail ??= "Fail user {$tid}: ".$e->getMessage();
            }
        }

        // audit 1x (bulk)
        try {
            DB::table('auth_audit_logs')->insert([
                'user_id' => $actorId,
                'module_code' => '00000',
                'action' => 'SSO_ACCESS_CLONE_BULK',
                'payload' => json_encode([
                    'source_user_id' => $src,
                    'target_user_ids' => array_slice($targets, 0, 200),
                    'mode' => $mode,
                    'preset' => $preset,
                    'scope_strategy' => $scopeStrategy,
                    'clone_roles' => (int) $this->cloneRoles,
                    'clone_module_overrides' => (int) $this->cloneModuleOverrides,
                    'clone_permission_overrides' => (int) $this->clonePermissionOverrides,
                    'only_active_overrides' => (int) $this->cloneOnlyActiveOverrides,
                    'result' => ['ok' => $ok, 'fail' => $fail, 'skip' => $skip, 'sample_error' => $firstFail],
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'ip' => request()->ip(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // audit gagal jangan bikin proses utama gagal
        }

        $this->cancelCloneAccess();

        // refresh UI
        $this->selected = [];
        $this->selectAll = false;
        $this->resetPage();

        $msg = "Bulk Clone selesai. OK: {$ok}, Skip: {$skip}, Fail: {$fail}.";
        if ($firstFail) {
            $msg .= " Contoh: {$firstFail}";
        }

        $this->toast = [
            'show' => true,
            'type' => ($fail === 0 ? 'success' : 'warning'),
            'message' => $msg,
        ];
    }

    public function render()
    {
        $rows = $this->baseQuery()->paginate($this->perPage);

        $visible = $rows->getCollection()
            ->pluck('id')
            ->map(fn ($x) => (int) $x)
            ->toArray();

        $this->selectAll = count($visible) > 0 && empty(array_diff($visible, $this->selected));

        $rolesMap = $this->rolesMapFor($visible);

        if ($this->showCloneModal && empty($this->cloneUserOptions)) {
            $this->cloneUserOptions = $this->getCloneUserOptions();
        }

        return view('livewire.auth.sso.sso-user-table', [
            'breadcrumbs' => $this->breadcrumbs,
            'rows' => $rows,
            'holdingOptions' => $this->holdingOptions(),
            'roleOptions' => $this->roleOptions(),
            'rolesMap' => $rolesMap,
        ])->layout('components.sccr-layout');
    }
}
