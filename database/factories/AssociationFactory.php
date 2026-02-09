<?php

namespace Database\Factories;

use App\Models\Association;
use App\Models\State;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssociationFactory extends Factory
{
    protected $model = Association::class;

    public function definition(): array
    {
        return [
            'state_id' => State::factory(), // automatisch State erstellen
            'name' => $this->faker->company(),
            'desc' => $this->faker->paragraph(),
            'link' => $this->faker->optional()->url(),
        ];
    }
}
