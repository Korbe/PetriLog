<script setup>
const steps = [
  {
    number: '01',
    title: 'Fisch landen',
    text: 'Kescher, Maßband, Foto – mehr brauchst du nicht. Das Handy bleibt ohnehin in der Jackentasche.',
  },
  {
    number: '02',
    title: 'Eintragen',
    text: 'Per Express-Eintrag nur das Nötigste direkt am Wasser – oder alle Details: Köder, Tiefe, Temperatur, Spot und bis zu 3 Fotos.',
  },
  {
    number: '03',
    title: 'Erinnern & auswerten',
    text: 'Dein Fang landet im privaten Fangbuch. Die Statistik zeigt dir, wann und wo es bei dir am besten läuft.',
  },
]
</script>

<template>
  <section id="so-gehts" class="relative scroll-mt-24 bg-(--paper)">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-20 md:py-28">

      <div class="ph-reveal grid gap-6 md:grid-cols-12 md:items-end">
        <div class="md:col-span-7">
          <p class="font-log text-xs uppercase tracking-[0.22em] text-(--ink)/55">01 — So geht's</p>
          <h2 class="font-display mt-4 text-5xl leading-[1] md:text-6xl">
            Vom Biss ins Fangbuch – <em class="text-primary-700">in drei Zügen.</em>
          </h2>
        </div>
        <p class="text-lg leading-relaxed text-(--ink)/70 md:col-span-5">
          Kein Zettel in der Angelbox, keine verlorenen Notizen. PetriLog begleitet dich an jeden Fluss und See
          in Österreich.
        </p>
      </div>

      <div class="relative mt-16 md:mt-24">
        <!-- the line running through the steps -->
        <svg class="absolute left-0 top-10 hidden h-24 w-full md:block" viewBox="0 0 1200 100" fill="none"
          preserveAspectRatio="none" aria-hidden="true">
          <path d="M0 50 C180 10 320 90 520 50 S880 10 1060 50 S1180 70 1200 60" stroke="var(--ink)"
            stroke-opacity="0.25" stroke-width="1.5" stroke-dasharray="2 7" stroke-linecap="round"
            vector-effect="non-scaling-stroke" />
        </svg>

        <ol class="relative grid gap-14 md:grid-cols-3 md:gap-10">
          <li v-for="(step, i) in steps" :key="step.number" class="ph-reveal"
            :style="{ transitionDelay: `${i * 120}ms` }">
            <span
              class="font-display block text-[6.5rem] leading-none text-transparent [-webkit-text-stroke:1.5px_var(--ink)] md:text-[8rem]">
              {{ step.number }}
            </span>
            <h3 class="mt-6 text-xl font-semibold">{{ step.title }}</h3>
            <p class="mt-3 max-w-sm leading-relaxed text-(--ink)/70">{{ step.text }}</p>
          </li>
        </ol>
      </div>

    </div>
  </section>
</template>
