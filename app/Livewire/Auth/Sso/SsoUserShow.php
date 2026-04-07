<?php

namespace App\Livewire\Auth\Sso;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SsoUserShow extends Component
{
    public int $userId;

    public bool $canUpdate = false;

    public bool $canIdentityUpdate = false;

    public bool $canLock = false;

    public bool $canRoleAssign = false;

    public ?object $row = null;

    public array $roles = [];

    public function mount(int $userId): void
    {
        $this->userId = $userId;

        $u = auth()->user();
        $this->canUpdate = (bool) ($u?->hasPermission('SSO_USER_UPDATE') ?? false);
        $this->canIdentityUpdate = (bool) ($u?->hasPermission('SSO_IDENTITY_UPDATE') ?? false);
        $this->canLock = (bool) ($u?->hasPermission('SSO_USER_LOCK') ?? false);
        $this->canRoleAssign = (bool) ($u?->hasPermission('SSO_USER_ROLE_ASSIGN') ?? false);

        $this->loadData();
    }

    protected function loadData(): void
    {
        $this->row = DB::table('auth_users as u')
            ->join('auth_identities as i', 'i.id', '=', 'u.identity_id')
            ->leftJoin('holdings as h', 'h.id', '=', 'i.holding_id')
            ->leftJoin('departments as d', 'd.id', '=', 'i.department_id')
            ->leftJoin('divisions as dv', 'dv.id', '=', 'i.division_id')
            ->where('u.id', $this->userId)
            ->select([
                'u.*',
                'i.identity_type', 'i.identity_key', 'i.is_active',
                'h.name as holding_name', 'h.alias as holding_alias',
                'd.name as department_name',
                'dv.name as division_name',
            ])
            ->first();

        $this->roles = DB::table('auth_user_roles as ur')
            ->join('auth_roles as r', 'r.id', '=', 'ur.role_id')
            ->where('ur.auth_user_id', $this->userId)
            ->orderBy('r.code')
            ->get(['r.code', 'r.name'])
            ->map(fn ($r) => $r->code.' - '.$r->name)
            ->toArray();
    }

    public function render()
    {
        return view('livewire.auth.sso.sso-user-show');
    }
}
