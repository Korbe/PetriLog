<script setup>
import Layout from '@/Layouts/Public/Layout.vue';
import PricingPlan from './Partials/PricingPlan.vue';
import CtaAlternative from '../CtaAlternative.vue';
import Faqs from './Partials/Faqs.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
  <Layout>

    <Head title="Preise" />

    <PricingPlan />
    <Faqs />
    <CtaAlternative class="overflow-hidden" heading="Logge jetzt deine Fangmomente" buttonText="Registrieren"
      buttonLink="/register" />
  </Layout>
</template>
