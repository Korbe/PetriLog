<?php

namespace Database\Seeders;

use App\Models\Lake;
use App\Models\State;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class LakeSeeder extends Seeder
{
    public function run(): void
    {
        $lakes = config('lakes');

        DB::transaction(function () use ($lakes) {

            // 1️⃣ Zuerst: Neutraler Default-Eintrag
            Lake::updateOrCreate(
                ['name' => 'Anderes / nicht gelistet'],
                [
                    'desc' => 'Sonstiges / nicht gelistetes Gewässer',
                    'hint' => null,
                    'is_default' => true,
                ]
            );

            // 2️⃣ Danach alle Lakes aus der Config
            foreach ($lakes as $lakeKey => $data) {

                $name = $data['name'] ?? $lakeKey;

                // Falls in der Config zufällig gleich benannt → überspringen
                if ($name === 'Anderes / nicht gelistet') {
                    continue;
                }

                $lake = Lake::updateOrCreate(
                    [
                        'name' => $name,
                    ],
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
                            'State(s) nicht gefunden für Lake "' .
                                $lake->name .
                                '": ' . implode(', ', $missing)
                        );
                    }

                    $lake->states()->syncWithoutDetaching($stateIds);
                }
            }
        });
    }
}
