<template>
  <PageWrapper title="Galerie" eyebrow="Deine Fangfotos" :backTo="route('app.dashboard')">
    <template v-slot:actions>
      <DropdownFilter :options="filters" @filtersChanged="handleFiltersChanged" />
      <VDateRangePicker align="right" v-model="dateRange" />
      <ResetButton @click="resetDateRange" />
      <VButton :href="route('app.catched.create')">
        <PlusIcon class="size-4" /> Eintragen
      </VButton>
    </template>

    <!-- Empty State -->
    <div v-if="catcheds.length === 0" class="ui-card mx-auto flex max-w-2xl flex-col items-center p-8 text-center md:p-12">
      <img src="/images/fisher.webp" alt="" class="mb-4 h-48 w-auto" />
      <h2 class="font-display text-4xl">Noch keine Schnappschüsse.</h2>
      <p class="ui-muted mt-2">Fänge mit Fotos landen automatisch hier – auch in anderen Zeiträumen lohnt ein Blick.</p>
      <VButton class="mt-6" size="lg" :href="route('app.catched.create')">
        <PlusIcon class="size-5" /> Fang mit Foto eintragen
      </VButton>
    </div>

    <!-- Galerie -->
    <div v-else class="grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-5 xl:grid-cols-4">
      <Link v-for="item in catcheds" :key="item.id" :href="route('app.catched.show', item.id)"
        class="group relative block overflow-hidden rounded-[1.25rem] bg-(--app-surface-2) shadow-(--app-shadow)">
        <img v-if="item.images.length" :src="item.images[0].url" :alt="item.name" loading="lazy"
          class="aspect-[4/5] w-full object-cover transition duration-500 group-hover:scale-105" />
        <div class="pointer-events-none absolute inset-0 bg-linear-to-t from-black/75 via-black/10 to-transparent"></div>
        <span v-if="item.images.length > 1"
          class="font-log absolute right-3 top-3 rounded-full bg-black/45 px-2 py-0.5 text-[11px] text-white backdrop-blur-sm">
          {{ item.images.length }} Fotos
        </span>
        <div class="absolute inset-x-0 bottom-0 p-3 text-white md:p-4">
          <p class="truncate text-base font-semibold md:text-lg">{{ item.name }}</p>
          <p class="truncate text-xs text-white/75 md:text-sm">
            {{ item.date }}<template v-if="item.water"> · {{ item.water.name }}</template>
          </p>
          <p v-if="item.length" class="font-log mt-1 text-[11px] text-white/70">{{ item.length }} cm<template v-if="item.weight"> · {{ item.weight }} g</template></p>
        </div>
      </Link>
    </div>

    <!-- Load More Button -->
    <div v-if="catcheds.length" class="my-8 text-center">
      <VButton v-if="nextPageUrl" variant="secondary" @click="loadMore" :disabled="isLoadingMore" :loading="isLoadingMore">
        {{ isLoadingMore ? 'Lade …' : 'Weitere Fotos laden' }}
      </VButton>
      <span v-else class="ui-kicker">Das waren alle Fotos im Zeitraum</span>
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
import { PlusIcon } from '@heroicons/vue/24/outline';

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