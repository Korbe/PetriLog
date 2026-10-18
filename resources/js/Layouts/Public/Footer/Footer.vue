<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import DepthContours from '@/components/public/DepthContours.vue'
import SiteLink from '@/components/public/SiteLink.vue'

const user = computed(() => usePage().props.auth?.user)
const year = new Date().getFullYear()

const columns = computed(() => [
  {
    title: 'PetriLog',
    span: 'md:col-span-2',
    links: [
      { label: 'Startseite', href: '/' },
      { label: 'Funktionen', href: '/#funktionen' },
      { label: 'Preise', href: '/pricing' },
      { label: 'Kontakt', href: '/contact' },
    ],
  },
  {
    title: 'Rechtliches',
    span: 'md:col-span-3',
    links: [
      { label: 'Impressum', href: '/impressum' },
      { label: 'Datenschutz', href: '/privacy-policy' },
      { label: 'Nutzungsbedingungen', href: '/terms-of-service' },
    ],
  },
  {
    title: 'Fangbuch',
    span: 'md:col-span-2',
    links: user.value
      ? [{ label: 'Zum Dashboard', href: '/dashboard' }]
      : [
        { label: 'Anmelden', href: '/login' },
        { label: 'Kostenlos registrieren', href: '/register' },
      ],
  },
])
</script>

<template>
  <footer class="ph-grain relative isolate overflow-hidden bg-(--lake-950) text-(--paper)">
    <DepthContours class="absolute inset-0 -z-10 h-full w-full opacity-40" :labels="false" :animated="false" />

    <div class="max-w-6xl mx-auto px-4 sm:px-6 pt-16 md:pt-20">
      <div class="grid gap-12 md:grid-cols-12">

        <div class="md:col-span-5">
          <Link href="/" class="inline-flex items-center gap-2.5" aria-label="PetriLog – zur Startseite">
            <img src="/logo-short.png" alt="" width="34" height="34" class="size-8" />
            <span class="text-[1.35rem] font-medium tracking-tight">PetriLog</span>
          </Link>
          <p class="mt-5 max-w-xs leading-relaxed text-(--paper)/60">
            Das digitale Fangbuch für Angler in Österreich – Fänge festhalten, Spots wiederfinden, Erfolge auswerten.
          </p>
          <p class="font-log mt-6 text-xs uppercase tracking-[0.2em] text-(--float)">Petri Heil!</p>
        </div>

        <nav v-for="col in columns" :key="col.title" :class="col.span" :aria-label="col.title">
          <h2 class="font-log text-[11px] uppercase tracking-[0.2em] text-(--paper)/45">{{ col.title }}</h2>
          <ul class="mt-4 space-y-2.5">
            <li v-for="link in col.links" :key="link.href">
              <SiteLink :href="link.href" class="text-(--paper)/75 transition hover:text-white">{{ link.label }}</SiteLink>
            </li>
          </ul>
        </nav>
      </div>

      <div
        class="font-log mt-16 flex flex-col gap-2 border-t border-white/10 py-6 text-[11px] uppercase tracking-[0.15em] text-(--paper)/40 sm:flex-row sm:justify-between">
        <span>&copy; {{ year }} PetriLog.com · Ein Produkt von
          <a href="https://korbitsch.at" target="_blank" rel="noopener"
            class="text-(--paper)/70 underline-offset-4 transition hover:text-(--paper) hover:underline"
            title="Entwickelt von Ing. Lukas Korbitsch">korbitsch.at</a></span>
        <span>Entwickelt von Ing. Lukas Korbitsch · Kärnten, Österreich</span>
      </div>
    </div>

    <!-- Oversized wordmark -->
    <div class="pointer-events-none -mb-[0.22em] select-none text-center" aria-hidden="true">
      <span
        class="font-display block whitespace-nowrap text-[26vw] leading-[0.8] text-transparent [-webkit-text-stroke:1px_rgba(243,238,227,0.14)] md:text-[19rem]">
        PetriLog
      </span>
    </div>
  </footer>
</template>
