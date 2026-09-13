<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AcademicYearFactory extends Factory
{
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-1 year', '+1 year');

        return [
            'name' => $startDate->format('Y').'-'.((int) $startDate->format('Y') + 1),
            'start_date' => $startDate,
            'end_date' => (clone $startDate)->modify('+1 year -1 day'),
            'is_current' => false,
        ];
    }
}