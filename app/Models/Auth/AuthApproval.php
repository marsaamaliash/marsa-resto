<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;

class AuthApproval extends Model
{
    protected $table = 'auth_approvals';

    public $timestamps = false;

    protected $fillable = [
        'auth_user_id',
        'approver_role_id',
        'module_code',
        'permission_code',
        'action_payload',
        'status',
        'approved_by',
        'approved_at',
        'rejected_reason',
    ];

    protected $casts = [
        'action_payload' => 'array',
        'approved_at' => 'datetime',
    ];

    /* =====================
     | RELATIONS
     ===================== */

    public function requester()
    {
        return $this->belongsTo(AuthUser::class, 'auth_user_id');
    }

    public function approver()
    {
        return $this->belongsTo(AuthUser::class, 'approved_by');
    }
}
