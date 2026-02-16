<x-mail.wrapper>
    <p>
        Hallo{{ $name ? ' ' . $name : '' }},
    </p>

    {!! $content !!}

</x-mail.wrapper>
