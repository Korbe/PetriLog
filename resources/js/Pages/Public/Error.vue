<template>
  <Layout>
    <Head :title="copy.title" />

    <section class="relative mx-auto flex max-w-3xl flex-col items-center px-4 py-20 text-center sm:px-6 md:py-28">
      <!-- Wasserlinie mit „Schwimmer“ -->
      <div class="relative mb-8 h-28 w-full max-w-sm" aria-hidden="true">
        <svg viewBox="0 0 320 112" class="h-full w-full">
          <path d="M0 70 Q 40 60 80 70 T 160 70 T 240 70 T 320 70" fill="none" stroke="currentColor"
            class="text-(--app-accent) opacity-40" stroke-width="2" />
          <path d="M0 86 Q 40 78 80 86 T 160 86 T 240 86 T 320 86" fill="none" stroke="currentColor"
            class="text-(--app-accent) opacity-20" stroke-width="2" />
          <line x1="160" y1="8" x2="160" y2="44" stroke="currentColor" class="text-(--app-subtle)" stroke-width="1.5"
            stroke-dasharray="3 4" />
          <g class="bobber">
            <ellipse cx="160" cy="56" rx="9" ry="13" fill="#ff5a1f" />
            <rect x="151" y="56" width="18" height="13" fill="#fff" />
            <ellipse cx="160" cy="69" rx="9" ry="4" fill="#fff" />
          </g>
        </svg>
      </div>

      <p class="font-mono text-sm font-medium tracking-[0.3em] text-(--app-accent)">FEHLER {{ status }}</p>
      <h1 class="font-display mt-3 text-4xl text-(--app-ink) sm:text-5xl">{{ copy.title }}</h1>
      <p class="mt-4 max-w-md text-base text-(--app-muted)">{{ copy.message }}</p>

      <div class="mt-10 flex w-full flex-col items-stretch justify-center gap-3 sm:w-auto sm:flex-row">
        <VButton size="lg" variant="secondary" @click="goBack">
          <ArrowLeftIcon class="size-5" /> Zurück
        </VButton>
        <VButton v-if="loggedIn" size="lg" :href="route('app.dashboard')">
          <Squares2X2Icon class="size-5" /> Zum Dashboard
        </VButton>
        <VButton v-else size="lg" :href="route('public.index')">
          <HomeIcon class="size-5" /> Zur Startseite
        </VButton>
      </div>
    </section>
  </Layout>
</template>

<script setup>
import { computed } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'
import Layout from '@/Layouts/Public/Layout.vue'
import VButton from '@/components/VButton.vue'
import { ArrowLeftIcon, HomeIcon, Squares2X2Icon } from '@heroicons/vue/24/outline'

const props = defineProps({
  status: { type: Number, required: true },
})

const COPY = {
  403: { title: 'Kein Zugriff', message: 'Diese Seite ist nicht für dich freigegeben. Vielleicht gehört der Eintrag jemand anderem oder er ist privat.' },
  404: { title: 'Hier beißt nichts an', message: 'Diese Seite gibt es nicht (mehr). Vielleicht wurde der Eintrag gelöscht oder der Link ist nicht mehr gültig.' },
  405: { title: 'Das ging so nicht', message: 'Diese Aktion ist hier nicht möglich. Geh einfach einen Schritt zurück.' },
  429: { title: 'Kurz durchatmen', message: 'Zu viele Anfragen in kurzer Zeit. Bitte warte einen Moment und versuch es dann erneut.' },
  500: { title: 'Da hat sich die Schnur verheddert', message: 'Auf unserer Seite ist etwas schiefgelaufen. Wir kümmern uns darum – bitte versuch es gleich noch einmal.' },
  503: { title: 'Gleich wieder da', message: 'PetriLog wird gerade gewartet. Bitte versuch es in ein paar Minuten erneut.' },
}

const copy = computed(() => COPY[props.status] ?? COPY[500])
const loggedIn = computed(() => !!usePage().props.auth?.user)

// In der PWA gibt es keinen Browser-Zurück-Button – ohne Verlauf geht es zum Dashboard bzw. zur Startseite
const goBack = () => {
  if (window.history.length > 1) {
    window.history.back()
  } else {
    window.location.href = loggedIn.value ? route('app.dashboard') : route('public.index')
  }
}
</script>

<style scoped>
.bobber {
  transform-origin: 160px 62px;
  animation: bob 2.6s ease-in-out infinite;
}

@keyframes bob {
  0%, 100% { transform: translateY(0) rotate(-4deg); }
  50% { transform: translateY(6px) rotate(4deg); }
}

@media (prefers-reduced-motion: reduce) {
  .bobber { animation: none; }
}
</style>
