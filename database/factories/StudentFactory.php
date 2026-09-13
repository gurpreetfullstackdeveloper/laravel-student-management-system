<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => 'student']),
            'admission_no' => fake()->unique()->bothify('ADM-####'),
            'date_of_birth' => fake()->dateTimeBetween('-20 years', '-5 years'),
            'gender' => fake()->randomElement(['male', 'female']),
            'guardian_name' => fake()->name(),
            'guardian_phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'admission_date' => fake()->dateTimeBetween('-5 years', 'now'),
            'photo_path' => null,
        ];
    }
}