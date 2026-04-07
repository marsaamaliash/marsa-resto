<?php

namespace App\Livewire\Dashboard;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SsoDashboard extends Component
{
    public array $breadcrumbs = [];

    public int $pendingApprovals = 0;

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'SSO', 'color' => 'text-gray-900 font-semibold'],
        ];

        // Optional: count pending approvals (indikatif saja)
        $user = auth()->user();
        if ($user) {
            $roleIds = DB::table('auth_user_roles')
                ->where('auth_user_id', $user->id)
                ->pluck('role_id')
                ->map(fn ($v) => (int) $v)
                ->toArray();

            $this->pendingApprovals = (int) DB::table('auth_approvals')
                ->where('status', 'pending')
                ->where(function ($q) use ($roleIds) {
                    // approval yang tidak spesifik role (null) atau match role approver
                    $q->whereNull('approver_role_id');
                    if (! empty($roleIds)) {
                        $q->orWhereIn('approver_role_id', $roleIds);
                    }
                })
                ->count();
        }
    }

    public function render()
    {
        return view('livewire.dashboard.sso-dashboard', [
            'breadcrumbs' => $this->breadcrumbs,
            'pendingApprovals' => $this->pendingApprovals,
        ])->layout('components.sccr-layout');
    }
}
