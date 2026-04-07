<?php

namespace App\Actions\Inventaris;

use App\Models\Auth\AuthApproval;
use App\Models\Auth\AuthIdentity;
use App\Models\Holdings\Hq\Sdm\Rt\Inventaris\Inventaris;
use Illuminate\Support\Facades\DB;

class ApproveDeleteInventarisAction
{
    public function execute(AuthApproval $approval, int $approverUserId): void
    {
        $user = auth()->user();

        // double-check auth identity
        if (! $user || (int) $user->id !== (int) $approverUserId) {
            abort(403, 'Unauthorized approver.');
        }

        // permission check (prefer INV_DELETE_APPROVE; fallback to INV_DELETE)
        $canApprove = $user->hasPermission('INV_DELETE_APPROVE') || $user->hasPermission('INV_DELETE');
        if (! $canApprove) {
            abort(403, 'No permission to approve delete.');
        }

        DB::transaction(function () use ($approval, $approverUserId) {

            // lock approval row from DB to avoid stale object
            $lockedApproval = AuthApproval::where('id', $approval->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedApproval->status !== 'pending') {
                abort(409, 'Approval sudah diproses.');
            }

            if ($lockedApproval->module_code !== '01005' || $lockedApproval->permission_code !== 'INV_DELETE') {
                abort(422, 'Approval bukan untuk delete inventaris.');
            }

            $payload = $lockedApproval->action_payload ?? [];
            $kodeLabel = $payload['kode_label'] ?? null;
            $reason = $payload['reason'] ?? null;

            if (! $kodeLabel) {
                abort(422, 'Payload invalid: kode_label missing.');
            }

            // lock inventaris row
            $inv = Inventaris::where('kode_label', $kodeLabel)
                ->lockForUpdate()
                ->firstOrFail();

            // consistency checks
            if ($inv->deleted_at !== null) {
                abort(409, 'Item sudah di-soft-delete.');
            }

            if ($inv->lifecycle_status !== 'pending_delete') {
                abort(409, 'Lifecycle tidak pending_delete. Tidak bisa approve.');
            }

            // update approval as approved
            $lockedApproval->update([
                'status' => 'approved',
                'approved_by' => $approverUserId,
                'approved_at' => now(),
            ]);

            // final soft delete (use update to avoid timestamps dependency)
            $inv->update([
                'lifecycle_status' => 'inactive',
                'deleted_at' => now(),
                'deleted_by' => $approverUserId,
                'deleted_reason' => $reason ?: $inv->deleted_reason,
                'updated_by' => $approverUserId,
            ]);
        });
    }

    protected function assertApprovalScope(AuthApproval $approval, int $approverUserId): void
    {
        $approverIdentity = AuthIdentity::where('auth_user_id', $approverUserId)
            ->where('is_active', 1)
            ->first();

        if (! $approverIdentity) {
            abort(403, 'Approver identity not found.');
        }

        $requesterIdentity = AuthIdentity::where('auth_user_id', $approval->auth_user_id)
            ->where('is_active', 1)
            ->first();

        if (! $requesterIdentity) {
            abort(403, 'Requester identity not found.');
        }

        // Ambil scope role_modules yang berlaku untuk approver (module inventaris 01005)
        $scopes = DB::table('auth_user_roles as ur')
            ->join('auth_role_modules as rm', 'rm.role_id', '=', 'ur.role_id')
            ->where('ur.auth_user_id', $approverUserId)
            ->where('rm.module_code', '01005')
            ->where('rm.is_active', 1)
            ->get(['rm.scope_type', 'rm.scope_department_id', 'rm.scope_division_id']);

        // jika ada GLOBAL (scope_type null) → allow
        if ($scopes->contains(fn ($s) => $s->scope_type === null)) {
            return;
        }

        $allowed = $scopes->contains(function ($s) use ($approverIdentity, $requesterIdentity) {
            if ($s->scope_type === 'division') {
                return (int) $approverIdentity->division_id === (int) $requesterIdentity->division_id
                    && (int) $s->scope_division_id === (int) $approverIdentity->division_id;
            }

            if ($s->scope_type === 'department') {
                return (int) $approverIdentity->department_id === (int) $requesterIdentity->department_id
                    && (int) $s->scope_department_id === (int) $approverIdentity->department_id;
            }

            return false;
        });

        if (! $allowed) {
            abort(403, 'Out of scope approval.');
        }
    }
}
