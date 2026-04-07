<?php

namespace App\Services;

use App\Models\Auth\AuthApproval;
use App\Models\Auth\AuthUser;

class ActionDispatcher
{
    /** @var array<string, class-string> */
    protected array $approveMap = [
        '01005:INV_DELETE' => \App\Actions\Inventaris\FinalizeDeleteInventarisAction::class,
        '00000:SSO_USER_DEACTIVATE' => \App\Actions\Sso\DeactivateAuthUserAction::class,
        '00000:SSO_USER_PASSWORD_RESET' => \App\Actions\Sso\ResetAuthUserPasswordAction::class,
    ];

    /** @var array<string, class-string> */
    protected array $rejectMap = [
        '01005:INV_DELETE' => \App\Actions\Inventaris\CancelDeleteInventarisAction::class,
        // reject SSO deactivate: optional (biasanya tidak perlu side-effect)
    ];

    public function dispatchApprove(AuthApproval $approval, AuthUser $approver): void
    {
        $key = "{$approval->module_code}:{$approval->permission_code}";
        abort_unless(isset($this->approveMap[$key]), 500, "No approve action for {$key}");
        app($this->approveMap[$key])->execute($approval, $approver);
    }

    public function dispatchReject(AuthApproval $approval, AuthUser $approver, string $reason): void
    {
        $key = "{$approval->module_code}:{$approval->permission_code}";
        if (! isset($this->rejectMap[$key])) {
            return;
        }
        app($this->rejectMap[$key])->execute($approval, $approver, $reason);
    }
}
