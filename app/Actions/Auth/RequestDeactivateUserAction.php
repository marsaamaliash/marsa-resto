<?php

namespace App\Actions\Auth;

use Illuminate\Support\Facades\DB;

class RequestDeactivateUserAction
{
    /**
     * Create approval record for deactivating a user identity (ERP workflow).
     * Does NOT change auth_users/auth_identities immediately.
     */
    public function execute(int $targetUserId, string $reason, int $requestedByUserId): void
    {
        $reason = trim($reason);
        if ($reason === '' || mb_strlen($reason) > 255) {
            throw new \InvalidArgumentException('Reason wajib diisi (maks 255).');
        }

        $target = DB::table('auth_users as u')
            ->join('auth_identities as i', 'i.id', '=', 'u.identity_id')
            ->where('u.id', $targetUserId)
            ->select(['u.id', 'u.username', 'u.email', 'i.id as identity_id', 'i.identity_type', 'i.identity_key'])
            ->first();

        if (! $target) {
            throw new \RuntimeException('Target user tidak ditemukan.');
        }

        // block duplicate pending
        $pending = DB::table('auth_approvals')
            ->where('auth_user_id', $targetUserId)
            ->where('module_code', '00000')
            ->where('permission_code', 'SSO_USER_DEACTIVATE')
            ->where('status', 'pending')
            ->exists();

        if ($pending) {
            throw new \RuntimeException('Sudah ada request pending untuk user ini.');
        }

        $payload = [
            'target_user_id' => (int) $target->id,
            'target_username' => (string) $target->username,
            'target_email' => (string) ($target->email ?? ''),
            'identity_id' => (int) $target->identity_id,
            'identity_type' => (string) $target->identity_type,
            'identity_key' => (string) $target->identity_key,
            'reason' => $reason,
            'requested_by' => $requestedByUserId,
            'requested_at' => now()->format('Y-m-d H:i:s'),
        ];

        DB::table('auth_approvals')->insert([
            'auth_user_id' => $targetUserId,
            'approver_role_id' => null, // bisa diisi jika kamu punya mapping approver per scope/role
            'module_code' => '00000',
            'permission_code' => 'SSO_USER_DEACTIVATE',
            'action_payload' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
            'rejected_reason' => null,
            'created_at' => now(),
        ]);
    }
}
