<?php

namespace App\Livewire\Holdings\Resto\Components;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class BranchSwitcher extends Component
{
    public array $userBranches = [];

    public ?int $currentBranchId = null;

    public string $currentBranchName = '';

    public bool $showDropdown = false;

    public function mount(): void
    {
        $this->loadUserBranches();
        $this->setCurrentBranch();
    }

    protected function loadUserBranches(): void
    {
        $userId = auth()->id();

        if (! $userId) {
            return;
        }

        $this->userBranches = DB::connection('sccr_resto')
            ->table('user_branches as ub')
            ->join('branches as b', 'b.id', '=', 'ub.branch_id')
            ->where('ub.auth_user_id', $userId)
            ->where('ub.is_active', true)
            ->where('b.is_active', true)
            ->select([
                'b.id',
                'b.code',
                'b.name',
                'ub.is_default',
            ])
            ->orderBy('ub.is_default', 'desc')
            ->orderBy('b.name')
            ->get()
            ->map(fn ($b) => (array) $b)
            ->toArray();
    }

    protected function setCurrentBranch(): void
    {
        $sessionBranchId = session('current_branch_id');

        if ($sessionBranchId && $this->isValidBranch($sessionBranchId)) {
            $this->currentBranchId = (int) $sessionBranchId;
            $this->updateCurrentBranchName();

            return;
        }

        $defaultBranch = collect($this->userBranches)->firstWhere('is_default', true);

        if ($defaultBranch) {
            $this->currentBranchId = $defaultBranch['id'];
            $this->saveBranchToSession();
            $this->updateCurrentBranchName();

            return;
        }

        $firstBranch = $this->userBranches[0] ?? null;

        if ($firstBranch) {
            $this->currentBranchId = $firstBranch['id'];
            $this->saveBranchToSession();
            $this->updateCurrentBranchName();
        }
    }

    protected function isValidBranch(int $branchId): bool
    {
        return collect($this->userBranches)->contains('id', $branchId);
    }

    protected function updateCurrentBranchName(): void
    {
        $branch = collect($this->userBranches)->firstWhere('id', $this->currentBranchId);
        $this->currentBranchName = $branch ? $branch['name'] : 'Select Branch';
    }

    protected function saveBranchToSession(): void
    {
        session(['current_branch_id' => $this->currentBranchId]);
    }

    public function switchBranch(int $branchId): void
    {
        if (! $this->isValidBranch($branchId)) {
            $this->dispatch('toast', type: 'error', message: 'Invalid branch selected');

            return;
        }

        $this->currentBranchId = $branchId;
        $this->saveBranchToSession();
        $this->updateCurrentBranchName();
        $this->showDropdown = false;

        $this->dispatch('branch-changed', branchId: $branchId);

        $this->dispatch('toast', type: 'success', message: 'Branch switched successfully');

        $this->dispatch('refresh-page');
    }

    public function toggleDropdown(): void
    {
        $this->showDropdown = ! $this->showDropdown;
    }

    public function render()
    {
        return view('livewire.holdings.resto.components.branch-switcher');
    }
}
