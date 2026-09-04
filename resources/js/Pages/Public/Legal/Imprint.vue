<template>
    <Layout>

        <Head title="Impressum" />

        <section class="relative">

            <PageIllustration />

            <div class="max-w-6xl mx-auto px-4 sm:px-6">

                <!-- Hero content -->
                <div class="pt-32 pb-12 md:pt-10 md:pb-20">

                    <!-- Section header -->
                    <div class="pb-12 md:pb-16">
                        <h1 class="text-center text-5xl md:text-6xl font-bold mb-4">Impressum</h1>
                        <p class="text-center text-lg text-gray-700 max-w-2xl mx-auto">
                            Angaben gemäß § 5 ECG und Offenlegung nach § 25 Mediengesetz.
                        </p>
                    </div>

                    <div class="max-w-4xl mx-auto">

                        <!-- Diensteanbieter & Kontakt -->
                        <div class="grid md:grid-cols-2 gap-6 mb-6">
                            <div class="rounded-xl bg-white border border-gray-200 shadow-sm p-6 md:p-8">
                                <div class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-primary-50 mb-4">
                                    <BuildingOffice2Icon class="w-5 h-5 text-primary-500" />
                                </div>
                                <h2 class="font-medium text-lg mb-2">Diensteanbieter</h2>
                                <p class="text-[15px] text-gray-700 leading-relaxed">
                                    Ing. Lukas Korbitsch<br>
                                    Webentwicklung &amp; IT Dienstleistungen<br>
                                    Kiesweg 19<br>
                                    Villach, Kärnten<br>
                                    Österreich
                                </p>
                            </div>

                            <div class="rounded-xl bg-white border border-gray-200 shadow-sm p-6 md:p-8">
                                <div class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-primary-50 mb-4">
                                    <EnvelopeIcon class="w-5 h-5 text-primary-500" />
                                </div>
                                <h2 class="font-medium text-lg mb-4">Kontakt</h2>
                                <div class="space-y-3">
                                    <a href="mailto:info@korbitsch.at"
                                        class="flex items-center gap-3 text-[15px] text-gray-700 hover:text-primary-500 transition-colors">
                                        <EnvelopeIcon class="w-4 h-4 shrink-0 text-gray-400" />
                                        info@korbitsch.at
                                    </a>
                                    <a href="tel:+436701923388"
                                        class="flex items-center gap-3 text-[15px] text-gray-700 hover:text-primary-500 transition-colors">
                                        <PhoneIcon class="w-4 h-4 shrink-0 text-gray-400" />
                                        +43 670 1923388
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Unternehmensdaten -->
                        <div class="grid sm:grid-cols-2 gap-5 mb-12 md:mb-16">
                            <div class="rounded-xl bg-white border border-gray-200 shadow-sm p-6">
                                <div class="flex items-center gap-2 mb-2">
                                    <IdentificationIcon class="w-4 h-4 text-primary-500" />
                                    <h3 class="font-medium text-[15px]">Rechtsform</h3>
                                </div>
                                <p class="text-[15px] text-gray-700">Einzelunternehmen</p>
                            </div>

                            <div class="rounded-xl bg-white border border-gray-200 shadow-sm p-6">
                                <div class="flex items-center gap-2 mb-2">
                                    <BriefcaseIcon class="w-4 h-4 text-primary-500" />
                                    <h3 class="font-medium text-[15px]">Tätigkeitsbereich</h3>
                                </div>
                                <p class="text-[15px] text-gray-700">Entwicklung individueller Webanwendungen sowie
                                    IT-Dienstleistungen.</p>
                            </div>

                            <div class="rounded-xl bg-white border border-gray-200 shadow-sm p-6">
                                <div class="flex items-center gap-2 mb-2">
                                    <ScaleIcon class="w-4 h-4 text-primary-500" />
                                    <h3 class="font-medium text-[15px]">Kammer / Berufsverband</h3>
                                </div>
                                <p class="text-[15px] text-gray-700">Wirtschaftskammer Kärnten</p>
                            </div>

                            <div class="rounded-xl bg-white border border-gray-200 shadow-sm p-6">
                                <div class="flex items-center gap-2 mb-2">
                                    <DocumentTextIcon class="w-4 h-4 text-primary-500" />
                                    <h3 class="font-medium text-[15px]">GISA-Zahl</h3>
                                </div>
                                <p class="text-[15px] text-gray-700">38890017<br>39963185</p>
                            </div>

                            <div class="rounded-xl bg-white border border-gray-200 shadow-sm p-6 sm:col-span-2">
                                <div class="flex items-center gap-2 mb-2">
                                    <ShieldCheckIcon class="w-4 h-4 text-primary-500" />
                                    <h3 class="font-medium text-[15px]">Aufsichtsbehörde gem. ECG (E-Commerce Gesetz)</h3>
                                </div>
                                <p class="text-[15px] text-gray-700">Bezirkshauptmannschaft Villach</p>
                            </div>
                        </div>

                        <!-- Rechtliche Hinweise -->
                        <div>
                            <h2 class="text-2xl font-semibold mt-8 mb-4 flex items-center gap-2">
                                <SparklesIcon class="w-5 h-5 text-primary-500" />
                                Bildrechte
                            </h2>
                            <p class="mb-4 text-gray-700">
                                Die Bilder sind KI generiert und unterliegen keinem Urheberrecht. Sie wurden mit ChatGPT
                                erstellt. Oder sie sind lizenzfreie Bilder von Unsplash und Pexels oder sind selbst erstellt.
                            </p>

                            <h2 class="text-2xl font-semibold mt-8 mb-4">Haftung für Inhalte</h2>
                            <p class="mb-4 text-gray-700">
                                Die Inhalte dieser Website wurden mit größtmöglicher Sorgfalt erstellt. Dennoch kann
                                keine Gewähr für die Richtigkeit, Vollständigkeit oder Aktualität der bereitgestellten
                                Informationen übernommen werden.
                            </p>
                            <p class="mb-4 text-gray-700">
                                Diese Website enthält Verlinkungen zu externen Diensten und Plattformen, darunter
                                Facebook, Instagram und WhatsApp. Für die Inhalte dieser externen Websites sind
                                ausschließlich die jeweiligen Betreiber verantwortlich. Auf deren Inhalte und zukünftige
                                Änderungen besteht kein Einfluss, weshalb hierfür keine Haftung übernommen wird.
                            </p>

                            <h2 class="text-2xl font-semibold mt-8 mb-4 flex items-center gap-2">
                                <LinkIcon class="w-5 h-5 text-primary-500" />
                                Haftung für Links
                            </h2>
                            <p class="mb-4 text-gray-700">
                                Diese Website enthält Links zu externen Websites Dritter, auf deren Inhalte kein Einfluss
                                besteht. Für diese Inhalte wird keine Haftung übernommen.
                            </p>

                            <h2 class="text-2xl font-semibold mt-8 mb-4">Urheberrecht</h2>
                            <p class="mb-4 text-gray-700">
                                Die auf dieser Website erstellten Inhalte und Werke unterliegen dem Urheberrecht.
                                Beiträge Dritter sind entsprechend gekennzeichnet.
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
import {
    BuildingOffice2Icon,
    EnvelopeIcon,
    PhoneIcon,
    IdentificationIcon,
    BriefcaseIcon,
    ScaleIcon,
    DocumentTextIcon,
    ShieldCheckIcon,
    SparklesIcon,
    LinkIcon,
} from '@heroicons/vue/24/solid';

</script>
