<?php

namespace App\Services;

use App\Models\Auth\AuthNavItem;
use App\Models\Auth\AuthUser;
use Illuminate\Support\Facades\Cache;

class AuthNavigationService
{
    public function sidebarTree(AuthUser $user): array
    {
        $cacheKey = "auth:user:{$user->id}:nav_tree";

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($user) {
            $rows = AuthNavItem::query()
                ->where('is_active', 1)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            // build map
            $map = [];
            foreach ($rows as $r) {
                $map[$r->id] = [
                    'id' => (int) $r->id,
                    'nav_code' => (string) $r->nav_code,
                    'parent_id' => $r->parent_id ? (int) $r->parent_id : null,
                    'module_code' => (string) $r->module_code,
                    'label' => (string) $r->label,
                    'route_name' => $r->route_name ? (string) $r->route_name : null,
                    'permission_code' => $r->permission_code ? (string) $r->permission_code : null,
                    'icon' => $r->icon ? (string) $r->icon : null,
                    'children' => [],
                ];
            }

            // attach children
            $tree = [];
            foreach ($map as $id => &$node) {
                if ($node['parent_id'] && isset($map[$node['parent_id']])) {
                    $map[$node['parent_id']]['children'][] = &$node;
                } else {
                    $tree[] = &$node;
                }
            }
            unset($node);

            // filter by ACL
            $tree = $this->filterTree($tree, $user);

            return $tree;
        });
    }

    private function filterTree(array $nodes, AuthUser $user): array
    {
        $authz = app(AuthorizationService::class);
        $out = [];

        foreach ($nodes as $n) {
            // cek module access
            if (! $authz->canAccessModule($user, $n['module_code'])) {
                continue;
            }

            // filter children dulu
            $n['children'] = $this->filterTree($n['children'], $user);

            // cek permission item (kalau item ini halaman spesifik)
            $hasPermission = true;
            if (! empty($n['permission_code'])) {
                $hasPermission = $authz->hasPermission($user, $n['permission_code']);
            }

            // rule tampil:
            // - kalau punya route: wajib lolos permission (jika ada)
            // - kalau tidak punya route: tampil jika ada child yang tampil
            if ($n['route_name']) {
                if ($hasPermission) {
                    $out[] = $n;
                }
            } else {
                if (! empty($n['children'])) {
                    $out[] = $n;
                }
            }
        }

        return $out;
    }

    /** resolve input "kode" ala SAP: bisa module_code atau nav_code */
    public function resolveRouteByCode(AuthUser $user, string $code): ?string
    {
        $code = trim($code);
        if ($code === '') {
            return null;
        }

        // 1) match nav_code
        $item = AuthNavItem::query()
            ->where('is_active', 1)
            ->where('nav_code', $code)
            ->first();

        if ($item) {
            $tree = $this->sidebarTree($user); // already filtered
            // quick allow check: reuse authz directly
            $authz = app(AuthorizationService::class);
            if (! $authz->canAccessModule($user, $item->module_code)) {
                return null;
            }
            if ($item->permission_code && ! $authz->hasPermission($user, $item->permission_code)) {
                return null;
            }

            return $item->route_name ?: null;
        }

        // 2) fallback module_code -> route module
        return $user->moduleRoute($code);
    }

    public function clearUserNavCache(int $userId): void
    {
        Cache::forget("auth:user:{$userId}:nav_tree");
    }
}
