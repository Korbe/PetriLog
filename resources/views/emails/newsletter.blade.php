<x-mail.wrapper :preheader="$subjectText">
    <x-mail.heading eyebrow="Newsletter">
        {{ $subjectText }}
    </x-mail.heading>

    <x-mail.text>
        Hallo{{ $user ? ' ' . $user->name : '' }},
    </x-mail.text>

    {{ $body }}

    @if ($user)
        <x-slot:subcopy>
            Du bekommst diese E-Mail, weil du den PetriLog-Newsletter erhältst.
            <a href="{{ URL::signedRoute('public.newsletter.unsubscribe', $user) }}" style="color:#0c6fc0;">Newsletter abbestellen</a>
        </x-slot:subcopy>
    @endif
</x-mail.wrapper>
