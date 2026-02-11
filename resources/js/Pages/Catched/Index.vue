<template>
    <PageWrapper title="Fänge" backTo="/dashboard">
        <template v-slot:actions>
            <VDateRangePicker align="right" v-model="dateRange" />
            <ResetButton @click="resetDateRange" />
            <VButton v-if="canAddNewEntry()" :href="createUrl">Eintragen</VButton>
        </template>

        <div class="md:w-1/2 m-auto">

            <MaxEntriesReachedBanner v-if="!canAddNewEntry()" />

            <!-- Empty state and stats cards -->
            <div v-if="Object.keys(groupedCatcheds).length === 0" class="">

                <div>
                    <img src="images/fisher.webp" alt="Fischer" class="mx-auto mb-1 w-auto h-64" />

                    <div
                        class="mx-auto w-full lg:w-2/3 bg-white dark:bg-gray-800 shadow-md hover:shadow-xl rounded-lg p-8 flex transition-all duration-200 transform hover:scale-105 flex-col items-center text-center">
                        <h1 class="text-2xl font-bold mb-2 text-gray-800 dark:text-gray-100">Sieht ganz schön leer hier
                            aus.
                        </h1>
                        <p class="text-gray-700 dark:text-gray-300 mb-5">Trag am Besten deinen ersten Fang ein!</p>

                        <VButton class="px-6 py-3 text-lg rounded-lg font-semibold" :href="createUrl">
                            Ersten Fang eintragen
                        </VButton>
                    </div>
                </div>
            </div>

            <div v-for="(items, date) in groupedCatcheds" :key="date" class="py-2">
                <div>
                    <button @click="toggleOpen(date)"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-5 flex w-full items-start justify-between text-left text-brand-headline sm:text-3xl dark:text-brand-headline-dark">
                        <span class="cursor-pointer text-base/7 font-semibold">🎣 {{ date }} ({{ items.length }})</span>
                        <span class="ml-6 flex h-7 items-center">
                            <PlusIcon v-if="!isOpen(date)" class="cursor-pointer size-6" />
                            <MinusIcon v-else class="cursor-pointer size-6" />
                        </span>
                    </button>
                </div>

                <transition name="fade">
                    <div v-if="isOpen(date)" class="mt-2 pl-2 pr-2">
                        <div v-for="catched in items" :key="catched.id">
                            <Link :href="`/catched/${catched.id}`"
                                class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-5 flex justify-between my-2">
                            <span>
                                <span class="text-primary-500"><b>{{ catched.fish?.name ?? 'Unbekannt' }}</b></span>
                                -
                                <span>
                                    {{ catched.lake?.name ?? catched.river?.name ?? 'Unbekanntes Gewässer' }}
                                </span>
                            </span>
                            <ChevronRightIcon class="h-6" />
                            </Link>
                        </div>
                    </div>
                </transition>
            </div>
        </div>
    </PageWrapper>
</template>

<script setup lang="ts">
import { MinusIcon, PlusIcon } from '@heroicons/vue/24/outline'
import { router, usePage } from '@inertiajs/vue3';
import VButton from "@/components/VButton.vue";
import { ChevronRightIcon } from '@heroicons/vue/24/solid';
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
import { computed, onMounted, ref, watch } from 'vue';
import VDateRangePicker from '@/components/VDateRangePicker.vue';
import ResetButton from '@/components/pagination/ResetButton.vue';
import MaxEntriesReachedBanner from '@/components/MaxEntriesReachedBanner.vue';

interface Fish { id: number; name: string }
interface Lake { id: number; name: string }
interface River { id: number; name: string }

interface CatchedData {
    id: number;
    user_id: number;
    fish?: Fish;
    lake?: Lake;
    river?: River;
    length: number;
    weight: number;
    depth: number;
    temperature: number;
    date: string;
    latitude: number;
    longitude: number;
    address: string;
    remark: string;
    created_at: string;
    updated_at: string;
}

interface Props {
    totalCatchedCount: number,
    groupedCatcheds: Record<string, CatchedData[]>;
    dateRange: { startDate: string; endDate: string; };
    createUrl: string,
    currentUrl: string,
}

const page = usePage()
const props = defineProps<Props>();

const canAddNewEntry = () => {
    if (page.props.auth.user.subscribed) return true;
    return props.totalCatchedCount < 5;
}

let isUpdating = false;

const originalDateRange = ref({
    startDate: new Date(props.dateRange?.startDate),
    endDate: new Date(props.dateRange?.endDate)
});

const dateRange = ref({
    startDate: new Date(props.dateRange?.startDate || new Date().setDate(new Date().getDate() - 7)),
    endDate: new Date(props.dateRange?.endDate || new Date())
});

const filters = ref([
    { label: 'Beschreibung', value: 'onlyWithDescription', selected: false },
])

watch(dateRange, (newRange) => handleDateRangeUpdate(newRange), { deep: true });

const getFilterParams = () => {
    return filters.value
        .filter(filter => filter.selected)
        .map(filter => ({ [filter.value]: 1 }))
        .reduce((acc, curr) => ({ ...acc, ...curr }), {});
};

const handleDateRangeUpdate = (newRange) => {
    if (!newRange?.startDate || !newRange?.endDate) return;
    if (!(newRange.startDate instanceof Date) || !(newRange.endDate instanceof Date)) return;
    if (isNaN(newRange.startDate.getTime()) || isNaN(newRange.endDate.getTime())) return;

    dateRange.value = newRange;
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

    router.get(
        props.currentUrl,
        {
            startDate: dateRange.value.startDate.toLocaleString(),
            endDate: dateRange.value.endDate.toLocaleString(),
            ...filterParams
        },
        {
            preserveState: true,
            preserveScroll: true,
            onFinish: () => { isUpdating = false; }
        }
    );
};

// Accordion state
const openKeys = ref<string[]>([]);

const isOpen = (date: string) => openKeys.value.includes(date);
const toggleOpen = (key: string) => {
    if (openKeys.value.includes(key))
        openKeys.value = openKeys.value.filter(k => k !== key);
    else
        openKeys.value.push(key);

    sessionStorage.setItem('catched-open', JSON.stringify(openKeys.value));
};

onMounted(() => {
    const stored = sessionStorage.getItem('catched-open');
    if (stored) {
        try { openKeys.value = JSON.parse(stored); } catch (_) { openKeys.value = []; }
    }
});
</script>