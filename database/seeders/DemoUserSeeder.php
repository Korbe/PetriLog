<?php

namespace Database\Seeders;

use App\Jobs\FetchCatchWeather;
use App\Models\Catched;
use App\Models\Correction;
use App\Models\Fish;
use App\Models\Lake;
use App\Models\River;
use App\Models\State;
use App\Models\User;
use Database\Seeders\Concerns\UsesSeedData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/**
 * Demo-Account mit realistischen Fängen (Kärnten) und Fotos.
 * Idempotent: Alles, was Besucher im Demo-Konto angelegt haben (Fänge, Fotos, Spots, Meldungen), wird bei jedem
 * Lauf gelöscht und neu angelegt. Läuft jede Nacht um Mitternacht über `php artisan demo:reset` (routes/console.php).
 */
class DemoUserSeeder extends Seeder
{
    use UsesSeedData;

    public const EMAIL = User::DEMO_EMAIL;

    /**
     * Einheiten: length cm, weight g, depth cm, temperature °C (Wasser), air_pressure hPa.
     * days_ago/time → Datum relativ zu now(). Fisch und Gewässer per Name (exakt wie in data/*.json).
     */
    /**
     * Bewusst nur 3 Fänge: Ohne Abo sind 5 erlaubt, so können Besucher selbst noch zwei Fänge ausprobieren.
     */
    private const CATCHES = [
        [
            'fish' => 'Hecht', 'lake' => 'Wörthersee', 'days_ago' => 3, 'time' => '17:40',
            'length' => 78, 'weight' => 3650, 'depth' => 450, 'temperature' => 14, 'air_pressure' => 1018,
            'bait' => 'Gummifisch 12 cm, Barsch-Dekor',
            'latitude' => 46.6330, 'longitude' => 14.1400, 'address' => 'Wörthersee, Pörtschach am Wörthersee',
            'remark' => '<p>Biss direkt an der Schilfkante, kurz bevor die Sonne weg war. <strong>Schöner Herbsthecht</strong>, schonend released.</p>',
            'photos' => ['catches/hecht-1.jpg'],
        ],
        [
            // Längster und schwerster Fang (Dashboard-Highlights)
            'fish' => 'Spiegelkarpfen', 'lake' => 'Faaker See', 'days_ago' => 95, 'time' => '05:50',
            'length' => 81, 'weight' => 13400, 'depth' => 350, 'temperature' => 21, 'air_pressure' => 1014,
            'bait' => 'Boilie Fisch/Krill 24 mm',
            'latitude' => 46.5780, 'longitude' => 13.9150, 'address' => 'Faaker See, Faak am See',
            'remark' => '<p>Neuer persönlicher Rekord beim Karpfen. <em>Was für ein Morgen!</em></p>',
            'photos' => ['catches/spiegelkarpfen-2.jpg'],
        ],
        [
            'fish' => 'Regenbogenforelle', 'lake' => 'Millstätter See', 'days_ago' => 40, 'time' => '06:45',
            'length' => 46, 'weight' => 1150, 'depth' => 400, 'temperature' => 18, 'air_pressure' => 1017,
            'bait' => 'Blinker 15 g, kupfer',
            'latitude' => 46.7950, 'longitude' => 13.5800, 'address' => 'Millstätter See, Millstatt',
            'remark' => '<p>Weit ausgeworfen und langsam eingeholt. <strong>Mehrere Sprünge</strong> im Drill.</p>',
            'photos' => ['catches/regenbogenforelle-1.jpg'],
        ],
    ];

    public function run(): void
    {
        $user = User::where('email', self::EMAIL)->first();

        if (!$user) {
            $user = User::create([
                'name' => 'Demo Angler',
                'email' => self::EMAIL,
                'password' => Hash::make(env('DEMO_USER_PASSWORD', 'password')),
                'state_id' => State::where('name', 'Kärnten')->value('id')
                    ?? throw new RuntimeException('State "Kärnten" nicht gefunden – StatesSeeder zuerst ausführen'),
            ]);

            // E-Mail von vornherein bestätigt (email_verified_at ist nicht fillable)
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        // Profil auf den Ausgangszustand (Besucher können es nicht ändern, aber sicher ist sicher)
        $user->forceFill([
            'name' => 'Demo Angler',
            'role' => User::ROLE_USER,
            'newsletter_opt_out' => true,
            'state_id' => State::where('name', 'Kärnten')->value('id') ?? $user->state_id,
        ])->save();

        $fishIds = Fish::pluck('id', 'name');
        $lakes = Lake::with('fish:id')->get()->keyBy('name');
        $rivers = River::with('fish:id')->get()->keyBy('name');

        $created = [];

        DB::transaction(function () use ($user, $fishIds, $lakes, $rivers, &$created) {
            // Alles von Besuchern Angelegte entfernen – Fänge einzeln, damit die Fotos (Media Library) mitgehen
            $user->catched()->get()->each->delete();
            $user->spots()->delete();
            Correction::where('user_id', $user->id)->delete();

            foreach (self::CATCHES as $data) {
                $water = isset($data['lake']) ? $lakes[$data['lake']] ?? null : $rivers[$data['river']] ?? null;
                $waterName = $data['lake'] ?? $data['river'];
                $fishId = $fishIds[$data['fish']] ?? null;

                if (!$water || !$fishId) {
                    throw new RuntimeException("Demo-Fang: Fisch \"{$data['fish']}\" oder Gewässer \"{$waterName}\" nicht gefunden");
                }

                // Nur Arten, die laut Stammdaten im Gewässer vorkommen
                if (!$water->fish->contains('id', $fishId)) {
                    throw new RuntimeException("Demo-Fang: \"{$data['fish']}\" ist \"{$waterName}\" nicht zugeordnet");
                }

                $catch = Catched::create([
                    'user_id' => $user->id,
                    'fish_id' => $fishId,
                    'lake_id' => isset($data['lake']) ? $water->id : null,
                    'river_id' => isset($data['river']) ? $water->id : null,
                    'date' => now()->subDays($data['days_ago'])->setTimeFromTimeString($data['time']),
                    ...collect($data)->only([
                        'length', 'weight', 'depth', 'temperature', 'air_pressure', 'bait',
                        'latitude', 'longitude', 'address', 'remark',
                    ])->all(),
                ]);

                $created[] = $catch;

                foreach ($data['photos'] as $order => $photo) {
                    $catch->addMedia($this->seedAsset($photo))
                        ->preservingOriginal()
                        ->setOrder($order)
                        ->toMediaCollection('photos');
                }
            }
        });

        // Wetter zu den Demo-Fängen (läuft über die Queue)
        foreach ($created as $catch) {
            FetchCatchWeather::dispatchFor($catch);
        }
    }
}
