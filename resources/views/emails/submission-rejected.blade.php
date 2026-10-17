<x-mail.wrapper :preheader="'Rückmeldung zu deinem Vorschlag „' . $review['name'] . '“'">
    <x-mail.heading :eyebrow="'Vorschlag geprüft · ' . $review['label']">
        Danke für deinen Vorschlag{{ $review['user_name'] ? ', ' . $review['user_name'] : '' }}!
    </x-mail.heading>

    <x-mail.text>
        Wir haben „{{ $review['name'] }}“ geprüft, aber nicht als eigenen Eintrag ins Wiki übernommen –
        meistens, weil es den Eintrag unter einem anderen Namen schon gibt.
    </x-mail.text>

    @if (filled($review['reason']))
        <x-mail.panel title="Anmerkung unseres Teams">
            <p style="margin:0 0 14px; font-size:16px; line-height:1.6; color:#0f1d22; white-space:pre-line;">{{ $review['reason'] }}</p>
        </x-mail.panel>
    @endif

    @if (filled($review['replacement']))
        <x-mail.text>
            Deine Fänge mit diesem Eintrag haben wir <strong>„{{ $review['replacement'] }}“</strong> zugeordnet –
            es geht also nichts verloren. Du kannst die Zuordnung jederzeit beim Bearbeiten eines Fangs ändern.
        </x-mail.text>
    @endif

    <x-mail.button :url="route('app.catched.index')">Zu deinen Fängen</x-mail.button>

    <x-slot:subcopy>
        Fragen dazu? Schreib uns einfach über „Ticket erstellen“ in der App.
    </x-slot:subcopy>
</x-mail.wrapper>
