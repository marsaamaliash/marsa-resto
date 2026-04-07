<?php

namespace App\Services;

use App\Models\Auth\AuthIdentity;
use Illuminate\Support\Facades\DB;

class AuthProfileResolver
{
    private static function normKey(string $s): string
    {
        // NBSP & whitespace aneh -> spasi biasa
        $s = preg_replace('/[\x{00A0}\x{2007}\x{202F}]/u', ' ', $s) ?? $s;

        // collapse whitespace beruntun menjadi 1 spasi (tetap mempertahankan konsep "nip pakai spasi")
        $s = preg_replace('/\s+/u', ' ', $s) ?? $s;

        return trim($s);
    }

    public static function resolve(?AuthIdentity $identity = null)
    {
        if (! $identity) {
            $user = auth()->user();
            if (! $user || ! $user->identity) {
                return null;
            }
            $identity = $user->identity;
        }

        $key = self::normKey((string) $identity->identity_key);

        return match ($identity->identity_type) {

            'employee' => self::resolveEmployee($key),

            'lecturer' => DB::connection('sccr_campus')
                ->table('lecturers')
                ->where('nidn', $key)
                ->first(),

            'student' => DB::connection('sccr_campus')
                ->table('students')
                ->where('nim', $key)
                ->first(),

            default => null,
        };
    }

    private static function resolveEmployee(string $key)
    {
        // 1) FAST PATH: exact match (pakai index PK nip)
        $row = DB::table('employees')
            ->where('nip', $key)
            ->first();

        if ($row) {
            return $row;
        }

        // 2) FALLBACK: kalau nip di DB mengandung spasi ganda / whitespace aneh
        // Catatan: ini tidak pakai index, jadi hanya jalan kalau fast path gagal.
        return DB::table('employees')
            ->whereRaw("REPLACE(REPLACE(REPLACE(TRIM(nip), '  ', ' '), '  ', ' '), '  ', ' ') = ?", [$key])
            ->first();
    }
}
