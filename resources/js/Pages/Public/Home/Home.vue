<template>
    <Layout>
        <Hero/>
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

</script>