<?php

namespace App\Services;

use App\Models\Auth\AuthUser;
use Illuminate\Support\Facades\DB;

class ApprovalResolver
{
    /**
     * Tentukan role approver berdasarkan permission & requester
     */
    public function resolveApproverRole(
        AuthUser $requester,
        string $permissionCode,
        string $moduleCode
    ): int {
        // DEV bypass (seharusnya tidak sampai sini)
        if ($requester->isSuperAdmin()) {
            abort(400, 'DEV tidak memerlukan approval');
        }

        $identity = $requester->identity;

        abort_unless($identity, 403, 'Identity tidak ditemukan');

        /**
         * RULE: INV_DELETE → HEAD dari DIVISION requester
         */
        if ($permissionCode === 'INV_DELETE') {
            return DB::table('auth_roles')
                ->where('code', 'HEAD')
                ->value('id');
        }

        abort(400, 'Approval rule tidak ditemukan');
    }
}
