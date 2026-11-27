<?php

namespace Database\Factories;

use App\Enums\UserStatus;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'role_id' => fn () => Role::query()
                ->firstOrCreate(
                    ['code' => 'student'],
                    ['name' => 'Student']
                )->id,

            'name' => fake()->name(),
            'mobile' => fake()->unique()->numerify('+120255501##'),
            'email' => fake()->unique()->safeEmail(),

            'email_verified_at' => now(),
            'mobile_verified_at' => now(),

            'password' => static::$password ??= Hash::make('password'),

            'status' => UserStatus::Active,

            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function mobileUnverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'mobile_verified_at' => null,
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => UserStatus::Suspended,
        ]);
    }

    public function forRole(Role $role): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => $role->id,
        ]);
    }
}
