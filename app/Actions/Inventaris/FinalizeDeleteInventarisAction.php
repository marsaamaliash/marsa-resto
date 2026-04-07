<?php

namespace App\Actions\Inventaris;

use App\Models\Auth\AuthApproval;
use App\Models\Auth\AuthUser;
use App\Models\Holdings\Hq\Sdm\Rt\Inventaris\Inventaris;

class FinalizeDeleteInventarisAction
{
    public function execute(AuthApproval $approval, AuthUser $approver): void
    {
        abort_unless($approval->module_code === '01005' && $approval->permission_code === 'INV_DELETE', 422);

        $payload = (array) ($approval->action_payload ?? []);
        $kodeLabel = (string) ($payload['kode_label'] ?? '');
        $reason = (string) ($payload['reason'] ?? '');

        abort_unless($kodeLabel !== '', 422, 'Payload invalid: kode_label missing.');

        $inv = Inventaris::where('kode_label', $kodeLabel)->lockForUpdate()->firstOrFail();

        if ($inv->deleted_at !== null) {
            abort(409, 'Item sudah di-soft-delete.');
        }
        if ($inv->lifecycle_status !== 'pending_delete') {
            abort(409, 'Lifecycle bukan pending_delete.');
        }

        $inv->update([
            'lifecycle_status' => 'inactive',
            'deleted_at' => now(),
            'deleted_by' => (int) $approver->id,
            'deleted_reason' => $reason ?: $inv->deleted_reason,
            'updated_by' => (int) $approver->id,
        ]);
    }
}
