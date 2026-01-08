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

            foreach ($rivers as $riverKey => $data) {

                $river = River::updateOrCreate(
                    ['name' => $data['name'] ?? $riverKey],
                    [
                        'desc' => $data['desc'] ?? null,
                        'hint' => $data['tips'] ?? null,
                    ]
                );

                if (!empty($data['state'])) {
                    $states = (array) $data['state'];
                    $stateIds = State::whereIn('name', $states)->pluck('id');
                    $river->states()->syncWithoutDetaching($stateIds);
                }
            }
        });
    }
}
