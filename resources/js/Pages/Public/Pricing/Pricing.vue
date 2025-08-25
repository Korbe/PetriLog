<script setup>
import Layout from '@/Layouts/Public/Layout.vue';
import PricingPlan from './Partials/PricingPlan.vue';
import CtaAlternative from '../CtaAlternative.vue';
import Faqs from './Partials/Faqs.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
  <Layout>

    <Head title="Preis">

      <meta name="description"
        content="Ein Preis, ein Paket: Sichere dir das PetriLog Jahresabo und nutze alle Funktionen ohne Einschränkung.">

      <meta property="og:title" content="PetriLog Preis - Jahresabo" />
      <meta property="og:description"
        content="Ein Preis, ein Paket: Sichere dir das PetriLog Jahresabo und nutze alle Funktionen ohne Einschränkung." />
      <meta property="og:image" content="/images/icons/logo-512.png" />
      <meta property="og:url" content="https://petrilog.korbitsch.at/preis" />
      <meta property="og:type" content="website" />

      <meta name="twitter:card" content="summary_large_image" />
      <meta name="twitter:title" content="PetriLog Preis - Jahresabo" />
      <meta name="twitter:description"
        content="Ein Preis, ein Paket: Sichere dir das PetriLog Jahresabo und nutze alle Funktionen ohne Einschränkung." />
      <meta name="twitter:image" content="/images/icons/logo-512.png" />

    </Head>

    <PricingPlan />
    <Faqs />
    <CtaAlternative class="overflow-hidden" heading="Logge jetzt deine Fangmomente" buttonText="Registrieren"
      buttonLink="/register" />
  </Layout>
</template>
