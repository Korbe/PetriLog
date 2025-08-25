<template>
    <Layout>

        <Head title="Impressum">

            <title>Impressum</title>
            <meta name="description"
                content="Impressum der PetriLog App - Angaben gemäß §5 TMG. Kontaktinformationen und rechtliche Hinweise.">
            <meta name="robots" content="index, follow">

            <!-- Open Graph / Social Media -->
            <meta property="og:title" content="Impressum | PetriLog">
            <meta property="og:description"
                content="Impressum der PetriLog App - Angaben gemäß §5 TMG. Kontaktinformationen und rechtliche Hinweise.">
            <meta property="og:type" content="website">
            <meta property="og:url" content="https://petrilog.com/impressum">
            <meta property="og:image" content="https://petrilog.com/logo.pn">

            <!-- Twitter Card -->
            <meta name="twitter:card" content="summary_large_image">
            <meta name="twitter:title" content="Impressum | PetriLog">
            <meta name="twitter:description"
                content="Impressum der PetriLog App - Angaben gemäß §5 TMG. Kontaktinformationen und rechtliche Hinweise.">
            <meta name="twitter:image" content="https://petrilog.com/logo.pn">







            <meta name="description"
                content="Lies die Nutzungsbedingungen von PetriLog. Erfahre alles über die Nutzung unserer Fangbuch-App, Verantwortlichkeiten und Richtlinien.">
            <meta name="keywords" content="PetriLog, Nutzungsbedingungen, Fangbuch, Angler, Bedingungen">

            <!-- Open Graph / Facebook -->
            <meta property="og:title" content="PetriLog - Nutzungsbedingungen">
            <meta property="og:description"
                content="Lies die Nutzungsbedingungen von PetriLog. Erfahre alles über die Nutzung unserer Fangbuch-App, Verantwortlichkeiten und Richtlinien.">
            <meta property="og:type" content="website">
            <meta property="og:url" content="https://petrilog.com/terms-of-service">
            <meta property="og:image" content="https://petrilog.com/logo.png">

            <!-- Twitter Card -->
            <meta name="twitter:card" content="summary_large_image">
            <meta name="twitter:title" content="PetriLog - Nutzungsbedingungen">
            <meta name="twitter:description"
                content="Lies die Nutzungsbedingungen von PetriLog. Erfahre alles über die Nutzung unserer Fangbuch-App, Verantwortlichkeiten und Richtlinien.">
            <meta name="twitter:image" content="https://petrilog.com/logo.png">

        </Head>

        <section class="relative">

            <PageIllustration />

            <div class="max-w-6xl mx-auto px-4 sm:px-6">

                <!-- Hero content -->
                <div class="pt-32 pb-12 md:pt-10 md:pb-20">

                    <!-- Section header -->
                    <div class="pb-12">
                        <h1 class="text-center text-5xl md:text-6xl font-bold mb-6">
                            Impressum</h1>
                        <div class="max-w-3xl mx-auto">

                            <p><strong>PetriLog</strong></p>
                            <p>Verantwortlich für den Inhalt:</p>
                            <p>Korbitsch Lukas<br>
                                Kiesweg 19<br>
                                9500 Villach<br>
                                Österreich</p>

                            <p>Kontakt:</p>
                            <p>
                                E-Mail: <a href="mailto:info@korbitsch.at"
                                    class="text-blue-600 underline">info@korbitsch.at</a><br>
                                Telefon: <a href="tel:+436701923388" class="text-blue-600 underline">+43 670 192 33
                                    88</a>
                            </p>

                            <p>Umsatzsteuer-Identifikationsnummer: keine</p>

                            <hr class="my-4">

                            <p>Haftungshinweis: Trotz sorgfältiger inhaltlicher Kontrolle übernehmen wir keine Haftung
                                für die Inhalte externer Links. Für den Inhalt der verlinkten Seiten sind ausschließlich
                                deren Betreiber verantwortlich.</p>
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