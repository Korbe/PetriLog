<?php

namespace Database\Seeders;

use App\Models\Fish;
use App\Models\River;
use App\Models\State;
use Database\Seeders\Concerns\UsesSeedData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RiverSeeder extends Seeder
{
    use UsesSeedData;

    public function run(): void
    {
        $rivers = $this->seedData('rivers');
        $stateIds = State::pluck('id', 'name');
        $fishIds = Fish::pluck('id', 'name');

        DB::transaction(function () use ($rivers, $stateIds, $fishIds) {
            foreach ($rivers as $data) {
                // is_default ist nicht fillable → forceFill
                $river = River::firstOrNew(['name' => $data['name']]);
                $river->forceFill([
                    'desc' => $data['desc'] ?? null,
                    'hint' => $data['hint'] ?? null,
                    'fishing_rights' => $data['fishing_rights'] ?? null,
                    'ticket_sales' => $data['ticket_sales'] ?? null,
                    'is_default' => (bool) ($data['is_default'] ?? false),
                ])->save();

                // Bundesländer und Fischarten (n:n) über Namen zuordnen
                $river->states()->sync(
                    $this->resolveIds($data['states'] ?? [], $stateIds, 'State(s) für River "' . $river->name . '"')
                );
                $river->fish()->sync(
                    $this->resolveIds($data['fish'] ?? [], $fishIds, 'Fisch(e) für River "' . $river->name . '"')
                );
            }
        });
    }
}
