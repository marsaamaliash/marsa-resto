<?php

namespace App\Models\Auth;

use Laravel\Sanctum\PersonalAccessToken as SanctumToken;

class AuthPersonalAccessToken extends SanctumToken
{
    protected $connection = 'mysql';

    protected $table = 'auth_personal_access_tokens';
}
