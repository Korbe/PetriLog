@php($c = $correction)
<x-mail.wrapper :preheader="$done ? 'Deine Meldung wurde umgesetzt.' : 'Rückmeldung zu deiner Meldung.'">
    <x-mail.heading :eyebrow="'Wiki · ' . $c->categoryLabel()">
        @if ($done)
            Danke{{ $c->user ? ', ' . $c->user->name : '' }} – „{{ $name }}“ ist korrigiert!
        @else
            Danke für deine Meldung{{ $c->user ? ', ' . $c->user->name : '' }}!
        @endif
    </x-mail.heading>

    <x-mail.text>
        @if ($done)
            Wir haben deinen Hinweis geprüft und den Eintrag im Wiki angepasst. Mit Meldungen wie deiner bleibt
            PetriLog für alle Angler aktuell – Petri Heil!
        @else
            Wir haben deinen Hinweis zu „{{ $name }}“ geprüft, aber nicht übernommen.
        @endif
    </x-mail.text>

    <x-mail.panel title="Deine Meldung">
        <p style="margin:0 0 14px; font-size:15px; line-height:1.6; color:#5b6669; white-space:pre-line;">{{ $c->message }}</p>
    </x-mail.panel>

    @if (filled($c->review_note))
        <x-mail.panel title="Anmerkung unseres Teams">
            <p style="margin:0 0 14px; font-size:16px; line-height:1.6; color:#0f1d22; white-space:pre-line;">{{ $c->review_note }}</p>
        </x-mail.panel>
    @endif

    @if ($url = $c->wikiUrl())
        <x-mail.button :url="$url">Eintrag im Wiki ansehen</x-mail.button>
    @endif

    <x-slot:subcopy>
        Fragen dazu? Schreib uns einfach über „Ticket erstellen“ in der App.
    </x-slot:subcopy>
</x-mail.wrapper>
