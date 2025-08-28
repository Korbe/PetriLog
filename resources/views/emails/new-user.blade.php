@component('mail::message')
# Neuer User, {{ $user->name }} ({{ $user->email }})!

Danke,<br>
{{ config('app.name') }}
@endcomponent