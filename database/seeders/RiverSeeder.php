<?php

namespace Database\Seeders;

use App\Models\River;
use App\Models\State;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RiverSeeder extends Seeder
{
    public function run(): void
    {
        $rivers = config('rivers');

        DB::transaction(function () use ($rivers) {

            // 1️⃣ Zuerst: Neutraler Default-Eintrag
            River::updateOrCreate(
                ['name' => 'Anderes / nicht gelistet'],
                [
                    'desc' => 'Sonstiges / nicht gelistetes Gewässer',
                    'hint' => null,
                    'is_default' => true,
                ]
            );

            // 2️⃣ Danach alle Rivers aus der Config
            foreach ($rivers as $riverKey => $data) {

                $name = $data['name'] ?? $riverKey;

                // Falls in der Config zufällig gleich benannt → überspringen
                if ($name === 'Anderes / nicht gelistet') {
                    continue;
                }

                $river = River::updateOrCreate(
                    ['name' => $name],
                    [
                        'desc' => $data['desc'] ?? null,
                        'hint' => $data['tips'] ?? null,
                    ]
                );

                /**
                 * STATES anbinden (n:n)
                 */
                if (!empty($data['state'])) {
                    $states = (array) $data['state'];

                    $stateIds = State::whereIn('name', $states)->pluck('id');

                    if ($stateIds->count() !== count($states)) {
                        $missing = array_diff(
                            $states,
                            State::whereIn('name', $states)->pluck('name')->toArray()
                        );

                        throw new \Exception(
                            'State(s) nicht gefunden für River "' .
                                $river->name .
                                '": ' . implode(', ', $missing)
                        );
                    }

                    $river->states()->syncWithoutDetaching($stateIds);
                }
            }
        });
    }
}
