<x-mail.wrapper>
    @if ($user)
        <p>Hallo {{ $user->name }},</p>
    @else
        <p>Hallo,</p>
    @endif

    {!! $content !!}

    @if ($user)
        @slot('subcopy')
            <p style="font-size:12px; color:#999;">
                Du möchtest keine Newsletter mehr erhalten?<br>
                <a href="{{ URL::signedRoute('public.newsletter.unsubscribe', $user) }}">
                    Newsletter abbestellen
                </a>
            </p>
        @endslot
    @endif
</x-mail.wrapper>
