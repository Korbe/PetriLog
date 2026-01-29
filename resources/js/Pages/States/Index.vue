<template>
  <PageWrapper title="Gewässer" :backTo="`/dashboard`">

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mt-10">
      <div v-for="state in states" :key="state.id">
        <Link :href="`/states/${state.id}`">
        <div
          class="bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition-shadow duration-200 overflow-hidden">

          <!-- Bild -->
          <div class="relative w-full h-44">
            <img :src="state.media[0]?.url ?? '/placeholder.png'" :alt="state.name"
              class="w-full h-full object-contain mt-5 hover:scale-105 transition-transform duration-200" />
          </div>

          <!-- Info-Bereich -->
          <div class="p-4 flex flex-col items-center text-center">
            <h2 class="text-xl font-bold">{{ state.name }}</h2>
            <p class="text-gray-700 dark:text-gray-300 mt-2 text-sm">
              {{ state.lakes_count }} {{ state.lakes_count === 1 ? 'See' : 'Seen' }} &nbsp;•&nbsp;
              {{ state.rivers_count }} {{ state.rivers_count === 1 ? 'Fluss' : 'Flüsse' }}
            </p>
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">
              {{ state.associations_count }} {{ state.associations_count === 1 ? 'Verein' : 'Vereine' }}
            </p>
          </div>

        </div>
        </Link>
      </div>
    </div>

    <div class="flex items-center gap-2 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 mt-5  px-4 py-3 text-sm">
      <span>
        Diese Liste ist unvollständig und wird laufend ergänzt. Sollten Sie Vorschläge oder Ergänzungen haben,
        schreiben Sie uns gerne per Mail
        <a href="mailto:info@petrilog.com" class="underline">info@petrilog.com</a>
      </span>
    </div>

  </PageWrapper>
</template>


<script setup>
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
import { onMounted } from 'vue';

defineProps({
  states: Array
});

onMounted(() => {
  sessionStorage.setItem('lastOrigin', window.location.href);
});
</script>