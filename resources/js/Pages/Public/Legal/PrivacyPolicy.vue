<template>
    <Head title="Datenschutzerklärung" />

    <LegalPage :sections="sections" updated="Oktober 2026">
        <template #title>Datenschutz&shy;erklärung</template>
        <template #intro>
            Der Schutz deiner persönlichen Daten ist uns wichtig. Diese Erklärung informiert dich über Art, Umfang und
            Zweck der Verarbeitung deiner Daten in PetriLog.
        </template>

        <LegalSection id="verantwortlicher" :number="1" title="Verantwortlicher">
            <div class="rounded-2xl bg-white p-6 ring-1 ring-(--ink)/10">
                <p class="font-log text-[11px] uppercase tracking-[0.2em] text-(--ink)/45">Verantwortlich für die
                    Datenverarbeitung</p>
                <p class="mt-3">
                    <strong>Ing. Lukas Korbitsch</strong><br>
                    Kiesweg 19, Villach, Kärnten, Österreich<br>
                    <a href="mailto:info@korbitsch.at">info@korbitsch.at</a>
                </p>
            </div>
        </LegalSection>

        <LegalSection id="erhebung" :number="2" title="Erhebung und Verarbeitung personenbezogener Daten">
            <p>
                Wir erheben personenbezogene Daten nur, wenn du diese aktiv angibst, z. B. bei der Registrierung oder beim
                Eintragen deiner Fänge. Dazu gehören:
            </p>
            <ul>
                <li>Name und E-Mail-Adresse</li>
                <li>Fangdaten: Fischart, Gewicht, Länge, Fangdatum, Standort, Fotos und weitere Details</li>
                <li>Passwort (verschlüsselt gespeichert)</li>
            </ul>
        </LegalSection>

        <LegalSection id="zweck" :number="3" title="Zweck der Datenverarbeitung">
            <ul>
                <li>Bereitstellung der Funktionen von PetriLog (Fangbuch, Medien-Upload, Kartenintegration)</li>
                <li>Verbesserung der Benutzererfahrung und Funktionalität der App</li>
                <li>Kommunikation im Rahmen der Nutzung (z. B. Passwort-Reset, E-Mail-Benachrichtigungen)</li>
            </ul>
        </LegalSection>

        <LegalSection id="cookies" :number="4" title="Cookies, lokaler Speicher & externe Dienste">
            <p>
                PetriLog verwendet technisch notwendige Cookies bzw. lokalen Speicher (Local Storage), um grundlegende
                Funktionen wie die Anmeldung und deine Cookie-Einwilligung zu ermöglichen. Erst nachdem du dem im
                Cookie-Banner ausdrücklich zugestimmt hast, werden zusätzlich folgende externe Dienste geladen:
            </p>
            <ul>
                <li><strong>Google Analytics</strong> – zur anonymisierten Analyse der Nutzung (IP-Anonymisierung
                    aktiviert), Anbieter: Google Ireland Limited</li>
                <li><strong>Google Maps</strong> – zur Darstellung von Gewässern und Fangorten, Anbieter: Google Ireland
                    Limited</li>
            </ul>
            <p>
                Lehnst du im Cookie-Banner ab, werden diese Dienste nicht geladen – einzelne Funktionen wie die
                Kartenansicht stehen dann nicht zur Verfügung. Deine Wahl kannst du jederzeit über die Löschung der
                Browserdaten zurücksetzen. Bei Nutzung dieser Dienste können Daten an Google in die USA übermittelt werden.
            </p>
            <p>
                Für die Darstellung der Schriftarten nutzen wir <strong>Bunny Fonts</strong> (Anbieter: BunnyWay d.o.o.,
                Slowenien). Beim Laden der Schriften wird technisch bedingt deine IP-Adresse an den Anbieter übermittelt.
            </p>
            <p>
                Für eine eigene <strong>Zugriffsstatistik</strong> zählen wir Seitenaufrufe direkt auf unserem Server –
                ohne Cookies und ohne Speicherung deiner IP-Adresse. Um Besucher eines Tages unterscheiden zu können,
                bilden wir aus IP-Adresse und Browserkennung einen verschlüsselten Kurzwert, der sich täglich ändert und
                keinen Rückschluss auf deine Person zulässt. Gespeichert werden außerdem die aufgerufene Seite, ob du
                eingeloggt warst, und von welcher externen Website du gekommen bist. Die Daten werden nach einem Jahr
                gelöscht.
            </p>
            <p>
                Die <strong>Spot-Karte</strong> im eingeloggten Bereich lädt Kartenkacheln von <strong>basemap.at</strong>
                (Verwaltungsgrundkarte Österreich, betrieben von den österreichischen Bundesländern) und – falls du diese
                Kartenansicht wählst – von <strong>OpenStreetMap</strong> (OpenStreetMap Foundation, Vereinigtes Königreich).
                Dabei wird technisch bedingt deine IP-Adresse an den jeweiligen Kartenserver übermittelt; deine Fänge und
                Spots selbst werden nicht übertragen.
            </p>
            <p>
                Zu jedem Fang ergänzt PetriLog automatisch Wetterdaten von <strong>GeoSphere Austria</strong> (Bundesanstalt
                für Geologie, Geophysik, Klimatologie und Meteorologie, Wien). Dafür übermittelt unser Server ausschließlich
                Koordinaten und Zeitpunkt des Fangs – ohne Bezug zu deiner Person, deinem Namen oder deiner IP-Adresse.
            </p>
        </LegalSection>

        <LegalSection id="weitergabe" :number="5" title="Weitergabe von Daten">
            <p>
                Eine Weitergabe deiner Daten an Dritte erfolgt nur, wenn dies gesetzlich erlaubt ist oder du zuvor
                ausdrücklich eingewilligt hast. Daten können z. B. an Hosting-Anbieter oder Cloud-Dienste für die
                Speicherung von Fotos übermittelt werden.
            </p>
        </LegalSection>

        <LegalSection id="rechte" :number="6" title="Deine Rechte">
            <div class="grid gap-3 sm:grid-cols-2">
                <div v-for="right in rights" :key="right.title" class="rounded-2xl bg-white p-5 ring-1 ring-(--ink)/10">
                    <p class="font-semibold text-(--ink)">{{ right.title }}</p>
                    <p class="mt-1 text-sm">{{ right.text }}</p>
                </div>
            </div>
            <p class="mt-6">
                Zur Ausübung deiner Rechte genügt eine formlose Nachricht an
                <a href="mailto:info@korbitsch.at">info@korbitsch.at</a>.
            </p>
        </LegalSection>

        <LegalSection id="sicherheit" :number="7" title="Datensicherheit">
            <p>
                Wir setzen technische und organisatorische Maßnahmen ein, um deine Daten vor Verlust, Missbrauch oder
                unberechtigtem Zugriff zu schützen. Dazu gehören z. B. verschlüsselte Speicherung von Passwörtern und
                SSL-Verschlüsselung bei der Datenübertragung.
            </p>
        </LegalSection>

        <LegalSection id="aenderungen" :number="8" title="Änderungen der Datenschutzerklärung">
            <p>
                Wir behalten uns vor, diese Datenschutzerklärung anzupassen, damit sie stets den aktuellen rechtlichen
                Anforderungen entspricht oder um Änderungen unserer Dienste umzusetzen. Bitte besuche diese Seite
                regelmäßig.
            </p>
        </LegalSection>
    </LegalPage>
</template>
<script setup>
import { Head } from '@inertiajs/vue3';
import LegalPage from '@/components/public/LegalPage.vue';
import LegalSection from '@/components/public/LegalSection.vue';

const sections = [
    { id: 'verantwortlicher', title: 'Verantwortlicher' },
    { id: 'erhebung', title: 'Erhobene Daten' },
    { id: 'zweck', title: 'Zweck' },
    { id: 'cookies', title: 'Cookies & externe Dienste' },
    { id: 'weitergabe', title: 'Weitergabe von Daten' },
    { id: 'rechte', title: 'Deine Rechte' },
    { id: 'sicherheit', title: 'Datensicherheit' },
    { id: 'aenderungen', title: 'Änderungen' },
];

const rights = [
    { title: 'Auskunft', text: 'Über die zu deiner Person gespeicherten Daten.' },
    { title: 'Berichtigung', text: 'Unrichtige Daten korrigieren lassen.' },
    { title: 'Löschung', text: 'Deine personenbezogenen Daten löschen lassen.' },
    { title: 'Datenübertragbarkeit', text: 'Export deiner Daten in einem gängigen Format.' },
];
</script>
