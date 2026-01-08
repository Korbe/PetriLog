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

            foreach ($lakes as $lakeKey => $data) {

                // Name aus Config oder Key
                $lake = Lake::updateOrCreate(
                    [
                        'name' => $data['name'] ?? $lakeKey,
                    ],
                    [
                        'desc' => $data['desc'] ?? null,
                        'hint' => $data['tips'] ?? null,
                    ]
                );

                /**
                 * STATES anbinden (n:n)
                 * erlaubt:
                 * 'state' => 'Kärnten'
                 * 'state' => ['Kärnten', 'Steiermark']
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
