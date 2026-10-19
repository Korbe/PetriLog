<script setup>
// Measuring board: 0–50 cm, the trout is laid out to 45 cm.
const RULER_START = 10;
const CM = 19.2;
const ticks = Array.from({ length: 51 }, (_, cm) => ({
  cm,
  x: RULER_START + cm * CM,
  h: cm % 10 === 0 ? 34 : cm % 5 === 0 ? 24 : 14,
}));
const measured = 45;

// Positions are percentages of the fish image box.
const callouts = [
  { x: 3, y: 58, label: 'Köder', value: 'Spinner, Gr. 3' },
  { x: 50, y: 34, label: 'Gewicht', value: '989 g' },
  { x: 90, y: 42, label: 'Tiefe · Wasser', value: '4,0 m · 12 °C', alignRight: true },
];

const ledger = [
  ['Fischart', 'Regenbogenforelle'],
  ['Länge', '45 cm'],
  ['Gewicht', '989 g'],
  ['Köder', 'Spinner, Gr. 3'],
  ['Tiefe', '4,0 m'],
  ['Wassertemp.', '12 °C'],
  ['Luftdruck', '1018 hPa'],
  ['Standort', 'Faaker See · GPS'],
];

// Real catch photos cropped out of a screenshot of the trout catch (offsets in % of crop width).
// Bewusst ein eigenes Bild – nicht die PWA-Screenshots, die sich mit jedem Redesign ändern.
const shot = '/images/home/fangbuch-regenbogenforelle-fotos.png';
const photos = [
  { ratio: '884 / 375', width: 114.03, left: -7.01, top: -39.48, cls: 'col-span-2' },
  { ratio: '425 / 270', width: 237.18, left: -14.59, top: -178.82, cls: '' },
  { ratio: '424 / 270', width: 237.74, left: -123.11, top: -179.25, cls: '' },
];
</script>

<template>
  <section class="relative bg-(--paper)">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 pb-20 md:pb-28">

      <div class="ph-reveal max-w-2xl">
        <p class="font-log text-xs uppercase tracking-[0.22em] text-(--ink)/55">02 — Das Fangbuch</p>
        <h2 class="font-display mt-4 text-5xl leading-[1] md:text-6xl">
          Alles, was einen Fang <em class="text-primary-700">ausmacht.</em>
        </h2>
        <p class="mt-6 text-lg leading-relaxed text-(--ink)/70">
          Genau die Daten, die beim nächsten Ausflug den Unterschied machen – sauber festgehalten statt
          schnell vergessen.
        </p>
      </div>

      <div class="mt-14 grid gap-6 lg:grid-cols-12">

        <!-- Measuring board -->
        <div
          class="ph-reveal relative overflow-hidden rounded-[2rem] bg-white bg-[linear-gradient(rgba(16,68,86,0.07)_1px,transparent_1px),linear-gradient(90deg,rgba(16,68,86,0.07)_1px,transparent_1px)] bg-[size:22px_22px] p-6 ring-1 ring-(--ink)/10 sm:p-10 lg:col-span-7 lg:flex lg:flex-col">

          <div class="font-log flex flex-wrap items-center justify-between gap-2 text-[11px] uppercase tracking-[0.18em] text-(--ink)/55">
            <span>Eintrag · 09.05.2026 · 16:18</span>
            <span>Faaker See, Kärnten</span>
          </div>

          <div class="relative mt-6 md:mt-10 md:pt-16 lg:my-auto">
            <div class="relative" style="aspect-ratio: 800 / 289; width: 89.44%; margin-left: -0.34%">
              <img src="/images/fish/Regenbogenforelle.png" alt="Regenbogenforelle auf dem Messbrett"
                class="absolute inset-0 h-full w-full mix-blend-multiply" loading="lazy" />

              <div v-for="c in callouts" :key="c.label" class="absolute hidden md:block"
                :style="{ left: `${c.x}%`, top: '-4rem', height: `calc(${c.y}% + 4rem)` }">
                <div class="absolute top-0 whitespace-nowrap" :class="c.alignRight ? 'right-0 text-right' : 'left-0'"
                  :style="{ transform: c.alignRight ? 'translateX(6px)' : 'translateX(-6px)' }">
                  <p class="font-log text-[10px] uppercase tracking-[0.2em] text-(--ink)/50">{{ c.label }}</p>
                  <p class="text-sm font-semibold">{{ c.value }}</p>
                </div>
                <span class="absolute bottom-0 left-0 top-11 border-l border-dashed border-(--ink)/40"></span>
                <span
                  class="absolute bottom-0 left-0 size-2.5 -translate-x-1/2 translate-y-1/2 rounded-full bg-(--float) ring-4 ring-(--float)/20"></span>
              </div>
            </div>

            <!-- Ruler -->
            <svg class="mt-4 w-full" viewBox="0 0 1000 100" aria-label="Messbrett: 45 Zentimeter">
              <rect x="0" y="0" width="1000" height="100" rx="14" fill="#f6efdd" />
              <rect x="0" y="0" width="1000" height="100" rx="14" fill="none" stroke="#0f1d22" stroke-opacity="0.12" />
              <rect :x="RULER_START" y="0" :width="measured * CM" height="7" fill="#118DF0" opacity="0.85" />
              <g stroke="#0f1d22" stroke-width="2">
                <line v-for="t in ticks" :key="t.cm" :x1="t.x" :x2="t.x" y1="0" :y2="t.h"
                  :stroke-opacity="t.h > 14 ? 0.8 : 0.4" />
              </g>
              <g class="font-log" font-size="20" fill="#0f1d22" fill-opacity="0.75" text-anchor="middle">
                <text v-for="t in ticks.filter(t => t.cm % 10 === 0)" :key="`n${t.cm}`" :x="t.x" y="62">{{ t.cm }}</text>
              </g>
              <line :x1="RULER_START + measured * CM" :x2="RULER_START + measured * CM" y1="0" y2="100"
                stroke="#ff5a1f" stroke-width="3" />
              <text class="font-log" :x="RULER_START + measured * CM - 12" y="90" font-size="19" fill="#ff5a1f"
                text-anchor="end" font-weight="500">45,0 cm</text>
            </svg>
          </div>

          <p class="mt-6 text-sm leading-relaxed text-(--ink)/60">
            Express-Eintrag oder alle Details – du entscheidest, wie viel du erfasst.
          </p>
        </div>

        <!-- Entry card -->
        <div class="ph-reveal rounded-[2rem] bg-white p-3 shadow-[0_30px_60px_-30px_rgba(15,29,34,0.35)] ring-1 ring-(--ink)/10 lg:col-span-5"
          style="transition-delay: 120ms">
          <div class="grid grid-cols-2 gap-2">
            <div v-for="(p, i) in photos" :key="i" class="relative overflow-hidden rounded-[1.4rem] bg-(--paper-deep)"
              :class="p.cls" :style="{ aspectRatio: p.ratio }">
              <img :src="shot" alt="" loading="lazy" class="block max-w-none"
                :style="{ width: `${p.width}%`, marginLeft: `${p.left}%`, marginTop: `${p.top}%` }" />
            </div>
          </div>

          <div class="px-4 pb-4 pt-6 sm:px-5">
            <div class="flex items-baseline justify-between gap-4">
              <h3 class="font-display text-3xl">Regenbogenforelle</h3>
              <span class="font-log shrink-0 text-[10px] uppercase tracking-[0.2em] text-(--ink)/50">3/3 Fotos</span>
            </div>

            <dl class="mt-4 space-y-2.5">
              <div v-for="[label, value] in ledger" :key="label" class="flex items-baseline gap-3 text-sm">
                <dt class="font-log text-[11px] uppercase tracking-[0.15em] text-(--ink)/55">{{ label }}</dt>
                <span class="flex-1 border-b border-dotted border-(--ink)/25" aria-hidden="true"></span>
                <dd class="font-medium">{{ value }}</dd>
              </div>
            </dl>
          </div>
        </div>

      </div>
    </div>
  </section>
</template>
