<x-mail.wrapper :preheader="'Account gelöscht: ' . ($user['name'] ?? '')" :signoff="false">
    <x-mail.heading eyebrow="Admin · Account gelöscht">
        {{ $user['name'] ?? 'Ein User' }} hat seinen Account gelöscht
    </x-mail.heading>

    <x-mail.text muted>
        Der Account wurde selbstständig gelöscht. Das sind die zuletzt gespeicherten Daten:
    </x-mail.text>

    <x-mail.facts :rows="[
        'Name' => $user['name'] ?? null,
        'E-Mail' => $user['email'] ?? null,
        'Telefon' => $user['tel'] ?? null,
        'Bundesland' => $user['state'] ?? null,
        'Gelöscht' => now('Europe/Vienna')->format('d.m.Y, H:i') . ' Uhr',
    ]" />
</x-mail.wrapper>
