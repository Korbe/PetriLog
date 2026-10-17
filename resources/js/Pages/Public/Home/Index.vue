<template>
    <Layout overlay>
        <Head title="Das digitale Fangbuch für Angler in Österreich" />

        <Hero />
        <SpeciesMarquee />
        <HowItWorks />
        <CatchDetails />
        <FeaturesBento />
        <FishAtlas />
        <Cta />
    </Layout>
</template>
<script setup>
import { Head } from '@inertiajs/vue3';
import Layout from '@/Layouts/Public/Layout.vue';
import Hero from './Partials/Hero.vue';
import SpeciesMarquee from './Partials/SpeciesMarquee.vue';
import HowItWorks from './Partials/HowItWorks.vue';
import CatchDetails from './Partials/CatchDetails.vue';
import FeaturesBento from './Partials/FeaturesBento.vue';
import FishAtlas from './Partials/FishAtlas.vue';
import Cta from './Partials/Cta.vue';
</script>
