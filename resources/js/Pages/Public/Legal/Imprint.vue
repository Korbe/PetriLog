<template>
    <Layout>

        <Head title="Impressum" />

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

                            <p><strong>PetriLog GesbR</strong></p>
                            <p class="pt-5">Kontakt: <a href="mailto:info@petrilog.com"
                                    class="text-blue-600 underline">info@petrilog.com</a></p>



                            <p class="mt-10">Verantwortlich für den Inhalt:</p>
                            <p class="py-5">Ing. Korbitsch Lukas (Technische Leitung)<br>
                                Kiesweg 19<br>
                                9500 Villach<br>
                                Österreich</p>
                            <p>Telefon: <a href="tel:+436701923388" class="text-blue-600 underline">+43 670 192 33
                                    88</a></p>


                            <hr class="mt-5">

                            <p class="py-5">Perschon Thomas (Vetrieb)<br>
                                Ahornweg 13<br>
                                9601 Arnoldstein<br>
                                Österreich</p>

                            <p>Telefon: <a href="tel:+436505222753" class="text-blue-600 underline">+43 650 522 27
                                    53</a></p>

                            <hr class="my-4">

                            <p>Quelle Bilder - Wikipedia.com</p>

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