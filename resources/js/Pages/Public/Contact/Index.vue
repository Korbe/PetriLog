<template>
    <Layout overlay>
        <Head title="Kontakt" />

        <PageHero eyebrow="Kontakt">
            <template #title>Fragen, Ideen oder ein <em class="text-primary-300">Gewässer fehlt?</em></template>
            Schreib uns oder ruf an – wir freuen uns über jede Frage und jedes Feedback.
        </PageHero>

        <section class="max-w-6xl mx-auto px-4 sm:px-6 py-16 md:py-24">
            <div class="grid gap-5 lg:grid-cols-12">

                <!-- Mail -->
                <a href="mailto:info@petrilog.com"
                    class="ph-reveal group relative overflow-hidden rounded-[2rem] bg-(--lake-900) p-8 text-(--paper) md:p-10 lg:col-span-7">
                    <p class="font-log text-[11px] uppercase tracking-[0.2em] text-primary-300">E-Mail</p>
                    <p class="font-display mt-4 break-all text-4xl sm:text-5xl md:text-6xl">info@petrilog.com</p>
                    <p class="mt-4 max-w-md text-(--paper)/65">Für Fragen, Feedback und Support – am besten mit einer kurzen
                        Beschreibung deines Anliegens.</p>
                    <span
                        class="mt-8 inline-flex items-center gap-2 rounded-full bg-(--paper) px-5 py-3 text-sm font-semibold text-(--ink) transition group-hover:bg-white">
                        Mail schreiben
                        <svg class="size-3.5 transition-transform group-hover:translate-x-0.5" viewBox="0 0 16 16"
                            fill="none" aria-hidden="true">
                            <path d="M1 8h13m-5-5 5 5-5 5" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                </a>

                <!-- Phone -->
                <div class="grid gap-5 lg:col-span-5">
                    <a v-for="(person, i) in people" :key="person.tel" :href="`tel:${person.tel}`"
                        class="ph-reveal group flex flex-col justify-between rounded-[2rem] bg-white p-7 ring-1 ring-(--ink)/10 transition hover:ring-(--ink)/25"
                        :style="{ transitionDelay: `${(i + 1) * 100}ms` }">
                        <p class="font-log text-[11px] uppercase tracking-[0.2em] text-(--ink)/50">{{ person.role }}</p>
                        <div class="mt-6">
                            <p class="text-xl font-semibold">{{ person.name }}</p>
                            <p class="font-log mt-1 text-(--ink)/70 group-hover:text-primary-700">{{ person.display }}</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Common topics -->
            <div class="ph-reveal mt-16 border-t border-(--ink)/10 pt-12 md:mt-24">
                <h2 class="font-display text-4xl md:text-5xl">Häufige Anliegen</h2>
                <p class="mt-3 text-(--ink)/65">Ein Klick öffnet eine vorbereitete Mail mit passendem Betreff.</p>
                <ul class="mt-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <li v-for="topic in topics" :key="topic.subject">
                        <a :href="`mailto:info@petrilog.com?subject=${encodeURIComponent(topic.subject)}`"
                            class="flex h-full flex-col rounded-2xl border border-(--ink)/10 bg-(--paper) p-5 transition hover:border-(--ink)/30 hover:bg-white">
                            <span class="font-semibold">{{ topic.subject }}</span>
                            <span class="mt-1 text-sm text-(--ink)/60">{{ topic.text }}</span>
                        </a>
                    </li>
                </ul>
            </div>
        </section>
    </Layout>
</template>
<script setup>
import { Head } from '@inertiajs/vue3';
import Layout from '@/Layouts/Public/Layout.vue';
import PageHero from '@/components/public/PageHero.vue';

const people = [
    { role: 'Vertrieb', name: 'Perschon Thomas', tel: '+436505222753', display: '+43 650 522 27 53' },
    { role: 'Technische Leitung', name: 'Ing. Lukas Korbitsch', tel: '+436701923388', display: '+43 670 192 33 88' },
];

const topics = [
    { subject: 'Gewässer fehlt', text: 'Dein See oder Fluss ist noch nicht im Wiki?' },
    { subject: 'Fehler gefunden', text: 'Etwas funktioniert nicht wie erwartet.' },
    { subject: 'Abo & Rechnung', text: 'Fragen zu PetriLog Plus und Zahlungen.' },
    { subject: 'Idee für PetriLog', text: 'Dir fehlt eine Funktion im Fangbuch?' },
];
</script>
