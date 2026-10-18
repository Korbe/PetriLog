<?php

namespace Database\Factories;

use App\Models\Catched;
use App\Models\Fish;
use App\Models\Lake;
use App\Models\River;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Catched>
 */
class CatchedFactory extends Factory
{
    protected $model = Catched::class;

    public function definition(): array
    {
        $useLake = $this->faker->boolean();

        // Vorhandene Stammdaten nutzen (Seeder), sonst – z. B. in Tests – passende Einträge anlegen
        return [
            'user_id' => User::factory(),
            'fish_id' => fn() => Fish::inRandomOrder()->value('id') ?? Fish::factory(),
            'lake_id' => $useLake ? fn() => Lake::inRandomOrder()->value('id') ?? Lake::factory() : null,
            'river_id' => $useLake ? null : fn() => River::inRandomOrder()->value('id') ?? River::factory(),
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


    /**
     * Setzt einen bestimmten User für die Fänge
     * 
     * Catched::factory()->count(100)->forUser($user)->create();
     * 
     */
    public function forUser(User $user)
    {
        return $this->state(fn(array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}
