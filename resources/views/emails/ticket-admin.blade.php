@use('App\Support\MailHtml')
@use('Illuminate\Support\HtmlString')
@php
    $link = fn (string $href, string $label) => new HtmlString('<a href="' . e($href) . '" style="color:#0c6fc0;">' . e($label) . '</a>');

    // Nur http(s) und App-Pfade (/dashboard) verlinken, alles andere als Text
    $rawUrl = (string) $ticket->url;
    $href = preg_match('#^https?://#i', $rawUrl) ? $rawUrl : (str_starts_with($rawUrl, '/') ? url($rawUrl) : null);
@endphp
<x-mail.wrapper :preheader="$category . ' · ' . $ticket->title" :signoff="false">
    <x-mail.heading eyebrow="Support · Neues Ticket">
        {{ $ticket->title }}
    </x-mail.heading>

    <x-mail.facts :rows="[
        'Von' => $user->name,
        'E-Mail' => $link('mailto:' . $user->email, $user->email),
        'Telefon' => $user->tel,
        'Kategorie' => $category,
        'Seite / URL' => $href ? $link($href, $rawUrl) : $rawUrl,
        'Eingegangen' => $ticket->created_at?->timezone('Europe/Vienna')->format('d.m.Y, H:i') . ' Uhr',
    ]" />

    <x-mail.panel title="Beschreibung">
        @if (filled($ticket->description))
            {{ MailHtml::format($ticket->description) }}
        @else
            <p style="margin:0 0 14px; color:#5b6669;">Keine Beschreibung angegeben.</p>
        @endif
    </x-mail.panel>

    @if (filled($ticket->steps))
        <x-mail.panel title="Schritte zur Reproduktion">
            {{ MailHtml::format($ticket->steps) }}
        </x-mail.panel>
    @endif

    <x-mail.button :url="route('admin.tickets.show', $ticket)">Ticket im Admin öffnen</x-mail.button>
</x-mail.wrapper>
