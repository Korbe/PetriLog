<template>
  <PageWrapper title="Fisch-Wiki" :eyebrow="`${fish.length} heimische Arten`" :backTo="route('app.dashboard')">

    <template v-slot:actions>
      <div class="ui-search w-full md:w-72">
        <MagnifyingGlassIcon />
        <input v-model="search" type="search" class="form-input rounded-full!" placeholder="Fischart suchen …"
          aria-label="Fischart suchen" />
      </div>
    </template>

    <div v-if="filteredFish.length" class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:gap-5 xl:grid-cols-4">
      <Link v-for="f in filteredFish" :key="f.id" :href="route('app.fish.show', f.id)"
        class="ui-card ui-card-hover group flex flex-col overflow-hidden">
        <!-- illustrations have a white background: keep the frame white in both themes -->
        <div class="flex h-32 items-center justify-center bg-white p-4 md:h-40">
          <img :src="f.media[0]?.url || '/images/fish-default.png'" :alt="f.name" loading="lazy"
            class="max-h-full w-full object-contain transition duration-500 group-hover:-rotate-2 group-hover:scale-105" />
        </div>
        <div class="flex items-center justify-between gap-2 border-t border-(--app-border) px-4 py-3">
          <h2 class="font-display truncate text-2xl leading-tight">{{ f.name }}</h2>
          <ChevronRightIcon class="size-4 shrink-0 text-(--app-subtle) transition group-hover:translate-x-0.5" />
        </div>
      </Link>
    </div>

    <div v-else class="ui-card p-10 text-center">
      <p class="font-display text-3xl">Kein Fisch gefunden.</p>
      <p class="ui-muted mt-2 text-sm">Keine Fischart passt zu „{{ search }}“.</p>
    </div>

    <div class="ui-panel mt-6 flex items-start gap-3 px-4 py-3 text-sm text-(--app-muted) md:mt-8">
      <InformationCircleIcon class="mt-0.5 size-5 shrink-0 text-(--app-accent-ink)" />
      <span>
        Diese Liste ist unvollständig und wird laufend ergänzt. Vorschläge oder Ergänzungen gerne per Mail an
        <a href="mailto:info@petrilog.com" class="ui-link">info@petrilog.com</a>.
      </span>
    </div>
  </PageWrapper>
</template>

<script setup>
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
import { MagnifyingGlassIcon, ChevronRightIcon, InformationCircleIcon } from '@heroicons/vue/24/outline';
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
  fish: {
    type: Array,
    required: true
  }
});

const search = ref("");

const filteredFish = computed(() => {
  return props.fish
    .filter(f => !search.value || f.name.toLowerCase().includes(search.value.toLowerCase()));
});

onMounted(() => {
  sessionStorage.setItem('lastOrigin', window.location.href);
});
</script>
