<template>
    <Layout>
        <Head title="Home" />
        <Hero />
        <HowItWorks />
        <FeaturesPlanet />
        <CatchDetails />
        <FeaturesHome />
        <!-- <TestimonialsCarousel /> -->
        <SponsorsCarousel />
        <Cta />
    </Layout>
</template>
<script setup>
import Layout from '@/Layouts/Public/Layout.vue';
import Hero from './Partials/Hero.vue';
import HowItWorks from './Partials/HowItWorks.vue';
import FeaturesPlanet from './Partials/FeaturesPlanet.vue';
import CatchDetails from './Partials/CatchDetails.vue';
import FeaturesHome from './Partials/FeaturesHome.vue';
import TestimonialsCarousel from './Partials/TestimonialsCarousel.vue';
import Cta from './Partials/Cta.vue';
import { Head } from '@inertiajs/vue3';
import SponsorsCarousel from './Partials/SponsorsCarousel.vue';

</script>