@php
    $c = $catched;
    $num = fn($v, $d = 1) => number_format((float) $v, $d, ',', '.');
    $compass = fn($deg) => ['N', 'NO', 'O', 'SO', 'S', 'SW', 'W', 'NW'][((int) round($deg / 45)) % 8];
    $trend = $c->weather_pressure_trend;
    $rows = array_filter([
        'Gefangen' => $c->date->timezone('Europe/Vienna')->format('d.m.Y, H:i') . ' Uhr' . (($c->lake ?? $c->river) ? ' · ' . ($c->lake ?? $c->river)->name : ''),
        'Lufttemperatur' => $c->weather_temperature !== null ? $num($c->weather_temperature) . ' °C' : null,
        'Luftdruck' => $c->weather_pressure !== null
            ? $num($c->weather_pressure, 0) . ' hPa' . ($trend !== null ? ($trend >= 1 ? ' · steigend' : ($trend <= -1 ? ' · fallend' : ' · stabil')) : '')
            : null,
        'Wind' => $c->weather_wind_speed !== null
            ? $num($c->weather_wind_speed, 0) . ' km/h' . ($c->weather_wind_direction !== null ? ' aus ' . $compass($c->weather_wind_direction) : '')
            : null,
        'Luftfeuchte' => $c->weather_humidity !== null ? $c->weather_humidity . ' %' : null,
        'Niederschlag' => $c->weather_precipitation !== null ? $num($c->weather_precipitation) . ' mm' : null,
    ], fn($value) => $value !== null);
@endphp
<x-mail.wrapper :preheader="'Temperatur, Luftdruck und Wind zum Zeitpunkt deines Fangs.'">
    <x-mail.heading eyebrow="Wetter zum Fang">
        Das Wetter zu deinem {{ $fish }} ist da
    </x-mail.heading>

    <x-mail.text>
        Hallo {{ $c->user->name }}, GeoSphere Austria hat die Wetterdaten für deinen Fangzeitpunkt veröffentlicht.
        So war das Wetter, als der Fisch gebissen hat:
    </x-mail.text>

    <x-mail.facts :rows="$rows" />

    <x-mail.button :url="route('app.catched.show', $c->id)">
        Fang ansehen
    </x-mail.button>

    <x-slot:subcopy>
        Du bekommst diese E-Mail, weil du beim Eintragen „Benachrichtigen, wenn die Wetterdaten da sind“ gewählt hast.
        Wetterdaten: GeoSphere Austria (CC BY 4.0).
    </x-slot:subcopy>
</x-mail.wrapper>
