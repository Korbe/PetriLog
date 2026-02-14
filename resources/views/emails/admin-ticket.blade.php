@component('mail::message')

# Neues Ticket

Von: **{{ $user->name }} ({{ $user->email }})**

**Titel:** {{ $ticket->title }}  
**Kategorie:** {{ $ticket->category }}  

**Beschreibung:**  
{{ $ticket->description ?? 'Keine Beschreibung' }}

**Schritte zur Reproduktion:**  
{{ $ticket->steps }}

**URL:**  
{{ $ticket->url }}


Danke,<br>
{{ config('app.name') }}
@endcomponent