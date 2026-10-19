<script setup>
import DepthContours from '@/components/public/DepthContours.vue';

// Illustrative season curve for the statistics tile.
const months = ['J', 'F', 'M', 'A', 'M', 'J', 'J', 'A', 'S', 'O', 'N', 'D'];
const catches = [2, 3, 6, 11, 16, 13, 9, 10, 15, 12, 5, 2];
const peak = Math.max(...catches);

const wikiFish = [
  { src: '/images/fish/Hecht.png', alt: 'Hecht', pos: '92% 45%' },
  { src: '/images/fish/Barbe.png', alt: 'Barbe', pos: '6% 55%' },
  { src: '/images/fish/Schleie.png', alt: 'Schleie', pos: '10% 55%' },
  { src: '/images/fish/Bachsaibling.png', alt: 'Bachsaibling', pos: '5% 55%' },
];
</script>

<template>
  <section id="funktionen" class="ph-grain relative isolate scroll-mt-16 overflow-hidden bg-(--lake-900) text-(--paper)">
    <div class="absolute inset-0 -z-10 bg-[radial-gradient(ellipse_60%_50%_at_15%_0%,rgba(17,141,240,0.22),transparent_70%)]"
      aria-hidden="true"></div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-20 md:py-28">
      <div class="ph-reveal grid gap-6 md:grid-cols-12 md:items-end">
        <div class="md:col-span-7">
          <p class="font-log text-xs uppercase tracking-[0.22em] text-(--paper)/55">03 — Mehr als ein Notizbuch</p>
          <h2 class="font-display mt-4 text-5xl leading-[1] md:text-6xl">
            Gebaut fürs <em class="text-primary-300">Wasser.</em>
          </h2>
        </div>
        <p class="text-lg leading-relaxed text-(--paper)/70 md:col-span-5">
          Egal ob Fluss, Bergsee oder Baggerteich: PetriLog funktioniert am Smartphone genauso wie am PC
          zuhause.
        </p>
      </div>

      <div class="mt-14 grid auto-rows-[minmax(0,auto)] gap-4 md:grid-cols-6">

        <!-- Statistics -->
        <article
          class="ph-reveal relative flex flex-col justify-between overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.04] p-7 md:col-span-4 md:row-span-2 md:p-9">
          <div>
            <p class="font-log text-[11px] uppercase tracking-[0.2em] text-primary-300">Statistik</p>
            <h3 class="font-display mt-3 text-4xl leading-tight">Wann läuft's bei dir?</h3>
            <p class="mt-3 max-w-md text-(--paper)/65">
              Monats- und Jahresstatistiken zeigen dir auf einen Blick, wann du am erfolgreichsten warst.
            </p>
          </div>

          <div class="mt-10">
            <div class="flex h-48 items-end gap-1.5 sm:gap-3">
              <div v-for="(n, i) in catches" :key="i" class="group relative flex h-full flex-1 items-end">
                <div class="w-full rounded-t-md transition-all duration-500"
                  :class="n === peak ? 'bg-(--float)' : 'bg-primary-500/70 group-hover:bg-primary-400'"
                  :style="{ height: `${(n / peak) * 100}%` }"></div>
                <span v-if="n === peak"
                  class="font-log absolute -top-7 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-full bg-(--paper) px-2 py-0.5 text-[10px] text-(--ink)">
                  Mai · {{ n }}
                </span>
              </div>
            </div>
            <div class="font-log mt-3 flex gap-1.5 text-[10px] text-(--paper)/45 sm:gap-3">
              <span v-for="(m, i) in months" :key="i" class="flex-1 text-center">{{ m }}</span>
            </div>
            <p class="font-log mt-4 text-[10px] uppercase tracking-[0.2em] text-(--paper)/35">Beispieldaten</p>
          </div>
        </article>

        <!-- Privacy -->
        <article class="ph-reveal rounded-[2rem] border border-white/10 bg-white/[0.04] p-7 md:col-span-2"
          style="transition-delay: 80ms">
          <svg class="size-9 text-primary-300" viewBox="0 0 36 36" fill="none" aria-hidden="true">
            <rect x="7" y="15" width="22" height="16" rx="4" stroke="currentColor" stroke-width="1.8" />
            <path d="M12 15v-4a6 6 0 0 1 12 0v4" stroke="currentColor" stroke-width="1.8" />
            <circle cx="18" cy="23" r="2" fill="currentColor" />
          </svg>
          <h3 class="mt-6 text-xl font-semibold">Privat. Standardmäßig.</h3>
          <p class="mt-2 text-(--paper)/65">
            Nur du siehst dein Fangbuch. Ob du einen Fang mit Freunden teilst, entscheidest du – Fang für Fang.
          </p>
        </article>

        <!-- Spots -->
        <article
          class="ph-reveal relative overflow-hidden rounded-[2rem] border border-white/10 bg-(--lake-950)/60 p-7 md:col-span-2"
          style="transition-delay: 160ms">
          <DepthContours class="absolute -right-24 -top-16 h-64 w-[26rem] opacity-60" :labels="false"
            :animated="false" />
          <div class="relative">
            <svg class="size-9 text-(--float)" viewBox="0 0 36 36" fill="none" aria-hidden="true">
              <path d="M18 32s10-9.3 10-17a10 10 0 0 0-20 0c0 7.7 10 17 10 17Z" stroke="currentColor"
                stroke-width="1.8" />
              <circle cx="18" cy="15" r="3.5" stroke="currentColor" stroke-width="1.8" />
            </svg>
            <h3 class="mt-6 text-xl font-semibold">Spots wiederfinden</h3>
            <p class="mt-2 text-(--paper)/65">
              Jeder Fang mit GPS-Position – ein Tipp öffnet den Spot in Google oder Apple Maps.
            </p>
          </div>
        </article>

        <!-- Water wiki -->
        <article class="ph-reveal rounded-[2rem] border border-white/10 bg-white/[0.04] p-7 md:col-span-3 md:p-9">
          <div class="flex -space-x-3">
            <span v-for="f in wikiFish" :key="f.alt"
              class="size-14 overflow-hidden rounded-full bg-(--paper) ring-4 ring-(--lake-900)">
              <img :src="f.src" :alt="f.alt" loading="lazy" class="size-full object-cover mix-blend-multiply"
                :style="{ objectPosition: f.pos }" />
            </span>
          </div>
          <h3 class="mt-6 text-xl font-semibold">Gewässer-Wiki</h3>
          <p class="mt-2 text-(--paper)/65">
            Österreichs Seen und Flüsse mit den heimischen Fischarten und den zuständigen Vereinen – alles an einem
            Ort.
          </p>
        </article>

        <!-- Install -->
        <article class="ph-reveal rounded-[2rem] border border-white/10 bg-white/[0.04] p-7 md:col-span-3 md:p-9"
          style="transition-delay: 80ms">
          <svg class="size-9 text-primary-300" viewBox="0 0 36 36" fill="none" aria-hidden="true">
            <rect x="10" y="3" width="16" height="30" rx="4" stroke="currentColor" stroke-width="1.8" />
            <path d="M18 10v10m-4-4 4 4 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
              stroke-linejoin="round" />
            <path d="M15 28h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
          </svg>
          <h3 class="mt-6 text-xl font-semibold">Wie eine App – ohne App-Store</h3>
          <p class="mt-2 text-(--paper)/65">
            Zum Startbildschirm hinzufügen und PetriLog startet wie eine echte App. Auf Smartphone, Tablet und PC.
          </p>
        </article>

      </div>
    </div>
  </section>
</template>
