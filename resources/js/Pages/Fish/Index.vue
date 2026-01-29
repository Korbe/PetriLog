<template>
  <PageWrapper title="Fische" :backTo="route('dashboard')">

    <template v-slot:actions>
      <VInput placeholder="Suche" v-model="search" />
    </template>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 pt-5">
      <div v-for="fish in filteredFish" :key="fish.id"
        class="bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition-shadow duration-200 overflow-hidden">
        <Link :href="route('fish.show', fish.id)">
        <div class="relative w-full h-44">
          <img :src="fish.media[0]?.url || '/images/fish-default.png'" :alt="fish.name"
            class="w-full h-full object-contain hover:scale-105 transition-transform duration-200" />
          <div
            class="absolute bottom-0 left-0 w-full bg-primary-500 bg-opacity-50 text-white text-center py-1 font-semibold">
            {{ fish.name }}
          </div>
        </div>
        </Link>
      </div>
    </div>

    <div
      class="flex items-center gap-2 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 mt-5 px-4 py-3 text-sm mt-2">
      <span>
        Diese Liste ist unvollständig und wird laufend ergänzt. Sollten Sie Vorschläge oder Ergänzungen haben schreiben
        Sie
        uns gerne per Mail
        <a href="mailto:info@petrilog.com">info@petrilog.com</a>
      </span>
    </div>
  </PageWrapper>
</template>

<script setup>
import VInput from '@/components/VInput.vue';
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
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
