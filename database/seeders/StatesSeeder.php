<?php

namespace Database\Seeders;

use App\Models\State;
use Illuminate\Database\Seeder;

class StatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $states = [
            [
                'name' => 'Kärnten',
                'desc' => 'Kärnten, das südlichste Bundesland Österreichs, ist bekannt für seine Vielzahl an glasklaren Seen, darunter der Wörthersee, Faaker See und Ossiacher See. Diese Seen bieten nicht nur beeindruckende Landschaften, sondern auch ideale Bedingungen für Angler. Ob Forellen, Karpfen oder Hechte – Fischer finden hier reichlich Möglichkeiten, ihrem Hobby nachzugehen. Die Kombination aus alpiner Kulisse, sauberen Gewässern und gut ausgebauten Angelplätzen macht Kärnten zu einem beliebten Ziel für Natur- und Fischliebhaber gleichermaßen.',
            ],
            [
                'name' => 'Steiermark',
                'desc' => 'Die Steiermark, oft als „Grünes Herz Österreichs“ bezeichnet, bietet zahlreiche Möglichkeiten zum Angeln. Flüsse wie Mur, Enns oder Lafnitz sowie klare Gebirgsseen wie Grundlsee oder Toplitzsee locken sowohl Hobby- als auch erfahrene Angler. Forellen, Saiblinge und Karpfen sind hier häufige Fangarten. Die abwechslungsreiche Natur, umgeben von Wäldern und Bergen, sorgt für entspannte Angelstunden in idyllischer Umgebung. Angelkarten sind meist leicht erhältlich, und viele Gewässer sind gut zugänglich.',
            ],
            [
                'name' => 'Burgenland',
                'desc' => 'Das Burgenland in Österreich bietet hervorragende Möglichkeiten zum Angeln. Besonders der Neusiedler See zieht Hobby- und Profiangler gleichermaßen an, da er eine vielfältige Fischpopulation wie Zander, Hecht, Karpfen und Barsch beherbergt. Auch kleinere Gewässer wie der Zicksee oder Stauseen laden zum ruhigen Fischen ein. Angler profitieren von gut erreichbaren Uferstellen, Angelvereinen und klar geregelten Fischereirechten, die sowohl Einheimischen als auch Touristen ein entspanntes Erlebnis in der Natur ermöglichen.',
            ],
            [
                'name' => 'Niederösterreich',
                'desc' => 'Niederösterreich, das größte Bundesland Österreichs, bietet ausgezeichnete Angelmöglichkeiten. Besonders die Donau, Ybbs, Traisen und Thaya ziehen Angler mit reichen Fischbeständen wie Karpfen, Zander oder Forelle an. Neben Flüssen laden zahlreiche Seen wie der Lunzer See oder der Erlaufsee zum entspannten Fischen ein. Gut ausgebaute Angelplätze, Vereinsgewässer und naturnahe Flussabschnitte machen Niederösterreich sowohl für Freizeitangler als auch für erfahrene Sportfischer attraktiv. Angler genießen hier Ruhe, Natur und abwechslungsreiche Fischarten.',
            ],
            [
                'name' => 'Oberösterreich',
                'desc' => 'Oberösterreich ist ein Paradies für Angler. Mit zahlreichen Seen wie dem Traunsee, Attersee, Mondsee und Wolfgangsee sowie Flüssen wie Donau, Traun und Inn bietet das Bundesland vielfältige Fischmöglichkeiten. Forellen, Saiblinge, Hechte, Karpfen und Barsche sind weit verbreitet. Neben den natürlichen Gewässern gibt es gut gepflegte Angelplätze und Fischereivereine. Auch Fliegenfischen in klaren Gebirgsbächen ist beliebt. Für Hobby- und Profiangler bietet Oberösterreich sowohl Ruhe als auch abwechslungsreiche Fischreviere.',
            ],
            [
                'name' => 'Salzburg',
                'desc' => 'Salzburg, eingebettet zwischen Alpen und Seen, bietet ideale Bedingungen für Angler. Die Region verfügt über zahlreiche klare Bergseen wie den Zeller See oder Fuschlsee sowie Flüsse wie die Salzach, die hervorragende Fischbestände bieten. Forellen, Saiblinge und Karpfen sind hier häufig zu fangen. Angler schätzen zudem die ruhige Naturkulisse und gut ausgebaute Uferbereiche. Mit gültigem Fischereischein können sowohl Freizeit- als auch erfahrene Sportangler die abwechslungsreichen Gewässer Salzburgs erkunden.',
            ],
            [
                'name' => 'Tirol',
                'desc' => 'Tirol, im Westen Österreichs, bietet zahlreiche Möglichkeiten zum Angeln. Die Region ist geprägt von klaren Bergseen wie dem Achensee oder Piburger See sowie reißenden Flüssen wie dem Inn. Angler finden hier Forellen, Saiblinge und Äschen. Neben klassischen Angelplätzen gibt es auch ruhigere Almlandschaften, die perfekte Rückzugsorte bieten. Lizenzen sind meist erforderlich, doch Tirol kombiniert Naturschönheit und Fischreichtum ideal für entspannte wie sportliche Angeltouren.',
            ],
            [
                'name' => 'Vorarlberg',
                'desc' => 'Vorarlberg, das westlichste Bundesland Österreichs, bietet ideale Bedingungen für Angler. Zahlreiche Flüsse wie der Rhein und die Bregenzer Ach, sowie Bergseen wie der Bodensee, Formarinsee und Lünersee, bieten vielfältige Fischarten. Forellen, Äschen, Hechte und Karpfen sind hier verbreitet. Neben der Fischerei in klaren Gebirgsflüssen gibt es auch Möglichkeiten für Sport- und Freizeitfischen an größeren Seen, oft in landschaftlich reizvoller Umgebung, die Naturerlebnis und Angelerfolg perfekt verbinden.',
            ],
            [
                'name' => 'Wien',
                'desc' => 'Wien, die Hauptstadt Österreichs, liegt an der Donau und bietet zahlreiche Möglichkeiten zum Angeln. Sowohl die Donau selbst als auch die Alte und Neue Donau sind beliebte Spots für Hobbyangler. Hier kann man auf Karpfen, Zander, Hecht oder Wels fischen. Angelkarten sind in der Stadtregion leicht erhältlich, und viele Bereiche bieten ruhige Ufer, ideal für entspannte Stunden in der Natur. Urbanes Angeln kombiniert Naturerlebnis und Stadtleben – ein einzigartiges Erlebnis für Angelfreunde in Wien.',
            ],
        ];

        foreach ($states as $state) {
            State::create($state);
        }
    }
}
