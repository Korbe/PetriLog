<script setup>
import DepthContours from '@/components/public/DepthContours.vue';
import FishingFloat from '@/components/public/FishingFloat.vue';
</script>

<template>
  <section class="relative bg-(--paper) pb-20 md:pb-28">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
      <div
        class="ph-reveal ph-grain relative isolate overflow-hidden rounded-[2.5rem] bg-(--lake-950) px-6 py-16 text-(--paper) shadow-[0_40px_80px_-40px_rgba(3,20,26,0.7)] md:px-16 md:py-24">
        <DepthContours class="absolute inset-0 -z-10 h-full w-full opacity-70" :labels="false" :animated="false" />
        <div class="absolute inset-0 -z-10 bg-[radial-gradient(ellipse_60%_80%_at_80%_50%,rgba(17,141,240,0.25),transparent_70%)]"
          aria-hidden="true"></div>

        <div class="grid items-end gap-12 md:grid-cols-12">
          <div class="md:col-span-8">
            <p class="font-log text-xs uppercase tracking-[0.22em] text-(--paper)/55">Petri Heil</p>
            <h2 class="font-display mt-4 text-5xl leading-[0.98] md:text-7xl">
              Die nächste Saison <em class="text-primary-300">beginnt jetzt.</em>
            </h2>
            <p class="mt-6 max-w-lg text-lg text-(--paper)/70">
              Leg dein Fangbuch an und trag den nächsten Fang direkt am Wasser ein.
            </p>
            <div class="mt-10 flex flex-col gap-4 sm:flex-row sm:items-center">
              <Link href="/register"
                class="group inline-flex items-center justify-center gap-3 rounded-full bg-(--paper) px-7 py-4 text-base font-semibold text-(--ink) transition hover:bg-white">
                Kostenlos registrieren
                <svg class="size-4 transition-transform group-hover:translate-x-1" viewBox="0 0 16 16" fill="none"
                  aria-hidden="true">
                  <path d="M1 8h13m-5-5 5 5-5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                    stroke-linejoin="round" />
                </svg>
              </Link>
              <Link href="/pricing"
                class="inline-flex items-center justify-center px-2 py-3 font-medium text-(--paper)/85 underline decoration-(--paper)/30 underline-offset-8 transition hover:text-white hover:decoration-(--float)">
                Preise ansehen
              </Link>
            </div>
          </div>

          <div class="hidden justify-center pb-6 md:col-span-4 md:flex">
            <FishingFloat :size="40" />
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
