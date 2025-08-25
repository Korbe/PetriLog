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

                            <h2 class="text-2xl font-semibold mt-8 mb-4">1. Allgemeine Nutzung</h2>
                            <p class="mb-4">
                                PetriLog ist eine Plattform zur Dokumentation von Angel-Fängen. Sie erklären sich damit
                                einverstanden, die Anwendung nur zu legalen Zwecken und in Übereinstimmung mit allen
                                geltenden Gesetzen zu nutzen.
                            </p>

                            <h2 class="text-2xl font-semibold mt-8 mb-4">2. Registrierung und Konto</h2>
                            <p class="mb-4">
                                Für die Nutzung bestimmter Funktionen ist eine Registrierung erforderlich. Sie sind
                                verantwortlich für die Sicherheit Ihres Kontos und Ihrer Zugangsdaten. Geben Sie Ihr
                                Passwort nicht weiter.
                            </p>

                            <h2 class="text-2xl font-semibold mt-8 mb-4">3. Inhalte und Medien</h2>
                            <p class="mb-4">
                                Sie sind verantwortlich für alle Inhalte, die Sie in PetriLog hochladen, einschließlich
                                Fotos, Texte und Fangdaten. Bitte laden Sie keine urheberrechtlich geschützten oder
                                rechtswidrigen Inhalte hoch.
                            </p>

                            <h2 class="text-2xl font-semibold mt-8 mb-4">4. Haftung</h2>
                            <p class="mb-4">
                                PetriLog übernimmt keine Haftung für Schäden, Verluste oder Probleme, die durch die
                                Nutzung der Plattform entstehen. Die Nutzung erfolgt auf eigenes Risiko.
                            </p>

                            <h2 class="text-2xl font-semibold mt-8 mb-4">5. Änderungen der Plattform</h2>
                            <p class="mb-4">
                                Wir behalten uns das Recht vor, PetriLog jederzeit zu aktualisieren, zu ändern oder
                                einzustellen. Über wesentliche Änderungen werden Nutzer, soweit möglich, informiert.
                            </p>

                            <h2 class="text-2xl font-semibold mt-8 mb-4">6. Datenschutz</h2>
                            <p class="mb-4">
                                Die Verarbeitung Ihrer Daten erfolgt gemäß unserer <a href="/privacy-policy"
                                    class="text-blue-600">Datenschutzerklärung</a>. Bitte lesen Sie diese aufmerksam.
                            </p>

                            <h2 class="text-2xl font-semibold mt-8 mb-4">7. Änderungen der Nutzungsbedingungen</h2>
                            <p class="mb-4">
                                Wir können diese Nutzungsbedingungen jederzeit anpassen. Die aktuelle Version ist stets
                                auf unserer Website verfügbar. Durch die weitere Nutzung der Plattform erklären Sie sich
                                mit den geänderten Bedingungen einverstanden.
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