<template>
    <Layout overlay>
        <Head title="Newsletter abbestellt" />

        <PageHero eyebrow="Newsletter">
            <template #title>Newsletter <em class="text-primary-300">abbestellt.</em></template>
            Schade, dass du unseren Newsletter abbestellt hast. Du wurdest erfolgreich abgemeldet.
        </PageHero>

        <section class="max-w-6xl mx-auto px-4 sm:px-6 py-16 md:py-24">
            <p class="max-w-2xl text-lg text-(--ink)/70">
                Du kannst dich jederzeit wieder in deinem Profil anmelden, um Neuigkeiten zu erhalten.
            </p>
            <Link href="/"
                class="mt-8 inline-flex items-center rounded-full border border-(--ink)/20 px-6 py-3 font-semibold transition hover:border-(--ink)">
                Zur Startseite
            </Link>
        </section>
    </Layout>
</template>
<script setup>
import { Head } from '@inertiajs/vue3';
import Layout from '@/Layouts/Public/Layout.vue';
import PageHero from '@/components/public/PageHero.vue';
</script>
