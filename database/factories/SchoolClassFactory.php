<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SchoolClassFactory extends Factory
{
    public function definition(): array
    {
        $order = fake()->unique()->numberBetween(1, 12);

        return [
            'name' => 'Class '.$order,
            'order' => $order,
        ];
    }
}