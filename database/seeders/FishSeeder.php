<?php

namespace Database\Seeders;

use App\Models\Fish;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FishSeeder extends Seeder
{
    public function run(): void
    {
        $fishConfig = config('fish');

        DB::transaction(function () use ($fishConfig) {

            // 1️⃣ Default-Fisch immer zuerst anlegen / aktualisieren
            $defaultFish = Fish::updateOrCreate(
                ['name' => 'Anderes / nicht gelistet'],
                [
                    'desc' => 'Sonstige / nicht gelistete Fischart',
                    'is_default' => true,
                ]
            );

            // 2️⃣ Alle anderen Fische aus der Config
            foreach ($fishConfig as $fishKey => $fishData) {

                $name = $fishData['name'] ?? $fishKey;

                // Sicherheit: Default nicht doppelt anlegen
                if ($name === 'Anderes / nicht gelistet') {
                    continue;
                }

                Fish::updateOrCreate(
                    ['name' => $name],
                    [
                        'desc' => $fishData['desc'] ?? null,
                        'is_default' => false,
                    ]
                );
            }
        });
    }
}
