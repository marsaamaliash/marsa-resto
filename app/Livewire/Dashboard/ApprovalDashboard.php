<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class ApprovalDashboard extends Component
{
    public function render()
    {
        $roleId = auth()->user()->roles->first()->id;

        return view('livewire.dashboard.approvals', [
            'approvals' => AuthApproval::where('status', 'pending')
                ->where('approver_role_id', $roleId)
                ->latest()
                ->get(),
        ]);
    }
}
