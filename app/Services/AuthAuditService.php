<?php

namespace App\Services;

use App\Models\Auth\AuthUser;
use Illuminate\Support\Facades\DB;

class AuthAuditService
{
    /**
     * action max 30 char (kolom auth_audit_logs.action)
     * module_code NOT NULL
     */
    public function log(string $action, ?AuthUser $user = null, array $extra = [], string $moduleCode = '00000'): void
    {
        $payload = [];

        // minimal sesuai request: identity_type + identity_key
        if ($user && $user->relationLoaded('identity') && $user->identity) {
            $payload['identity_type'] = (string) $user->identity->identity_type;
            $payload['identity_key'] = (string) $user->identity->identity_key;
        }

        // extra opsional (tetap sederhana)
        foreach ($extra as $k => $v) {
            $payload[$k] = $v;
        }

        try {
            DB::connection('mysql')->table('auth_audit_logs')->insert([
                'user_id' => $user ? (int) $user->id : null,
                'module_code' => $moduleCode,
                'action' => substr($action, 0, 30),
                'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'ip' => request()->ip(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // audit tidak boleh menggagalkan auth
        }
    }
}
