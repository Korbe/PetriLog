<?php

namespace Database\Factories;

use App\Models\River;
use Illuminate\Database\Eloquent\Factories\Factory;

class RiverFactory extends Factory
{
    protected $model = River::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->city . ' Fluss',
            'desc' => $this->faker->sentence(10),
            'hint' => $this->faker->sentence(6),
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
