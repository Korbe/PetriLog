<template>
  <PageWrapper title="Galerie" :backTo="route('app.dashboard')">
    <template v-slot:actions>
      <DropdownFilter :options="filters" @filtersChanged="handleFiltersChanged" />
      <VDateRangePicker align="right" v-model="dateRange" />
      <ResetButton @click="resetDateRange" />
      <VButton :href="route('app.catched.create')">Eintragen</VButton>
    </template>

    <div class="w-full m-auto">

      <!-- Empty State -->
      <div v-if="catcheds.length === 0" class="text-center">
        <img src="images/fisher.webp" alt="Fischer" class="mx-auto mb-1 w-auto h-64" />
        <div
          class="mx-auto w-full lg:w-1/3 bg-white dark:bg-gray-800 shadow-md hover:shadow-xl rounded-lg p-8 flex transition-all duration-200 transform hover:scale-105 flex-col items-center text-center">
          <h1 class="text-2xl font-bold mb-2 text-gray-800 dark:text-gray-100">Noch keine Schnappschüsse vorhanden</h1>
          <p class="text-gray-700 dark:text-gray-300 mb-5">Lade deine ersten Bilder hoch!</p>
          <VButton class="px-6 py-3 text-lg rounded-lg font-semibold" :href="route('app.catched.create')">
            Ersten Fang eintragen
          </VButton>
        </div>
      </div>

      <!-- Galerie -->
      <div class="p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
          <div v-for="item in catcheds" :key="item.id"
            class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
            <Link :href="route('app.catched.show', item.id)">
            <img v-if="item.images.length" :src="item.images[0].url" alt="Bild" class="w-full h-48 object-cover" />
            <div class="p-4">
              <h2 class="text-lg font-semibold">{{ item.name }}</h2>
              <p class="text-sm text-gray-500">{{ item.date }} - <span class="text-primary-500">{{ item.water.name
              }}</span>
              </p>
            </div>
            </Link>
          </div>
        </div>
      </div>

      <!-- Load More Button -->
      <div class="text-center my-6">
        <VButton v-if="nextPageUrl" @click="loadMore" :disabled="isLoadingMore" class="px-6 py-2">
          {{ isLoadingMore ? 'Lade...' : 'Weitere Fänge laden' }}
        </VButton>
        <span v-else class="text-gray-500">Keine weiteren Fänge</span>
      </div>
    </div>
  </PageWrapper>
</template>

<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
import VDateRangePicker from '@/components/VDateRangePicker.vue';
import VButton from '@/components/VButton.vue';
import ResetButton from '@/components/pagination/ResetButton.vue';
import DropdownFilter from '@/components/DropdownFilter.vue';

interface Props {
  catcheds: Array<any>,
  dateRange: { startDate: string; endDate: string },
  pagination: { next_page_url: string | null }
}

const props = defineProps<Props>();

// Data
const catcheds = ref([...props.catcheds]);
const nextPageUrl = ref(props.pagination.next_page_url);
const isLoadingMore = ref(false);

const originalDateRange = ref({ ...props.dateRange });
const dateRange = ref({ ...props.dateRange });

const filters = ref([
  { label: 'Beschreibung', value: 'onlyWithDescription', selected: false },
]);

let isUpdating = false;

// Watcher für Datum
watch(dateRange, (newRange, oldRange) => {
  if (newRange.startDate !== oldRange.startDate || newRange.endDate !== oldRange.endDate) {
    search();
  }
}, { deep: true });

// Filter Änderungen
const handleFiltersChanged = (newFilters) => {
  filters.value = newFilters;
  search();
};

// Reset Date Range
const resetDateRange = () => {
  dateRange.value = { ...originalDateRange.value };
  search();
};

// Filter Param Helper
const getFilterParams = () => filters.value
  .filter(f => f.selected)
  .reduce((acc, f) => ({ ...acc, [f.value]: 1 }), {});

// Search / Filter
const search = () => {
  if (isUpdating) return;
  isUpdating = true;

  router.get(
    route('app.gallery.index'),
    {
      startDate: dateRange.value.startDate,
      endDate: dateRange.value.endDate,
      ...getFilterParams()
    },
    {
      preserveState: true,
      preserveScroll: true,
      only: ['catcheds', 'pagination'],
      onSuccess: (page) => {
        // Neue Items überschreiben
        catcheds.value = [...page.props.catcheds];
        nextPageUrl.value = page.props.pagination.next_page_url || null;
      },
      onFinish: () => { isUpdating = false; }
    }
  );
};

// Load More Button
const loadMore = async () => {
  if (!nextPageUrl.value || isLoadingMore.value) return;

  isLoadingMore.value = true;

  await router.get(nextPageUrl.value, {}, {
    preserveState: true,
    preserveScroll: true,
    replace: false,
    only: ['catcheds', 'pagination'],
    onSuccess: (page) => {
      // Neue Items anhängen
      catcheds.value.push(...page.props.catcheds);
      nextPageUrl.value = page.props.pagination.next_page_url || null;
    },
    onFinish: () => { isLoadingMore.value = false; }
  });
};

onMounted(() => {
  sessionStorage.setItem('lastOrigin', window.location.href);
});
</script>