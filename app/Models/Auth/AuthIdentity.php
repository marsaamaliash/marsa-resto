<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;

class AuthIdentity extends Model
{
    protected $table = 'auth_identities';

    protected $fillable = [
        'auth_user_id',
        'identity_type',     // employee | lecturer | student
        'identity_key',      // NIP / NIDN / NIM
        'holding_id',
        'department_id',
        'division_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /* =========================================================
     | RELATIONS
     ========================================================= */

    public function user()
    {
        return $this->belongsTo(AuthUser::class, 'auth_user_id');
    }

    public function holding()
    {
        return $this->belongsTo(\App\Models\Holding::class, 'holding_id');
    }

    public function department()
    {
        return $this->belongsTo(\App\Models\Department::class, 'department_id');
    }

    public function division()
    {
        return $this->belongsTo(\App\Models\Division::class, 'division_id');
    }

    /* =========================================================
     | STATUS
     ========================================================= */

    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    /* =========================================================
     | CONTEXT HELPERS (NON-AUTH)
     ========================================================= */

    public function isEmployee(): bool
    {
        return $this->identity_type === 'employee';
    }

    public function isLecturer(): bool
    {
        return $this->identity_type === 'lecturer';
    }

    public function isStudent(): bool
    {
        return $this->identity_type === 'student';
    }

    /**
     * Digunakan untuk resolve domain database / service
     * ❗️BUKAN untuk auth / permission
     */
    public function domainSource(): string
    {
        return match ($this->identity_type) {
            'employee' => 'sccr_db',
            'lecturer', 'student' => 'campus_db',
            default => 'unknown',
        };
    }
}
