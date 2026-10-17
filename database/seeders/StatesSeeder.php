<?php

namespace Database\Seeders;

use App\Models\State;
use Database\Seeders\Concerns\UsesSeedData;
use Illuminate\Database\Seeder;

class StatesSeeder extends Seeder
{
    use UsesSeedData;

    public function run(): void
    {
        foreach ($this->seedData('states') as $data) {
            $state = State::updateOrCreate(
                ['name' => $data['name']],
                ['desc' => $data['desc'] ?? null]
            );

            // Wappen
            $this->attachSeedImage($state, $data['image'] ?? null, 'state');
        }
    }
}
