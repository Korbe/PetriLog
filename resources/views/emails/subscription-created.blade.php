<x-mail.wrapper preheader="Dein PetriLog-Abo ist aktiv – ab jetzt ohne Limit.">
    <x-mail.heading eyebrow="Abo aktiv">
        Danke für dein Vertrauen, {{ $user->name }}!
    </x-mail.heading>

    <x-mail.text>
        Dein Abo ist aktiv. Ab sofort nutzt du <strong>alle Funktionen von PetriLog</strong> ohne Einschränkung
        und trägst so viele Fänge ein, wie du willst.
    </x-mail.text>

    <x-mail.text>
        Rechnungen, Zahlungsmethode und Kündigung findest du jederzeit in deinem Profil unter
        <a href="{{ url('/billing') }}" style="color:#0c6fc0;">Abo &amp; Rechnungen</a>.
    </x-mail.text>

    <x-mail.button :url="route('app.dashboard')">Zum Dashboard</x-mail.button>

    <x-slot:subcopy>
        Fragen oder Ideen? Schreib uns jederzeit an
        <a href="mailto:info@petrilog.com" style="color:#0c6fc0;">info@petrilog.com</a>.
    </x-slot:subcopy>
</x-mail.wrapper>
