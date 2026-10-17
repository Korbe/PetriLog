@use('App\Support\MailHtml')
@use('Illuminate\Support\HtmlString')
@php
    $mailto = new HtmlString('<a href="' . e('mailto:' . $submission['submitter_email']) . '" style="color:#0c6fc0;">' . e($submission['submitter_email']) . '</a>');
@endphp
<x-mail.wrapper :preheader="$submission['label'] . ' · ' . $submission['name'] . ' wartet auf Freigabe'" :signoff="false">
    <x-mail.heading :eyebrow="'Wiki · Neuer Vorschlag · ' . $submission['label']">
        {{ $submission['name'] }}
    </x-mail.heading>

    <x-mail.text>
        Ein User hat einen neuen Eintrag vorgeschlagen. Bis zur Freigabe ist er nur für ihn selbst sichtbar.
    </x-mail.text>

    <x-mail.facts :rows="array_filter([
        'Typ' => $submission['label'],
        'Von' => $submission['submitter_name'],
        'E-Mail' => $mailto,
        'Bundesland' => $submission['states'] ? implode(', ', $submission['states']) : null,
        'Eingegangen' => $submission['created_at'],
    ], fn ($value) => $value !== null)" />

    {{-- Texte wurden beim Einreichen als escapter Klartext gespeichert --}}
    @foreach (['desc' => 'Beschreibung', 'fishing_rights' => 'Fischereirecht / Verein', 'ticket_sales' => 'Lizenz erhältlich bei'] as $key => $title)
        @if (filled($submission[$key] ?? null))
            <x-mail.panel :title="$title">
                {{ MailHtml::format($submission[$key]) }}
            </x-mail.panel>
        @endif
    @endforeach

    <x-mail.button :url="route('admin.submissions.index')">Vorschlag prüfen</x-mail.button>
</x-mail.wrapper>
