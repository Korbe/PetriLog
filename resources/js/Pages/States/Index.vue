<template>
  <PageWrapper title="Gewässer-Wiki" eyebrow="Österreichs Bundesländer" :backTo="`/dashboard`">

    <p class="ui-muted -mt-2 mb-6 max-w-2xl md:mb-8">
      Seen, Flüsse und Fischereivereine – sortiert nach Bundesland. Wähle ein Bundesland, um seine Gewässer zu sehen.
    </p>

    <div class="grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-5 xl:grid-cols-4">
      <Link v-for="(state, i) in states" :key="state.id" :href="`/states/${state.id}`"
        class="ui-card ui-card-hover app-fade-up group flex flex-col items-center p-5 text-center md:p-6"
        :style="{ animationDelay: `${i * 40}ms` }">
        <div class="flex h-28 w-full items-center justify-center md:h-32">
          <img :src="state.media[0]?.url ?? '/placeholder.png'" :alt="`Wappen ${state.name}`" loading="lazy"
            class="max-h-full max-w-[70%] object-contain transition duration-300 group-hover:scale-105" />
        </div>
        <h2 class="font-display mt-4 text-2xl leading-tight md:text-3xl">{{ state.name }}</h2>
        <div class="mt-3 flex flex-wrap justify-center gap-1.5">
          <span class="ui-badge">{{ state.lakes_count }} {{ state.lakes_count === 1 ? 'See' : 'Seen' }}</span>
          <span class="ui-badge">{{ state.rivers_count }} {{ state.rivers_count === 1 ? 'Fluss' : 'Flüsse' }}</span>
          <span class="ui-badge ui-badge-accent">{{ state.associations_count }} {{ state.associations_count === 1 ? 'Verein' :
            'Vereine' }}</span>
        </div>
      </Link>
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
import { InformationCircleIcon } from '@heroicons/vue/24/outline';
import { onMounted } from 'vue';

defineProps({
  states: Array
});

onMounted(() => {
  sessionStorage.setItem('lastOrigin', window.location.href);
});
</script>
