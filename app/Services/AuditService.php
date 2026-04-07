<?php

namespace App\Services;

class AuditService
{
    public function log(string $action, string $module, array $payload = []): void
    {
        \DB::table('auth_audits')->insert([
            'auth_user_id' => auth()->id(),
            'action' => $action,
            'module_code' => $module,
            'payload' => json_encode($payload),
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);
    }
}
