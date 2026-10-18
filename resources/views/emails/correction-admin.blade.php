@use('Illuminate\Support\HtmlString')
@php
    $c = $correction;
    $mailto = $c->user
        ? new HtmlString('<a href="' . e('mailto:' . $c->user->email) . '" style="color:#0c6fc0;">' . e($c->user->email) . '</a>')
        : null;
    $source = filled($c->source) && preg_match('#^https?://#i', $c->source)
        ? new HtmlString('<a href="' . e($c->source) . '" style="color:#0c6fc0;">' . e($c->source) . '</a>')
        : $c->source;
@endphp
<x-mail.wrapper :preheader="$c->categoryLabel() . ' · ' . $name" :signoff="false">
    <x-mail.heading :eyebrow="'Wiki · Fehler gemeldet · ' . \App\Models\Correction::TYPE_LABELS[$c->typeKey()]">
        {{ $name }}
    </x-mail.heading>

    <x-mail.facts :rows="array_filter([
        'Betrifft' => $c->categoryLabel(),
        'Von' => $c->user?->name ?? 'gelöschtem User',
        'E-Mail' => $mailto,
        'Quelle' => $source,
    ], fn ($value) => $value !== null)" />

    <x-mail.panel title="Meldung">
        <p style="margin:0 0 14px; font-size:16px; line-height:1.6; color:#0f1d22; white-space:pre-line;">{{ $c->message }}</p>
    </x-mail.panel>

    <x-mail.button :url="route('admin.corrections.index')">Meldung prüfen</x-mail.button>
</x-mail.wrapper>
