<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeacherFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => 'teacher']),
            'employee_code' => fake()->unique()->bothify('EMP-####'),
            'qualification' => fake()->randomElement(['B.Ed.', 'M.Ed.', 'B.Sc.']),
            'phone' => fake()->phoneNumber(),
            'joining_date' => fake()->dateTimeBetween('-10 years', 'now'),
            'photo_path' => null,
        ];
    }
}