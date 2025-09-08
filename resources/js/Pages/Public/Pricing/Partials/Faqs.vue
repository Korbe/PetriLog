<script setup>
import Accordion from './Accordion.vue';

const faqs = [
    {
        question: 'Was ist PetriLog?',
        answer: 'PetriLog ist dein digitales Angel-Tagebuch, in dem du alle gefangenen Fische mit Länge, Gewicht, Tiefe, Wassertemperatur und Fotos festhalten kannst.',
        active: true
    },
    {
        question: 'Kann ich mehrere Fänge pro Tag eintragen?',
        answer: 'Ja! Du kannst unbegrenzt viele Einträge pro Tag hinzufügen und behältst so den vollen Überblick über deine Fänge.',
    },
    {
        question: 'Wie viele Fotos kann ich pro Fang speichern?',
        answer: 'Du kannst bis zu 3 Bilder pro Fang hinzufügen, um deinen größten Erfolg visuell festzuhalten.'
    },
    {
        question: 'Kann ich meine Fänge mit Freunden teilen?',
        answer: 'Ja! Du kannst einzelne Fänge oder dein gesamtes Tagebuch über Social Media oder einen direkten Link teilen.'
    },
    {
        question: 'Sind meine Daten sicher gespeichert?',
        answer: 'Alle deine Daten werden in der Cloud gespeichert und sind jederzeit von all deinen Geräten aus zugänglich.'
    },
    {
        question: 'Gibt es Statistiken und Analysen?',
        answer: 'Ja! PetriLog zeigt dir Fangstatistiken nach Tag, Monat und Jahr, inklusive Diagrammen und Gesamtübersichten.'
    },
    {
        question: 'Funktioniert PetriLog auf dem Smartphone?',
        answer: 'Ja! PetriLog ist für mobile Geräte optimiert, sodass du unterwegs direkt Fänge eintragen und nachschauen kannst.'
    },
    {
        question: 'Wie kann ich PetriLog abonnieren?',
        answer: 'Es gibt nur ein Jahresabo, das alle Funktionen freischaltet. Du kannst das Abo direkt auf unserer Website abschließen.'
    },
    {
        question: 'Gibt es ein Monatsabo?',
        answer: 'Es gibt nur ein Jahresabo, dass du buchen kannst.'
    }
];
</script>

<template>
    <section>
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="pb-12 md:pb-20">
                <div class="max-w-3xl mx-auto text-center pb-12 md:pb-20">
                    <h2 class="text-3xl md:text-4xl font-bold">Häufig gestellte Fragen</h2>
                </div>
                <div class="max-w-3xl mx-auto">
                    <div class="space-y-5">
                        <template v-for="(faq, index) in faqs">
                            <Accordion :title="faq.question" :id="`faqs-${index}`" :active="faq.active">
                                {{ faq.answer }}
                            </Accordion>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>