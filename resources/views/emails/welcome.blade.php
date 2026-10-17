<x-mail.wrapper preheader="Dein digitales Fangbuch ist bereit – so trägst du deinen ersten Fang ein.">
    <x-mail.heading eyebrow="Willkommen an Bord">
        Schön, dass du da bist{{ $name ? ', ' . $name : '' }}!
    </x-mail.heading>

    <x-mail.text>
        Dein digitales Fangbuch ist bereit. Ab sofort hältst du jeden Fang fest – Länge, Gewicht, Köder,
        Wassertemperatur und den genauen Spot. Privat, übersichtlich und direkt vom Ufer aus.
    </x-mail.text>

    <x-mail.steps :items="[
        ['title' => 'Ersten Fang eintragen', 'text' => 'Fisch, Gewässer und Maße erfassen – Fotos und Notizen sind optional.'],
        ['title' => 'Gewässer entdecken', 'text' => 'Seen und Flüsse in ganz Österreich mit Tipps, Fischbestand und Lizenz-Infos.'],
        ['title' => 'App aufs Handy holen', 'text' => 'PetriLog zum Startbildschirm hinzufügen und am Wasser direkt loslegen.'],
    ]" />

    <x-mail.button :url="route('app.catched.create')">Ersten Fang eintragen</x-mail.button>
</x-mail.wrapper>
