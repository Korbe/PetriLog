@component('mail::message')
Hallo {{ $user->name }},

{!! $content !!}

Danke & Petri Heil 🎣  
{{ config('app.name') }}

<p style="font-size: 12px; color: #999;">
Du möchtest keine Newsletter mehr erhalten?
<br>
<a href="{{ URL::signedRoute('newsletter.unsubscribe', $user) }}">
Newsletter abbestellen
</a>
</p>
@endcomponent