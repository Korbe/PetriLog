<x-mail.wrapper preheader="Wir haben deine Meldung erhalten und kümmern uns darum.">
    <x-mail.heading eyebrow="Ticket erhalten">
        Danke für deine Meldung{{ $user->name ? ', ' . $user->name : '' }}!
    </x-mail.heading>

    <x-mail.text>
        Wir haben dein Ticket erhalten und bearbeiten es so rasch wie möglich. Falls wir noch Infos
        brauchen, melden wir uns direkt bei dir.
    </x-mail.text>

    @if ($ticket)
        <x-mail.facts :rows="[
            'Titel' => $ticket->title,
            'Kategorie' => $category,
            'Eingegangen' => $ticket->created_at?->timezone('Europe/Vienna')->format('d.m.Y, H:i') . ' Uhr',
        ]" />
    @endif

    <x-slot:subcopy>
        Diese E-Mail bestätigt nur den Eingang deines Tickets – bitte nicht darauf antworten.
    </x-slot:subcopy>
</x-mail.wrapper>
