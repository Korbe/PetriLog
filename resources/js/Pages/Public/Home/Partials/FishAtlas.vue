<script setup>
const fish = [
  // brightness lifts the JPEG noise in the off-white background to pure white
  { name: 'Hecht', latin: 'Esox lucius', src: '/images/fish/Hecht.jpg', cls: 'max-h-full w-full brightness-[1.05]' },
  { name: 'Huchen', latin: 'Hucho hucho', src: '/images/fish/huchen.png' },
  { name: 'Bachsaibling', latin: 'Salvelinus fontinalis', src: '/images/fish/Bachsaibling.jpeg' },
  { name: 'Schleie', latin: 'Tinca tinca', src: '/images/fish/Schleie.jpg' },
  { name: 'Barbe', latin: 'Barbus barbus', src: '/images/fish/Barbe.jpeg' },
  // the source image has a lot of empty margin
  { name: 'Nase', latin: 'Chondrostoma nasus', src: '/images/fish/Nase.jpg', cls: 'h-[150%] w-[150%] max-w-none' },
];
</script>

<template>
  <section class="relative bg-(--paper)">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-20 md:py-28">

      <div class="ph-reveal grid gap-6 md:grid-cols-12 md:items-end">
        <div class="md:col-span-7">
          <p class="font-log text-xs uppercase tracking-[0.22em] text-(--ink)/55">04 — Heimische Arten</p>
          <h2 class="font-display mt-4 text-5xl leading-[1] md:text-6xl">
            Von der Nase bis zum <em class="text-primary-700">Huchen.</em>
          </h2>
        </div>
        <p class="text-lg leading-relaxed text-(--ink)/70 md:col-span-5">
          Im Gewässer-Wiki findest du die Fischarten deiner Gewässer – und trägst jeden Fang mit der richtigen Art
          ein.
        </p>
      </div>

      <ul class="mt-14 grid gap-px overflow-hidden rounded-[2rem] bg-(--ink)/10 ring-1 ring-(--ink)/10 sm:grid-cols-2 lg:grid-cols-3">
        <li v-for="(f, i) in fish" :key="f.name"
          class="ph-reveal group relative flex flex-col bg-(--paper) p-7 transition-colors duration-500 hover:bg-white"
          :style="{ transitionDelay: `${(i % 3) * 90}ms` }">
          <span class="font-log text-[10px] uppercase tracking-[0.2em] text-(--ink)/40">
            No. {{ String(i + 1).padStart(2, '0') }}
          </span>
          <!-- transforms stay on the img itself: a transformed wrapper would isolate the blend -->
          <div class="flex h-36 items-center justify-center py-4">
            <img :src="f.src" :alt="f.name" loading="lazy"
              class="object-contain mix-blend-multiply transition-transform duration-700 ease-[cubic-bezier(.2,.7,.2,1)] group-hover:-translate-y-1 group-hover:-rotate-2 group-hover:scale-[1.04]"
              :class="f.cls ?? 'max-h-full w-full'" />
          </div>
          <div class="mt-auto flex items-baseline justify-between gap-4 border-t border-(--ink)/10 pt-4">
            <h3 class="font-display text-3xl">{{ f.name }}</h3>
            <p class="font-log text-right text-[11px] italic text-(--ink)/55">{{ f.latin }}</p>
          </div>
        </li>
      </ul>

    </div>
  </section>
</template>
