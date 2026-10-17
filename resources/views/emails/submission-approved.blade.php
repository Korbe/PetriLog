<x-mail.wrapper :preheader="'„' . $review['name'] . '“ ist jetzt für alle Angler im Wiki sichtbar.'">
    <x-mail.heading :eyebrow="'Vorschlag freigegeben · ' . $review['label']">
        Danke{{ $review['user_name'] ? ', ' . $review['user_name'] : '' }} – „{{ $review['name'] }}“ ist jetzt offiziell!
    </x-mail.heading>

    <x-mail.text>
        Wir haben deinen Vorschlag geprüft und freigegeben. Der Eintrag ist ab sofort für alle PetriLog-Angler
        auswählbar und im Wiki zu finden. Deine bisherigen Fänge bleiben unverändert zugeordnet.
    </x-mail.text>

    <x-mail.text>
        Mit Beiträgen wie deinem wird PetriLog für alle besser – Petri Heil!
    </x-mail.text>

    <x-mail.button :url="$review['url'] ?? route('app.catched.index')">
        {{ $review['url'] ? 'Eintrag im Wiki ansehen' : 'Zu deinen Fängen' }}
    </x-mail.button>
</x-mail.wrapper>
