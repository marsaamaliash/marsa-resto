<?php

namespace App\Services;

use App\Models\Auth\AuthUser;
use Illuminate\Support\Facades\DB;

class NavMenuService
{
    /**
     * Build nested menu tree from auth_nav_items (filtered by module+permission).
     * Parent visible if:
     * - it has route AND user can access it; OR
     * - it has children visible (group header).
     *
     * If parent has permission_code, it's treated as gate for the whole subtree.
     */
    public function forUser(AuthUser $user): array
    {
        $items = DB::table('auth_nav_items')
            ->where('is_active', 1)
            ->orderByRaw('COALESCE(parent_id, 0) ASC')
            ->orderBy('sort_order', 'asc')
            ->orderBy('nav_code', 'asc')
            ->get([
                'id', 'nav_code', 'parent_id', 'module_code', 'label', 'route_name',
                'permission_code', 'icon', 'sort_order',
            ])
            ->map(fn ($r) => [
                'id' => (int) $r->id,
                'nav_code' => (string) $r->nav_code,
                'parent_id' => $r->parent_id !== null ? (int) $r->parent_id : null,
                'module_code' => (string) $r->module_code,
                'label' => (string) $r->label,
                'route_name' => $r->route_name ? (string) $r->route_name : null,
                'permission_code' => $r->permission_code ? (string) $r->permission_code : null,
                'icon' => $r->icon ? (string) $r->icon : null,
                'sort_order' => (int) $r->sort_order,
                'children' => [],
            ])
            ->toArray();

        // group by parent
        $byParent = [];
        foreach ($items as $it) {
            $pid = $it['parent_id'] ?? 0;
            $byParent[$pid] ??= [];
            $byParent[$pid][] = $it;
        }

        // recursive build with filtering
        $build = function ($parentId, $parentGateAllowed = true) use (&$build, $byParent, $user): array {
            $children = $byParent[$parentId] ?? [];
            $out = [];

            foreach ($children as $node) {
                // Gate 1: module access
                $moduleOk = $user->isSuperAdmin() ? true : $user->hasModule($node['module_code']);

                // Gate 2: permission (optional)
                $perm = $node['permission_code'];
                $permOk = ($perm === null || $perm === '') ? true : ($user->isSuperAdmin() ? true : $user->hasPermission($perm));

                // If parentGateAllowed false, subtree hidden
                $selfGateOk = $parentGateAllowed && $moduleOk && $permOk;

                // Build children first (if parent has explicit permission, it gates subtree)
                $childTree = $build($node['id'], $selfGateOk);

                // Visible rule:
                // - leaf with route_name: must pass gate
                // - group header (no route): visible if has visible children (and gate ok if permission exists)
                $hasRoute = ! empty($node['route_name']);
                $hasChildren = ! empty($childTree);

                $visible = false;
                if ($hasRoute) {
                    $visible = $selfGateOk;
                } else {
                    // group header
                    $visible = $hasChildren; // show only if any child visible
                }

                if ($visible) {
                    $node['children'] = $childTree;
                    $out[] = $node;
                }
            }

            return $out;
        };

        return $build(0, true);
    }
}
