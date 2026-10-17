<script setup>
const species = [
  'Hecht', 'Zander', 'Huchen', 'Bachforelle', 'Äsche', 'Seesaibling', 'Karpfen', 'Wels',
  'Barsch', 'Regenbogenforelle', 'Schleie', 'Barbe', 'Nase', 'Renke', 'Rapfen', 'Aal',
];
</script>

<template>
  <section class="relative overflow-hidden bg-(--paper) py-8 md:py-10" aria-label="Heimische Fischarten">
    <div
      class="group flex w-full flex-nowrap [mask-image:linear-gradient(to_right,transparent,black_12%,black_88%,transparent)]">
      <ul v-for="copy in 2" :key="copy" :aria-hidden="copy === 2 ? 'true' : undefined"
        class="flex shrink-0 items-center animate-[infinite-scroll_70s_linear_infinite] group-hover:[animation-play-state:paused]">
        <li v-for="name in species" :key="name"
          class="font-display flex items-center whitespace-nowrap text-4xl italic text-(--ink)/80 md:text-6xl">
          <span class="px-6 md:px-8">{{ name }}</span>
          <!-- little fish -->
          <svg class="h-3 w-6 shrink-0 text-(--float) md:h-4 md:w-8" viewBox="0 0 28 14" aria-hidden="true">
            <path d="M1 7c3-4.5 9-6 14-4.2 2 .7 3.6 1.9 4.6 3.2L27 1.5v11L19.6 8c-1 1.3-2.6 2.5-4.6 3.2C10 13 4 11.5 1 7Z"
              fill="currentColor" />
            <circle cx="6.5" cy="6" r="1.1" fill="var(--paper)" />
          </svg>
        </li>
      </ul>
    </div>
  </section>
</template>
