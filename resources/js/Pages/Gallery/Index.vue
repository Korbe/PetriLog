<template>
  <PageWrapper title="Galerie" :backTo="route('dashboard')">
    <template v-slot:actions>
      <!-- <DropdownFilter :options="filters" @filtersChanged="handleFiltersChanged" /> -->
      <VDateRangePicker align="right" v-model="dateRange" />
      <ResetButton @click="resetDateRange" />
      <VButton :href="route('catched.create')">Eintragen</VButton>
    </template>

    <div class="w-full m-auto">


      <div v-if="catcheds.length === 0">
        <!-- Empty state and stats cards -->
        <div>
          <img src="images/fisher.webp" alt="Fischer" class="mx-auto mb-1 w-auto h-64" />

          <div
            class="mx-auto w-full lg:w-1/3 bg-white dark:bg-gray-800 shadow-md hover:shadow-xl rounded-lg p-8 flex transition-all duration-200 transform hover:scale-105 flex-col items-center text-center">
            <h1 class="text-2xl font-bold mb-2 text-gray-800 dark:text-gray-100">Noch keine Schnappschüsse vorhanden
            </h1>
            <p class="text-gray-700 dark:text-gray-300 mb-5">Lade deine ersten Bilder hoch!</p>

            <VButton class="px-6 py-3 text-lg rounded-lg font-semibold" :href="route('catched.create')">
              Ersten Fang eintragen
            </VButton>
          </div>
        </div>
      </div>

      <div class="p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
          <div v-for="item in catcheds" :key="item.id"
            class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
            <Link :href="route('catched.show', item.id)">
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

    </div>
  </PageWrapper>
</template>

<script setup lang="ts">
import VButton from '@/components/VButton.vue';
import VDateRangePicker from '@/components/VDateRangePicker.vue';
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
import { onMounted, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import ResetButton from '@/components/pagination/ResetButton.vue';

interface Props {
  catcheds: Array<Object>,
  dateRange: {
    startDate: string;
    endDate: string;
  };
}

const props = defineProps<Props>();

const filters = ref([
  { label: 'Beschreibung', value: 'onlyWithDescription', selected: false },
])

let isUpdating = false;

const originalDateRange = ref({
  startDate: new Date(props.dateRange?.startDate),
  endDate: new Date(props.dateRange?.endDate)
});

const dateRange = ref({
  startDate: new Date(props.dateRange?.startDate || new Date().setDate(new Date().getDate() - 7)),
  endDate: new Date(props.dateRange?.endDate || new Date())
});

watch(dateRange, (newRange) => {
  handleDateRangeUpdate(newRange);
}, { deep: true });

const getFilterParams = () => {
  return filters.value
    .filter(filter => filter.selected)
    .map(filter => ({ [filter.value]: 1 }))
    .reduce((acc, curr) => ({ ...acc, ...curr }), {});
};

const handleDateRangeUpdate = (newRange) => {
  // Check if both dates are present and valid
  if (!newRange?.startDate || !newRange?.endDate) {
    dateRange.value = newRange; // Update local state but don't trigger search
    return;
  }

  // Validate dates are actual Date objects
  if (!(newRange.startDate instanceof Date) || !(newRange.endDate instanceof Date))
    return;

  // Check if dates are valid (not NaN)
  if (isNaN(newRange.startDate.getTime()) || isNaN(newRange.endDate.getTime()))
    return;

  dateRange.value = newRange;

  search();
};

const handleFiltersChanged = (newFilters) => {
  filters.value = newFilters;

  search();
};

const resetDateRange = () => {
  dateRange.value = originalDateRange.value;
  search();
}

const search = () => {
  if (isUpdating) return;

  isUpdating = true;

  const filterParams = getFilterParams();

  const request = {
    startDate: dateRange.value.startDate.toLocaleString(),
    endDate: dateRange.value.endDate.toLocaleString(),
    ...filterParams
  };

  router.get(
    route('gallery.index'), request,
    {
      preserveState: true,
      preserveScroll: true,
      onFinish: () => {
        setTimeout(() => {
          isUpdating = false;
        }, 0);
      }
    }
  );
};

onMounted(() => {
  sessionStorage.setItem('lastOrigin', window.location.href);
});
</script>