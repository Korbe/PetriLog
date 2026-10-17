<script setup>
import DepthContours from '@/components/public/DepthContours.vue';
import FishingFloat from '@/components/public/FishingFloat.vue';
import SiteLink from '@/components/public/SiteLink.vue';

const facts = ['Bis zu 3 Fotos pro Fang', 'GPS-Spot', 'Als App installierbar'];
</script>

<template>
  <section class="ph-grain relative isolate overflow-hidden bg-(--lake-900) text-(--paper)">
    <!-- Light falling into deep water -->
    <div class="absolute inset-0 -z-20 bg-[radial-gradient(ellipse_70%_60%_at_72%_38%,rgba(17,141,240,0.32),transparent_70%),radial-gradient(ellipse_50%_50%_at_0%_100%,rgba(255,90,31,0.10),transparent_70%),linear-gradient(180deg,var(--lake-950),var(--lake-900)_45%,var(--lake-800))]"
      aria-hidden="true"></div>
    <DepthContours class="absolute inset-0 -z-10 h-full w-full opacity-90" :labels="false" />

    <div class="max-w-6xl mx-auto px-4 sm:px-6 pt-32 md:pt-40 pb-40 md:pb-48">
      <div class="grid items-center gap-16 lg:grid-cols-12 lg:gap-8">

        <!-- Copy -->
        <div class="lg:col-span-7">
          <p class="ph-rise font-log inline-flex items-center gap-3 text-[11px] sm:text-xs uppercase tracking-[0.22em] text-(--paper)/70">
            <span class="relative flex size-2">
              <span class="absolute inline-flex size-full animate-ping rounded-full bg-(--float) opacity-60"></span>
              <span class="relative inline-flex size-2 rounded-full bg-(--float)"></span>
            </span>
            Petri Heil! · Das Fangbuch für Österreich
          </p>

          <h1 class="ph-rise font-display mt-6 text-[3.4rem] leading-[0.95] sm:text-7xl md:text-[5.5rem]"
            style="animation-delay: 0.1s">
            Jeder Fang verdient seine <em class="text-primary-300">Geschichte.</em>
          </h1>

          <p class="ph-rise mt-7 max-w-xl text-lg leading-relaxed text-(--paper)/75" style="animation-delay: 0.2s">
            Länge, Gewicht, Köder, Tiefe, Wassertemperatur und der genaue Spot – PetriLog hält jeden Fang
            fest. Privat, übersichtlich und direkt vom Ufer aus.
          </p>

          <div class="ph-rise mt-10 flex flex-col gap-4 sm:flex-row sm:items-center" style="animation-delay: 0.3s">
            <Link href="/register"
              class="group inline-flex items-center justify-center gap-3 rounded-full bg-primary-600 px-7 py-4 text-base font-semibold text-white shadow-[0_10px_40px_-10px_rgba(17,141,240,0.8)] transition hover:bg-primary-500">
              Jetzt Fang eintragen
              <svg class="size-4 transition-transform group-hover:translate-x-1" viewBox="0 0 16 16" fill="none"
                aria-hidden="true">
                <path d="M1 8h13m-5-5 5 5-5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                  stroke-linejoin="round" />
              </svg>
            </Link>
            <SiteLink href="/#so-gehts"
              class="inline-flex items-center justify-center gap-2 px-2 py-3 text-base font-medium text-(--paper)/85 underline decoration-(--paper)/30 underline-offset-8 transition hover:text-white hover:decoration-(--float)">
              So funktioniert's
            </SiteLink>
          </div>

          <ul class="ph-rise font-log mt-12 flex flex-wrap gap-x-6 gap-y-2 text-xs text-(--paper)/55"
            style="animation-delay: 0.4s">
            <li v-for="fact in facts" :key="fact" class="flex items-center gap-2">
              <span class="h-px w-4 bg-(--paper)/40"></span>{{ fact }}
            </li>
          </ul>
        </div>

        <!-- Phones -->
        <div class="relative mx-auto w-full max-w-sm lg:col-span-5 lg:max-w-none">
          <div class="ph-rise relative mx-auto w-[15.5rem] sm:w-[17rem]" style="animation-delay: 0.25s">
            <!-- back phone -->
            <div
              class="absolute -left-24 top-14 hidden w-[13rem] -rotate-[9deg] overflow-hidden rounded-[2.2rem] border-[7px] border-black/70 bg-black opacity-70 shadow-2xl sm:block">
              <img src="/images/icons/screenshots/Screenshot_1.png" alt="" class="block w-full" loading="lazy" />
            </div>
            <!-- front phone -->
            <div
              class="relative rotate-[4deg] overflow-hidden rounded-[2.6rem] border-[9px] border-black bg-black shadow-[0_40px_80px_-20px_rgba(0,0,0,0.75)] ring-1 ring-white/10">
              <img src="/images/icons/screenshots/Screenshot_5.png"
                alt="PetriLog Fangeintrag einer Regenbogenforelle mit Fotos, Länge, Gewicht und Tiefe"
                class="block w-full" />
            </div>

            <!-- Logbook chips -->
            <div
              class="absolute -left-10 top-[38%] rounded-2xl border border-white/15 bg-(--lake-950)/70 px-4 py-3 shadow-xl backdrop-blur-md sm:-left-20">
              <p class="font-display text-xl leading-none">Regenbogenforelle</p>
              <p class="font-log mt-1.5 text-[11px] text-primary-200">45 cm · 989 g</p>
            </div>
            <div
              class="absolute -right-4 bottom-[14%] rounded-2xl border border-white/15 bg-(--lake-950)/70 px-4 py-3 shadow-xl backdrop-blur-md sm:-right-12">
              <p class="font-log text-[10px] uppercase tracking-[0.2em] text-(--paper)/55">Faaker See</p>
              <p class="font-log mt-1 text-[11px] text-(--paper)">46.58° N · 13.92° E</p>
              <p class="font-log mt-0.5 text-[11px] text-primary-200">Tiefe 4,0 m</p>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Water surface -->
    <div class="pointer-events-none absolute inset-x-0 bottom-0 h-20 md:h-24" aria-hidden="true">
      <svg class="ph-drift-slow absolute bottom-0 left-0 h-full w-[200%] opacity-30" viewBox="0 0 2880 120"
        preserveAspectRatio="none">
        <path d="M0 56 C240 26 480 26 720 56 S1200 86 1440 56 S1920 26 2160 56 S2640 86 2880 56 V120 H0 Z"
          fill="var(--paper)" />
      </svg>
      <!-- float sits on the water line (y = 60 of 120) -->
      <div class="absolute bottom-1/2 left-[9%] z-10 md:left-[14%]">
        <FishingFloat :size="22" />
      </div>
      <svg class="ph-drift absolute bottom-0 left-0 h-full w-[200%]" viewBox="0 0 2880 120" preserveAspectRatio="none">
        <path d="M0 60 C240 50 480 50 720 60 S1200 70 1440 60 S1920 50 2160 60 S2640 70 2880 60 V120 H0 Z"
          fill="var(--paper)" />
      </svg>
    </div>
  </section>
</template>
