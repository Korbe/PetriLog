<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'category' => $this->faker->randomElement(['UI', 'Backend', 'API']),
            'steps' => $this->faker->paragraph(),
            'url' => $this->faker->url(),
        ];
    }

    public function withoutUser(): static
    {
        return $this->state(fn() => [
            'user_id' => null,
        ]);
    }

    public function category(string $cat): static
    {
        return $this->state(fn() => [
            'category' => $cat,
        ]);
    }
}
