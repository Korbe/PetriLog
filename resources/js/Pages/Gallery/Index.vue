<template>
  <PageWrapper title="Gallerie" :backTo="route('dashboard')">
    <template v-slot:actions>
      <DropdownFilter :options="filters" @filtersChanged="handleFiltersChanged" />
      <VDateRangePicker align="right" v-model="dateRange" />
      <ResetButton @click="resetDateRange" />
      <VButton :href="route('catched.create')">Eintragen</VButton>
    </template>

    <div class="w-full m-auto">

      <div v-if="catcheds.length === 0"
        class="m-auto mt-20 w-1/2 text-center bg-white rounded-lg shadow-lg p-5 flex flex-col my-2">
        <p class="pb-5">Für diesen Zeitraum wurden keine Einträge mit Bildern gefunden</p>
        <VButton :href="route('catched.create')">Jetzt eintragen</VButton>
      </div>

      <div class="p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
          <div v-for="item in catcheds" :key="item.id" class="bg-white dark:bg-gray-800 shadow-md rounded-xl overflow-hidden">
            <Link :href="route('catched.show', item.id)">
            <img v-if="item.images.length" :src="item.images[0].url" alt="Bild" class="w-full h-48 object-cover" />
            <div class="p-4">
              <h2 class="text-lg font-semibold">{{ item.name }}</h2>
              <p class="text-sm text-gray-500">{{ item.date }}</p>
            </div>
            </Link>
          </div>
        </div>
      </div>

    </div>
  </PageWrapper>
</template>

<script setup lang="ts">
import DropdownFilter from '@/components/DropdownFilter.vue';
import VButton from '@/components/VButton.vue';
import VDateRangePicker from '@/components/VDateRangePicker.vue';
import PageWrapper from '@/Layouts/PageWrapper.vue';
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