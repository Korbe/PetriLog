<template>
    <Layout>

        <Head title="Nutzungsbedingungen">

            <title>Datenschutzerklärung</title>
            <meta name="description"
                content="Lies die Datenschutzerklärung von PetriLog. Erfahre, wie wir deine Daten schützen, welche Informationen wir speichern und wie du deine Rechte wahrnehmen kannst.">
            <meta name="keywords"
                content="PetriLog, Datenschutzerklärung, Datenschutz, Angler, Fangbuch, persönliche Daten">

            <!-- Open Graph / Facebook -->
            <meta property="og:title" content="PetriLog - Datenschutzerklärung">
            <meta property="og:description"
                content="Lies die Datenschutzerklärung von PetriLog. Erfahre, wie wir deine Daten schützen, welche Informationen wir speichern und wie du deine Rechte wahrnehmen kannst.">
            <meta property="og:type" content="website">
            <meta property="og:url" content="https://petrilog.com/privacy-policy">
            <meta property="og:image" content="https://petrilog.com/logo.png">

            <!-- Twitter Card -->
            <meta name="twitter:card" content="summary_large_image">
            <meta name="twitter:title" content="PetriLog - Datenschutzerklärung">
            <meta name="twitter:description"
                content="Lies die Datenschutzerklärung von PetriLog. Erfahre, wie wir deine Daten schützen, welche Informationen wir speichern und wie du deine Rechte wahrnehmen kannst.">
            <meta name="twitter:image" content="https://petrilog.com/logo.png">

        </Head>

        <section class="relative">

            <PageIllustration />

            <div class="max-w-6xl mx-auto px-4 sm:px-6">

                <!-- Hero content -->
                <div class="pt-32 pb-12 md:pt-10 md:pb-20">

                    <!-- Section header -->
                    <div class=" pb-12">
                        <h1 class="text-center text-5xl md:text-6xl font-bold mb-6">
                            Nutzungsbedingungen</h1>
                        <div class="max-w-3xl mx-auto">
                            <p class="mb-4">
                                Willkommen bei <strong>PetriLog</strong>. Durch die Nutzung unserer Webanwendung
                                erklären Sie sich mit diesen Nutzungsbedingungen einverstanden. Bitte lesen Sie sie
                                sorgfältig durch.
                            </p>

                            <h2 class="text-2xl font-semibold mt-8 mb-4">1. Geltungsbereich</h2>
                            <p class="mb-4">
                                Diese Allgemeinen Geschäftsbedingungen (AGB) gelten für alle Verträge, die zwischen den
                                Nutzern (im Folgenden
                                „Mitglied“) und der Plattform Petrilog geschlossen werden.
                            </p>

                            <h2 class="text-2xl font-semibold mt-8 mb-4">2. Vertragsgegenstand</h2>
                            <p class="mb-4">
                                Petrilog bietet seinen Mitgliedern die Möglichkeit, Gewässerdaten (Seen, Tiefen, etc.)
                                zu erfassen und einzusehen.
                                Die Nutzung ist an eine kostenpflichtige Mitgliedschaft gebunden.
                            </p>

                            <h2 class="text-2xl font-semibold mt-8 mb-4">3. Mitgliedschaft und Preise</h2>
                            <p class="mb-4">
                                Die Mitgliedschaft kostet jährlich 49,99 € und verlängert sich automatisch um ein
                                weiteres Jahr, sofern sie nicht
                                rechtzeitig gekündigt wird.
                            </p>

                            <h2 class="text-2xl font-semibold mt-8 mb-4">4. Vertragsabschluss</h2>
                            <p class="mb-4">
                                Der Vertrag kommt mit Abschluss des Registrierungsprozesses und Bestätigung durch
                                Petrilog zustande.
                            </p>

                            <h2 class="text-2xl font-semibold mt-8 mb-4">5. Widerrufsrecht</h2>
                            <p class="mb-4">
                                Mitglieder haben das Recht, binnen 14 Tagen ohne Angabe von Gründen diesen Vertrag zu
                                widerrufen. Die Frist beträgt
                                14 Tage ab dem Tag des Vertragsabschlusses.
                            </p>
                            <p class="mb-4">
                                Um das Widerrufsrecht auszuüben, muss das Mitglied Petrilog mittels einer eindeutigen
                                Erklärung (z. B. per E-Mail)
                                über den Entschluss, diesen Vertrag zu widerrufen, informieren.
                            </p>

                            <h2 class="text-2xl font-semibold mt-8 mb-4">6. Kündigung</h2>
                            <p class="mb-4">
                                Die Mitgliedschaft kann jederzeit zum Ende der jeweiligen Laufzeit gekündigt werden.
                                Ohne Kündigung verlängert sich
                                die Mitgliedschaft automatisch um ein weiteres Jahr.
                            </p>

                            <h2 class="text-2xl font-semibold mt-8 mb-4">7. Haftung</h2>
                            <p class="mb-4">
                                Petrilog haftet nur für Schäden, die auf einer vorsätzlichen oder grob fahrlässigen
                                Pflichtverletzung beruhen. Für
                                leichte Fahrlässigkeit haftet Petrilog nur bei Verletzung wesentlicher
                                Vertragspflichten.
                            </p>

                            <h2 class="text-2xl font-semibold mt-8 mb-4">8. Schlussbestimmungen</h2>
                            <p class="mb-4">
                                Sollten einzelne Bestimmungen dieser AGB unwirksam sein, bleibt die Wirksamkeit der
                                übrigen Bestimmungen unberührt.
                                Es gilt das Recht der Bundesrepublik Deutschland.
                            </p>

                            <p class="mt-8 text-gray-600">
                                Stand: August 2025
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </Layout>
</template>
<script setup>
import Layout from '@/Layouts/Public/Layout.vue';
import { Head } from '@inertiajs/vue3';
import PageIllustration from '../PageIllustration.vue';

</script>