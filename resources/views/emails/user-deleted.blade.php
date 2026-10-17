<x-mail.wrapper preheader="Dein PetriLog-Account wurde gelöscht.">
    <x-mail.heading eyebrow="Account gelöscht">
        Schade, dass du gehst{{ $name ? ', ' . $name : '' }}.
    </x-mail.heading>

    <x-mail.text>
        Dein Account wurde <strong>erfolgreich gelöscht</strong>. Aktive Abonnements haben wir
        selbstverständlich beendet.
    </x-mail.text>

    <x-mail.text>
        Danke, dass du Teil von PetriLog warst. Falls du irgendwann zurückkommen möchtest, bist du
        jederzeit wieder herzlich willkommen – bis dahin wünschen wir dir viele schöne Stunden am Wasser.
    </x-mail.text>

    <x-slot:subcopy>
        Du hast deinen Account nicht selbst gelöscht? Dann melde dich bitte umgehend unter
        <a href="mailto:info@petrilog.com" style="color:#0c6fc0;">info@petrilog.com</a>.
    </x-slot:subcopy>
</x-mail.wrapper>
