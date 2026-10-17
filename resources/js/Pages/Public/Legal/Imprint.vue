<template>
    <Head title="Impressum" />

    <LegalPage :sections="sections">
        <template #title>Impressum</template>
        <template #intro>Angaben gemäß § 5 ECG und Offenlegung nach § 25 Mediengesetz.</template>

        <LegalSection id="angaben" :number="1" title="Diensteanbieter & Kontakt">
            <div class="not-prose grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl bg-white p-6 ring-1 ring-(--ink)/10">
                    <p class="font-log text-[11px] uppercase tracking-[0.2em] text-(--ink)/45">Diensteanbieter</p>
                    <p class="mt-3 leading-relaxed text-(--ink)">
                        <strong>Ing. Lukas Korbitsch</strong><br>
                        Webentwicklung &amp; IT Dienstleistungen<br>
                        Kiesweg 19<br>
                        Villach, Kärnten<br>
                        Österreich
                    </p>
                </div>
                <div class="rounded-2xl bg-white p-6 ring-1 ring-(--ink)/10">
                    <p class="font-log text-[11px] uppercase tracking-[0.2em] text-(--ink)/45">Kontakt</p>
                    <p class="mt-3 leading-relaxed text-(--ink)">
                        E-Mail: <a href="mailto:info@korbitsch.at">info@korbitsch.at</a><br>
                        Telefon: <a href="tel:+436701923388">+43 670 1923388</a>
                    </p>
                </div>
            </div>

            <dl class="mt-8 divide-y divide-(--ink)/10 border-y border-(--ink)/10">
                <div v-for="fact in facts" :key="fact.label" class="grid gap-1 py-4 sm:grid-cols-3 sm:gap-6">
                    <dt class="font-log text-[11px] uppercase tracking-[0.15em] text-(--ink)/50 sm:pt-1">{{ fact.label }}</dt>
                    <dd class="text-(--ink) sm:col-span-2" v-html="fact.value"></dd>
                </div>
            </dl>
        </LegalSection>

        <LegalSection id="bildrechte" :number="2" title="Bildrechte">
            <p>
                Die Bilder sind KI generiert und unterliegen keinem Urheberrecht. Sie wurden mit ChatGPT erstellt. Oder
                sie sind lizenzfreie Bilder von Unsplash und Pexels oder sind selbst erstellt.
            </p>
        </LegalSection>

        <LegalSection id="haftung-inhalte" :number="3" title="Haftung für Inhalte">
            <p>
                Die Inhalte dieser Website wurden mit größtmöglicher Sorgfalt erstellt. Dennoch kann keine Gewähr für die
                Richtigkeit, Vollständigkeit oder Aktualität der bereitgestellten Informationen übernommen werden.
            </p>
            <p>
                Diese Website enthält Verlinkungen zu externen Diensten und Plattformen, darunter Facebook, Instagram und
                WhatsApp. Für die Inhalte dieser externen Websites sind ausschließlich die jeweiligen Betreiber
                verantwortlich. Auf deren Inhalte und zukünftige Änderungen besteht kein Einfluss, weshalb hierfür keine
                Haftung übernommen wird.
            </p>
        </LegalSection>

        <LegalSection id="haftung-links" :number="4" title="Haftung für Links">
            <p>
                Diese Website enthält Links zu externen Websites Dritter, auf deren Inhalte kein Einfluss besteht. Für diese
                Inhalte wird keine Haftung übernommen.
            </p>
        </LegalSection>

        <LegalSection id="urheberrecht" :number="5" title="Urheberrecht">
            <p>
                Die auf dieser Website erstellten Inhalte und Werke unterliegen dem Urheberrecht. Beiträge Dritter sind
                entsprechend gekennzeichnet.
            </p>
        </LegalSection>
    </LegalPage>
</template>
<script setup>
import { Head } from '@inertiajs/vue3';
import LegalPage from '@/components/public/LegalPage.vue';
import LegalSection from '@/components/public/LegalSection.vue';

const sections = [
    { id: 'angaben', title: 'Diensteanbieter & Kontakt' },
    { id: 'bildrechte', title: 'Bildrechte' },
    { id: 'haftung-inhalte', title: 'Haftung für Inhalte' },
    { id: 'haftung-links', title: 'Haftung für Links' },
    { id: 'urheberrecht', title: 'Urheberrecht' },
];

const facts = [
    { label: 'Rechtsform', value: 'Einzelunternehmen' },
    { label: 'Tätigkeitsbereich bzw. Unternehmensgegenstand', value: 'Entwicklung individueller Webanwendungen sowie IT-Dienstleistungen.' },
    { label: 'Kammer / Berufsverband', value: 'Wirtschaftskammer Kärnten' },
    { label: 'GISA-Zahl', value: '38890017<br>39963185' },
    { label: 'Aufsichtsbehörde gem. ECG (E-Commerce Gesetz)', value: 'Bezirkshauptmannschaft Villach' },
];
</script>
