<template>
    <Layout>

        <Head title="Home">
            <meta name="description"
                content="Behalte den Überblick über deine Fänge, teile deine Erlebnisse und analysiere deine Angelerfolge mit PetriLog.">

            <meta property="og:title" content="PetriLog - Dein digitales Fangbuch" />
            <meta property="og:description"
                content="Behalte den Überblick über deine Fänge, teile deine Erlebnisse und analysiere deine Angelerfolge mit PetriLog." />
            <meta property="og:image" content="/images/icons/logo-512.png" />
            <meta property="og:url" content="https://petrilog.korbitsch.at/" />
            <meta property="og:type" content="website" />

            <meta name="twitter:card" content="summary_large_image" />
            <meta name="twitter:title" content="PetriLog - Dein digitales Fangbuch" />
            <meta name="twitter:description"
                content="Behalte den Überblick über deine Fänge, teile deine Erlebnisse und analysiere deine Angelerfolge mit PetriLog." />
            <meta name="twitter:image" content="/images/icons/logo-512.png" />

        </Head>

        <Hero />
        <FeaturesPlanet />
        <FeaturesHome />
        <TestimonialsCarousel />
        <Cta />
    </Layout>
</template>
<script setup>
import Layout from '@/Layouts/Public/Layout.vue';
import Hero from './Partials/Hero.vue';
import FeaturesPlanet from './Partials/FeaturesPlanet.vue';
import FeaturesHome from './Partials/FeaturesHome.vue';
import TestimonialsCarousel from './Partials/TestimonialsCarousel.vue';
import Cta from './Partials/Cta.vue';
import { Head } from '@inertiajs/vue3';

</script>