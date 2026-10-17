@use('Illuminate\Support\HtmlString')
@php
    $link = fn (string $href, string $label) => new HtmlString('<a href="' . e($href) . '" style="color:#0c6fc0;">' . e($label) . '</a>');
@endphp
<x-mail.wrapper :preheader="'Neue Registrierung: ' . $user->name" :signoff="false">
    <x-mail.heading eyebrow="Admin · Neue Registrierung">
        {{ $user->name }} ist neu dabei
    </x-mail.heading>

    <x-mail.facts :rows="[
        'Name' => $user->name,
        'E-Mail' => $link('mailto:' . $user->email, $user->email),
        'Telefon' => $user->tel,
        'Bundesland' => $user->state?->name,
        'Registriert' => $user->created_at?->timezone('Europe/Vienna')->format('d.m.Y, H:i') . ' Uhr',
    ]" />

    <x-mail.button :url="route('admin.user.index')">Zur User-Übersicht</x-mail.button>
</x-mail.wrapper>
