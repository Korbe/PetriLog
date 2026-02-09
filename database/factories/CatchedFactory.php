<?php

namespace Database\Factories;

use App\Models\Fish;
use App\Models\Lake;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Catched>
 */
class CatchedFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'fish_id' => Fish::factory(),
            'lake_id' => Lake::factory(),
            'length' => $this->faker->numberBetween(10, 100),
            'weight' => $this->faker->numberBetween(100, 10000),
            'depth' => $this->faker->numberBetween(1, 50),
            'temperature' => $this->faker->numberBetween(5, 30),
            'date' => $this->faker->dateTimeBetween('-20 weeks', 'now'),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'address' => $this->faker->address(),
        ];
    }
}
