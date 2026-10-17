<template>
    <Head title="Nutzungsbedingungen" />

    <LegalPage :sections="sections" updated="August 2025">
        <template #title>Nutzungs&shy;bedingungen</template>
        <template #intro>
            Willkommen bei PetriLog. Durch die Nutzung unserer Webanwendung erklären Sie sich mit diesen
            Nutzungsbedingungen einverstanden. Bitte lesen Sie sie sorgfältig durch.
        </template>

        <LegalSection v-for="(s, i) in sections" :id="s.id" :key="s.id" :number="i + 1" :title="s.title">
            <p v-for="(paragraph, p) in s.text" :key="p">{{ paragraph }}</p>
        </LegalSection>
    </LegalPage>
</template>
<script setup>
import { Head } from '@inertiajs/vue3';
import LegalPage from '@/components/public/LegalPage.vue';
import LegalSection from '@/components/public/LegalSection.vue';

const sections = [
    {
        id: 'geltungsbereich', title: 'Geltungsbereich', text: [
            'Diese Allgemeinen Geschäftsbedingungen (AGB) gelten für alle Verträge, die zwischen den Nutzern (im Folgenden „Mitglied“) und der Plattform PetriLog geschlossen werden.',
        ],
    },
    {
        id: 'vertragsgegenstand', title: 'Vertragsgegenstand', text: [
            'PetriLog bietet seinen Mitgliedern die Möglichkeit, Fangdaten, Gewässerinformationen sowie Medien (z. B. Fotos) zu erfassen, zu speichern und einzusehen. Die Nutzung ist an eine kostenpflichtige Mitgliedschaft gebunden.',
        ],
    },
    {
        id: 'mitgliedschaft', title: 'Mitgliedschaft und Preise', text: [
            'Die Mitgliedschaft kostet jährlich 49,99 € und verlängert sich automatisch um ein weiteres Jahr, sofern sie nicht rechtzeitig gekündigt wird.',
        ],
    },
    {
        id: 'vertragsabschluss', title: 'Vertragsabschluss', text: [
            'Der Vertrag kommt mit Abschluss des Registrierungsprozesses und Bestätigung durch PetriLog zustande.',
        ],
    },
    {
        id: 'widerrufsrecht', title: 'Widerrufsrecht', text: [
            'Mitglieder haben das Recht, binnen 14 Tagen ohne Angabe von Gründen diesen Vertrag zu widerrufen. Die Frist beträgt 14 Tage ab dem Tag des Vertragsabschlusses.',
            'Um das Widerrufsrecht auszuüben, muss das Mitglied PetriLog mittels einer eindeutigen Erklärung (z. B. per E-Mail) über den Entschluss informieren.',
        ],
    },
    {
        id: 'kuendigung', title: 'Kündigung', text: [
            'Die Mitgliedschaft kann jederzeit zum Ende der jeweiligen Laufzeit gekündigt werden. Ohne Kündigung verlängert sich die Mitgliedschaft automatisch um ein weiteres Jahr.',
        ],
    },
    {
        id: 'pflichten', title: 'Pflichten der Mitglieder', text: [
            'Mitglieder verpflichten sich, nur rechtmäßige und wahrheitsgemäße Inhalte zu veröffentlichen. Insbesondere dürfen keine beleidigenden, rechtswidrigen, urheberrechtsverletzenden oder umweltschädigenden Inhalte hochgeladen werden.',
        ],
    },
    {
        id: 'nutzungsrechte', title: 'Inhalte und Nutzungsrechte', text: [
            'Mitglieder behalten alle Rechte an ihren hochgeladenen Inhalten. Sie räumen PetriLog jedoch ein einfaches Nutzungsrecht ein, diese Inhalte zum Betrieb der Plattform zu speichern, darzustellen und technisch zu verarbeiten.',
        ],
    },
    {
        id: 'sichtbarkeit', title: 'Sichtbarkeit von Inhalten', text: [
            'Inhalte können privat oder öffentlich gespeichert werden. Öffentlich freigegebene Inhalte sind für andere Mitglieder sichtbar. Für die Weiterverwendung durch Dritte übernimmt PetriLog keine Haftung.',
        ],
    },
    {
        id: 'verfuegbarkeit', title: 'Verfügbarkeit', text: [
            'PetriLog bemüht sich um eine hohe Verfügbarkeit der Plattform, übernimmt jedoch keine Garantie für eine jederzeit unterbrechungsfreie Nutzung.',
        ],
    },
    {
        id: 'haftung', title: 'Haftung', text: [
            'PetriLog haftet nur für Schäden, die auf vorsätzlichem oder grob fahrlässigem Verhalten beruhen. Bei leichter Fahrlässigkeit nur bei Verletzung wesentlicher Vertragspflichten.',
        ],
    },
    {
        id: 'datenschutz', title: 'Datenschutz', text: [
            'Die Verarbeitung personenbezogener Daten erfolgt gemäß der Datenschutzerklärung von PetriLog.',
        ],
    },
    {
        id: 'aenderungen', title: 'Änderungen der Nutzungsbedingungen', text: [
            'PetriLog behält sich vor, diese Nutzungsbedingungen zu ändern. Änderungen werden rechtzeitig bekannt gegeben.',
        ],
    },
    {
        id: 'tod', title: 'Tod eines Mitglieds', text: [
            'Im Falle des Todes eines Mitglieds kann der Vertrag außerordentlich beendet werden.',
            'Die Kündigung muss durch eine berechtigte Person (z. B. Angehörige oder gesetzliche Vertreter) erfolgen. PetriLog kann zum Nachweis eine Kopie der Sterbeurkunde oder einen vergleichbaren amtlichen Nachweis verlangen.',
            'Nach Beendigung des Vertrags wird der Benutzeraccount mit allen Daten gelöscht. Personenbezogene Daten werden gemäß den gesetzlichen Vorgaben gelöscht oder anonymisiert, sofern keine gesetzlichen Aufbewahrungspflichten bestehen.',
            'Öffentlich geteilte Inhalte können auf Wunsch der Angehörigen entfernt oder anonymisiert werden.',
        ],
    },
    {
        id: 'schlussbestimmungen', title: 'Schlussbestimmungen', text: [
            'Sollten einzelne Bestimmungen unwirksam sein, bleibt die Wirksamkeit der übrigen unberührt. Es gilt das Recht der Republik Österreich.',
        ],
    },
];
</script>
