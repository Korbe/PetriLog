@component('mail::message')

# Neuer Bug-Report

Von: **{{ $user->name }} ({{ $user->email }})**

**Titel:** {{ $bug->title }}  
**Kategorie:** {{ $bug->category }}  

**Beschreibung:**  
{{ $bug->description ?? 'Keine Beschreibung' }}

**Schritte zur Reproduktion:**  
{{ $bug->steps }}

**URL:**  
{{ $bug->url }}


Danke,<br>
{{ config('app.name') }}
@endcomponent