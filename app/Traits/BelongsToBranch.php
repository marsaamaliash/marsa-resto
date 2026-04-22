<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToBranch
{
    protected static function bootBelongsToBranch(): void
    {
        static::creating(function ($model) {
            if (empty($model->branch_id) && session()->has('current_branch_id')) {
                $model->branch_id = session('current_branch_id');
            }
        });

        static::addGlobalScope('branch', function (Builder $query) {
            // Skip global scope jika sedang di console atau session tidak ada
            if (app()->runningInConsole()) {
                return;
            }

            $branchId = session('current_branch_id');

            // Jika ada request parameter show_all_branches, skip scope
            if (request()->has('show_all_branches')) {
                return;
            }

            if ($branchId) {
                $query->where('branch_id', $branchId);
            }
        });
    }

    public function scopeWithoutBranchScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('branch');
    }

    public function scopeForBranch(Builder $query, int $branchId): Builder
    {
        return $query->withoutGlobalScope('branch')->where('branch_id', $branchId);
    }

    public function scopeForMultipleBranches(Builder $query, array $branchIds): Builder
    {
        return $query->withoutGlobalScope('branch')->whereIn('branch_id', $branchIds);
    }
}
