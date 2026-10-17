<script setup>
import { Head } from '@inertiajs/vue3';
import Layout from '@/Layouts/Public/Layout.vue';
import PageHero from '@/components/public/PageHero.vue';
import PricingPlan from './Partials/PricingPlan.vue';
import Faqs from './Partials/Faqs.vue';

defineProps({
  faqs: { type: Array, required: true },
});
</script>

<template>
  <Layout overlay>
    <Head title="Preise" />

    <PageHero eyebrow="Preise">
      <template #title>Ein Abo für <em class="text-primary-300">echte Angler.</em></template>
      Starte kostenlos mit bis zu 5 Fängen. Mit PetriLog Plus ist alles unbegrenzt – ein Preis, keine
      Überraschungen.
    </PageHero>

    <PricingPlan />
    <Faqs :faqs="faqs" />
  </Layout>
</template>
