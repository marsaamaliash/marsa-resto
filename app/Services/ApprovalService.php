<?php

namespace App\Services;

use App\Models\Auth\AuthApproval;
use App\Models\Auth\AuthUser;
use Illuminate\Support\Facades\DB;

class ApprovalService
{
    public function approveById(int $approvalId, AuthUser $approver): void
    {
        DB::transaction(function () use ($approvalId, $approver) {
            $approval = AuthApproval::query()->lockForUpdate()->findOrFail($approvalId);

            $this->assertPending($approval);
            $this->assertCanProcess($approval, $approver);

            app(ActionDispatcher::class)->dispatchApprove($approval, $approver);

            $approval->update([
                'status' => 'approved',
                'approved_by' => (int) $approver->id,
                'approved_at' => now(),
            ]);
        });
    }

    public function rejectById(int $approvalId, AuthUser $approver, string $reason): void
    {
        $reason = trim($reason);
        if ($reason === '' || mb_strlen($reason) > 255) {
            abort(422, 'Alasan reject wajib diisi (maks 255 karakter).');
        }

        DB::transaction(function () use ($approvalId, $approver, $reason) {
            $approval = AuthApproval::query()->lockForUpdate()->findOrFail($approvalId);

            $this->assertPending($approval);
            $this->assertCanProcess($approval, $approver);

            app(ActionDispatcher::class)->dispatchReject($approval, $approver, $reason);

            $approval->update([
                'status' => 'rejected',
                'approved_by' => (int) $approver->id,
                'approved_at' => now(),
                'rejected_reason' => $reason,
            ]);
        });
    }

    protected function assertPending(AuthApproval $approval): void
    {
        abort_unless($approval->status === 'pending', 409, 'Approval sudah diproses.');
    }

    protected function assertCanProcess(AuthApproval $approval, AuthUser $approver): void
    {
        abort_unless($approver->isActive(), 403, 'Akun Anda tidak aktif.');

        // optional: constraint role khusus
        if (! empty($approval->approver_role_id)) {
            $hasRole = DB::table('auth_user_roles')
                ->where('auth_user_id', (int) $approver->id)
                ->where('role_id', (int) $approval->approver_role_id)
                ->exists();

            abort_unless($hasRole, 403, 'Anda bukan approver role untuk request ini.');
        }

        // permission pattern: <PERM>_APPROVE atau generic APPROVAL_APPROVE
        $approvePerm = $approval->permission_code.'_APPROVE';
        $canApprove = $approver->hasPermission($approvePerm) || $approver->hasPermission('APPROVAL_APPROVE');
        abort_unless($canApprove, 403, 'Anda tidak punya izin approve/reject.');

        abort_unless(app(ApprovalScopeService::class)->inScope($approver, $approval), 403, 'Approval di luar scope Anda.');
    }
}
