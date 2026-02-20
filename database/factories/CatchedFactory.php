<?php

namespace Database\Factories;

use App\Models\Catched;
use App\Models\Fish;
use App\Models\Lake;
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

        // Fish aus DB zufällig auswählen
        $fishId = Fish::inRandomOrder()->value('id');

        // Lake oder River zufällig aus definierten ID-Bereichen
        $lakeId = $useLake ? rand(2, 100) : null;
        $riverId = !$useLake ? rand(1, 20) : null;

        return [
            'user_id' => 10,
            'fish_id' => $fishId,
            'lake_id' => $lakeId,
            'river_id' => $riverId,
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
