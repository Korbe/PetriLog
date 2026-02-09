<?php

namespace Database\Factories;

use App\Models\Lake;
use App\Models\State;
use Illuminate\Database\Eloquent\Factories\Factory;

class LakeFactory extends Factory
{
    protected $model = Lake::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word() . ' See',
            'desc' => $this->faker->sentence(),
            'hint' => $this->faker->sentence(),
            'fishing_rights' => $this->faker->randomElement([
                'Erlaubnisschein erforderlich',
                'Freies Angeln',
                'Nur Vereinsmitglieder',
            ]),
            'ticket_sales' => $this->faker->randomElement([
                'Online',
                'Vor Ort',
                'Tourismusbüro',
            ]),
        ];
    }
}
