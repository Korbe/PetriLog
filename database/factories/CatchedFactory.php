<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Catched>
 */
class CatchedFactory extends Factory
{
    public function definition(): array
    {
        $fishSpeciesAustria = [
            'Aal', 'Aalrutte', 'Adriastör', 'Äsche', 'Aitel', 'Amur', 'Atlantischer Stör', 'Bachforelle', 'Bachneunauge',
            'Bachsaibling', 'Bachschmerle', 'Barbe', 'Bitterling', 'Blaubandbärbling', 'Brachse', 'Donaukaulbarsch',
            'Dreistachliger Stichling', 'Elritze', 'Flussbarsch', 'Frauennerfling', 'Giebel', 'Glattdick', 'Goldsteinbeißer',
            'Gründling', 'Güster', 'Hasel', 'Hausen', 'Hecht', 'Huchen', 'Karausche', 'Karpfen', 'Kaulbarsch',
            'Kesslergründling', 'Koppe', 'Laube', 'Malermuschel', 'Moderlieschen', 'Nackthalsgrundel', 'Nase', 'Nerfling',
            'Perlfisch', 'Rapfen', 'Regenbogenforelle', 'Reinanke', 'Rotauge', 'Rotfeder', 'Rußnase', 'Schleie',
            'Schlammpeitzger', 'Schneider', 'Schrätzer', 'Schwarzmundgrundel', 'Seeforelle', 'Seelaube', 'Seesaibling',
            'Semling', 'Sichling', 'Sonnenbarsch', 'Steingreßling', 'Steinbeißer', 'Sterlet', 'Sternhausen', 'Streber',
            'Tolstolob', 'Ukrainisches Bachneunauge', 'Waxdick', 'Weißflossengründling', 'Weißer Stör', 'Wels',
            'Wolgazander', 'Zander', 'Zingel', 'Zobel', 'Zope', 'Zwergwels'
        ];

        $watersAustria = [
            'Donau', 'Drau', 'Enns', 'Gail', 'Gurk', 'Inn', 'Kamp', 'Leitha', 'March', 'Mur', 'Raab', 'Salzach', 'Thaya',
            'Traun', 'Ybbs', 'Achensee', 'Altausseer See', 'Attersee', 'Faaker See', 'Fuschlsee', 'Grundlsee',
            'Hallstätter See', 'Klopeiner See', 'Millstätter See', 'Mondsee', 'Neusiedler See', 'Ossiacher See',
            'Traunsee', 'Weißensee', 'Wolfgangsee', 'Wörthersee', 'Zeller See'
        ];

        return [
            'user_id' => User::find(1)->id,
            'name' => Arr::random($fishSpeciesAustria),
            'length' => $this->faker->numberBetween(10, 100),
            'weight' => $this->faker->numberBetween(100, 10000),
            'depth' => $this->faker->numberBetween(1, 50),
            'temperature' => $this->faker->numberBetween(5, 30),
            'waters' => Arr::random($watersAustria),
            'date' => $this->faker->dateTimeBetween('-7 days', 'now'),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'address' => $this->faker->address(),
        ];
    }
}
