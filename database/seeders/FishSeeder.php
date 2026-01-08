<?php

namespace Database\Seeders;

use App\Models\Fish;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class FishSeeder extends Seeder
{
    public function run(): void
    {
        $fishConfig = config('fish');

        DB::transaction(function () use ($fishConfig) {

            foreach ($fishConfig as $fishKey => $fishData) {

                // Name: aus Array oder Fallback auf Key
                $name = $fishData['name'] ?? $fishKey;

                Fish::updateOrCreate(
                    [
                        'name' => $name,
                    ],
                    [
                        'desc' => $fishData['desc'] ?? null,
                    ]
                );
            }
        });
    }
}
