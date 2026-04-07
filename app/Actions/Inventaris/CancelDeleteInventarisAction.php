<?php

namespace App\Actions\Inventaris;

use App\Models\Auth\AuthApproval;
use App\Models\Auth\AuthUser;
use App\Models\Holdings\Hq\Sdm\Rt\Inventaris\Inventaris;

class CancelDeleteInventarisAction
{
    public function execute(AuthApproval $approval, AuthUser $approver, string $rejectReason): void
    {
        abort_unless($approval->module_code === '01005' && $approval->permission_code === 'INV_DELETE', 422);

        $payload = (array) ($approval->action_payload ?? []);
        $kodeLabel = (string) ($payload['kode_label'] ?? '');

        abort_unless($kodeLabel !== '', 422, 'Payload invalid: kode_label missing.');

        $inv = Inventaris::where('kode_label', $kodeLabel)->lockForUpdate()->firstOrFail();

        if ($inv->deleted_at === null && $inv->lifecycle_status === 'pending_delete') {
            $inv->update([
                'lifecycle_status' => 'active',
                'updated_by' => (int) $approver->id,
                'deleted_reason' => null,
            ]);
        }
    }
}
