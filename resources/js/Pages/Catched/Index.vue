<template>
    <PageWrapper title="Fänge" backTo="/dashboard">
        <template v-slot:actions>
            <VDateRangePicker align="right" v-model="dateRange" />
            <ResetButton @click="resetDateRange" />
            <VButton v-if="canAddNewEntry()" :href="createUrl">Eintragen</VButton>
        </template>

        <div class="md:w-1/2 m-auto">

            <MaxEntriesReachedBanner v-if="!canAddNewEntry()" />

            <!-- Empty State -->
            <div v-if="Object.keys(groupedCatcheds).length === 0">
                <div class="text-center">
                    <img src="images/fisher.webp" alt="Fischer" class="mx-auto mb-1 w-auto h-64" />
                    <div
                        class="mx-auto w-full lg:w-2/3 bg-white dark:bg-gray-800 shadow-md hover:shadow-xl rounded-lg p-8 flex flex-col items-center text-center transition-all duration-200 transform hover:scale-105">
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

            <!-- Fänge gruppiert nach Datum -->
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
                                <span>{{ catched.lake?.name ?? catched.river?.name ?? 'Unbekanntes Gewässer' }}</span>
                            </span>
                            <ChevronRightIcon class="h-6" />
                            </Link>
                        </div>
                    </div>
                </transition>
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
import { MinusIcon, PlusIcon } from '@heroicons/vue/24/outline'
import { router, usePage } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
import VDateRangePicker from '@/components/VDateRangePicker.vue';
import VButton from "@/components/VButton.vue";
import ResetButton from '@/components/pagination/ResetButton.vue';
import MaxEntriesReachedBanner from '@/components/MaxEntriesReachedBanner.vue';
import { ChevronRightIcon } from '@heroicons/vue/24/solid';

interface Props {
    totalCatchedCount: number,
    groupedCatcheds: Record<string, any[]>,
    dateRange: { startDate: string; endDate: string },
    createUrl: string,
    currentUrl: string,
    pagination: { next_page_url: string | null }
}

const page = usePage()
const props = defineProps<Props>();

// Date Range & Filters
const originalDateRange = ref({ ...props.dateRange });
const dateRange = ref({ ...props.dateRange });

const groupedCatcheds = ref({ ...props.groupedCatcheds });
const nextPageUrl = ref(props.pagination.next_page_url);
const isLoadingMore = ref(false);

// Watcher für DateRange
watch(
    dateRange,
    (newRange, oldRange) => {
        // Prüfen, ob wirklich neue Werte gesetzt wurden
        if (
            newRange.startDate !== oldRange.startDate ||
            newRange.endDate !== oldRange.endDate
        ) {
            search();
        }
    },
    { deep: true } // wichtig, weil dateRange ein Objekt ist
);

const canAddNewEntry = () => {
    if (page.props.auth.user.subscribed) return true;
    return props.totalCatchedCount < 5;
};

// Accordion state
const openKeys = ref<string[]>([]);
const isOpen = (date: string) => openKeys.value.includes(date);
const toggleOpen = (date: string) => {
    if (openKeys.value.includes(date)) {
        openKeys.value = openKeys.value.filter(k => k !== date);
    } else {
        openKeys.value.push(date);
    }
    sessionStorage.setItem('catched-open', JSON.stringify(openKeys.value));
};

onMounted(() => {
    const stored = sessionStorage.getItem('catched-open');
    if (stored) {
        try { openKeys.value = JSON.parse(stored); } catch (_) { openKeys.value = []; }
    }
});

// Search / Filter
let isUpdating = false;
const search = () => {
    if (isUpdating) return;
    isUpdating = true;

    router.get(
        props.currentUrl,
        {
            startDate: dateRange.value.startDate,
            endDate: dateRange.value.endDate,
            // ggf. Filter-Params
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['groupedCatcheds', 'pagination'],
            onSuccess: (page) => {
                // Neue Daten setzen
                groupedCatcheds.value = page.props.groupedCatcheds;
                nextPageUrl.value = page.props.pagination?.next_page_url || null;

                openKeys.value = [];
                sessionStorage.removeItem('catched-open');
            },
            onFinish: () => {
                isUpdating = false;
            }
        }
    );
};


const resetDateRange = () => {
    dateRange.value = { ...originalDateRange.value };
    search();
};

// Load more
const loadMore = async () => {
    if (!nextPageUrl.value || isLoadingMore.value) return;

    isLoadingMore.value = true;

    await router.get(nextPageUrl.value, {}, {
        preserveState: true,
        preserveScroll: true,
        replace: false,
        only: ['groupedCatcheds', 'pagination'],
        onSuccess: (page) => {
            // Neue Items anhängen
            Object.entries(page.props.groupedCatcheds).forEach(([date, items]) => {
                if (!groupedCatcheds.value[date]) {
                    groupedCatcheds.value[date] = items;
                } else {
                    groupedCatcheds.value[date].push(...items);
                }
            });
            nextPageUrl.value = page.props.pagination.next_page_url;
        },
        onFinish: () => { isLoadingMore.value = false; }
    });
};
</script>