<template>
    <Layout>

        <Head title="Kontakt">

            <meta name="description"
                content="Du hast Fragen oder Feedback? Kontaktiere uns direkt - wir freuen uns von dir zu hören.">

            <meta property="og:title" content="PetriLog Kontakt" />
            <meta property="og:description"
                content="Du hast Fragen oder Feedback? Kontaktiere uns direkt - wir freuen uns von dir zu hören." />
            <meta property="og:image" content="/images/icons/logo-512.png" />
            <meta property="og:url" content="https://petrilog.korbitsch.at/kontakt" />
            <meta property="og:type" content="website" />

            <meta name="twitter:card" content="summary_large_image" />
            <meta name="twitter:title" content="PetriLog Kontakt" />
            <meta name="twitter:description"
                content="Du hast Fragen oder Feedback? Kontaktiere uns direkt - wir freuen uns von dir zu hören." />
            <meta name="twitter:image" content="/images/icons/logo-512.png" />

        </Head>

        <PageIllustration />

        <div class="max-w-6xl mx-auto px-4 sm:px-6">

            <!-- Hero content -->
            <div class="pt-32 pb-12 md:pt-10 md:pb-20">

                <!-- Section header -->
                <div class="text-center pb-12">
                    <h1 class="text-5xl md:text-6xl font-bold mb-6">
                        Kontakt</h1>
                    <div class="max-w-3xl mx-auto">
                        <p class="text-lg text-gray-700">Sie erreichen uns jederzeit per Mail oder persönlich per
                            Telefon</p>
                    </div>
                </div>

                <div class="max-w-sm mx-auto gap-8">
                    <div
                        class="relative flex flex-col h-full p-5 rounded-2xl backdrop-blur-xs bg-linear-to-tr from-gray-900 to-gray-700 shadow-lg shadow-black/[0.03]">
                        <div class="text-center">
                            <div class="text-lg text-gray-300"><b>Email</b>: info@korbitsch.at</div>
                            <div class="text-lg text-gray-300"><b>Tel</b>: +43 670 192 33 88</div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </Layout>
</template>
<script setup>
import Layout from '@/Layouts/Public/Layout.vue';
import PageIllustration from '../PageIllustration.vue';
import { Head } from '@inertiajs/vue3';

</script>