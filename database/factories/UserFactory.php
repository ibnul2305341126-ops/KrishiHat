<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'verification_status' => 'verified',
            'farmer_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the farmer's profile is unverified.
     */
    public function unverifiedFarmer(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'farmer',
            'verification_status' => 'unverified',
            'farmer_verified_at' => null,
            'nid_number' => null,
            'trade_license_number' => null,
        ]);
    }

    /**
     * Indicate that the farmer has submitted verification and is pending admin review.
     */
    public function pendingVerification(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'farmer',
            'verification_status' => 'pending',
            'farmer_verified_at' => null,
            'nid_number' => '19901234567890123',
            'trade_license_number' => 'TRAD/BOG/2026/0124',
            'farm_name' => 'বগুড়া ডেমো এগ্রো ফার্ম',
            'verification_submitted_at' => now(),
        ]);
    }

    /**
     * Indicate that the farmer verification was rejected by admin.
     */
    public function rejectedVerification(string $reason = 'জাতীয় পরিচয়পত্রের কপি অস্পষ্ট।'): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'farmer',
            'verification_status' => 'rejected',
            'farmer_verified_at' => null,
            'nid_number' => '19901234567890123',
            'trade_license_number' => 'TRAD/BOG/2026/0124',
            'verification_rejection_reason' => $reason,
        ]);
    }
}
