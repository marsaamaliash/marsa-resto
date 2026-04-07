<?php

namespace App\Services;

use App\Models\Auth\AuthApproval;
use App\Models\Auth\AuthIdentity;
use App\Models\Auth\AuthUser;
use Illuminate\Support\Facades\DB;

class ApprovalScopeService
{
    public function inScope(AuthUser $approver, AuthApproval $approval): bool
    {
        if ($approver->isSuperAdmin()) {
            return true;
        }

        $approverIdentity = $approver->identity;
        if (! $approverIdentity || ! $approverIdentity->is_active) {
            return false;
        }

        $requester = $approval->requester()->first();
        $requesterIdentity = $requester?->identity;

        // untuk history: requester bisa resign tapi identity masih ada
        if (! $requesterIdentity) {
            $requesterIdentity = AuthIdentity::query()
                ->where('auth_user_id', (int) $approval->auth_user_id)
                ->orderByDesc('id')
                ->first();
        }
        if (! $requesterIdentity) {
            return false;
        }

        $moduleCode = (string) $approval->module_code;

        $scopes = DB::table('auth_user_roles as ur')
            ->join('auth_role_modules as rm', 'rm.role_id', '=', 'ur.role_id')
            ->where('ur.auth_user_id', (int) $approver->id)
            ->where('rm.module_code', $moduleCode)
            ->where('rm.is_active', 1)
            ->get([
                'rm.scope_type',
                'rm.scope_holding_id',
                'rm.scope_department_id',
                'rm.scope_division_id',
            ]);

        if ($scopes->isEmpty()) {
            return false;
        }

        // GLOBAL scope_type NULL => allow
        if ($scopes->contains(fn ($s) => $s->scope_type === null)) {
            return true;
        }

        foreach ($scopes as $s) {
            if ($s->scope_type === 'holding') {
                if ((int) $s->scope_holding_id > 0
                    && (int) $requesterIdentity->holding_id === (int) $s->scope_holding_id
                    && (int) $approverIdentity->holding_id === (int) $s->scope_holding_id) {
                    return true;
                }
            }

            if ($s->scope_type === 'department') {
                if ((int) $s->scope_department_id > 0
                    && (int) $requesterIdentity->department_id === (int) $s->scope_department_id
                    && (int) $approverIdentity->department_id === (int) $s->scope_department_id) {
                    return true;
                }
            }

            if ($s->scope_type === 'division') {
                if ((int) $s->scope_division_id > 0
                    && (int) $requesterIdentity->division_id === (int) $s->scope_division_id
                    && (int) $approverIdentity->division_id === (int) $s->scope_division_id) {
                    return true;
                }
            }
        }

        return false;
    }
}
