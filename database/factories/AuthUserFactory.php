<?php

namespace Database\Factories;

use App\Models\Auth\AuthUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Auth\AuthUser>
 */
class AuthUserFactory extends Factory
{
    protected $model = AuthUser::class;

    protected static ?string $password;

    public function definition(): array
    {
        return [
            'employee_nip' => fake()->unique()->numerify('########'),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'is_super_admin' => false,
            'is_super_scope' => false,
            'must_change_password' => 0,
        ];
    }
}
