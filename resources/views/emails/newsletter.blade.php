<x-mail.wrapper>
    @if ($user)
        <p>Hallo {{ $user->name }},</p>
    @else
        <p>Hallo,</p>
    @endif

    {!! $content !!}

    <p>Danke & Petri Heil 🎣<br>{{ config('app.name') }}</p>

    @if ($user)
        <p style="font-size:12px; color:#999;">
            Du möchtest keine Newsletter mehr erhalten?<br>
            <a href="{{ URL::signedRoute('newsletter.unsubscribe', $user) }}">Newsletter abbestellen</a>
        </p>
    @endif
</x-mail.wrapper>
