<template>
    <PageWrapper title="Fänge" :eyebrow="eyebrow" backTo="/dashboard">
        <template v-slot:actions>
            <template v-if="dateRange">
                <VDateRangePicker align="right" v-model="dateRange" />
                <ResetButton @click="resetDateRange" />
            </template>
            <VButton v-if="canAddNewEntry()" :href="createUrl">
                <PlusIcon class="size-4" /> Eintragen
            </VButton>
        </template>

        <div class="mx-auto max-w-3xl">

            <MaxEntriesReachedBanner v-if="!canAddNewEntry()" />

            <!-- Empty State -->
            <div v-if="Object.keys(groupedCatcheds).length === 0"
                class="ui-card flex flex-col items-center p-8 text-center md:p-12">
                <img src="/images/fisher.webp" alt="" class="mb-4 h-48 w-auto" />
                <h2 class="font-display text-4xl">{{ props.totalCatchedCount ? 'Keine Fänge in diesem Zeitraum.' : 'Sieht ganz schön leer hier aus.' }}</h2>
                <p class="ui-muted mt-2">{{ props.totalCatchedCount ? 'Wähle einen anderen Zeitraum oder setze den Filter zurück.' : 'Trag am besten gleich deinen ersten Fang ein!' }}</p>
                <VButton v-if="!props.totalCatchedCount" class="mt-6" size="lg" :href="createUrl">
                    <PlusIcon class="size-5" /> Ersten Fang eintragen
                </VButton>
                <VButton v-else class="mt-6" variant="secondary" @click="resetDateRange">Zeitraum zurücksetzen</VButton>
            </div>

            <!-- Fänge gruppiert nach Datum -->
            <section v-for="(items, date) in groupedCatcheds" :key="date" class="mb-6">
                <button type="button" @click="toggleOpen(date)" :aria-expanded="isOpen(date)"
                    class="group mb-2 flex w-full items-center gap-3 px-1 text-left">
                    <span class="font-log text-xs uppercase tracking-[0.18em] text-(--app-subtle)">{{ weekday(date) }}</span>
                    <span class="text-base font-semibold">{{ date }}</span>
                    <span class="ui-badge">{{ items.length }} {{ items.length === 1 ? 'Fang' : 'Fänge' }}</span>
                    <span class="h-px flex-1 bg-(--app-border)"></span>
                    <ChevronDownIcon class="size-4 text-(--app-subtle) transition group-hover:text-(--app-ink)"
                        :class="isOpen(date) ? '' : '-rotate-90'" />
                </button>

                <transition name="fade">
                    <ul v-if="isOpen(date)" class="ui-card divide-y divide-(--app-border) overflow-hidden">
                        <li v-for="catched in items" :key="catched.id">
                            <Link :href="`/catched/${catched.id}`"
                                class="group flex items-center gap-4 p-3 pr-4 transition hover:bg-(--app-hover)">
                                <span class="size-14 shrink-0 overflow-hidden rounded-xl bg-(--app-surface-2)">
                                    <img v-if="thumb(catched)" :src="thumb(catched)" alt="" loading="lazy"
                                        class="h-full w-full object-cover" />
                                    <img v-else src="/logo-short.png" alt="" class="m-auto mt-3.5 size-7 opacity-40" />
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-semibold">{{ catched.fish?.name ?? 'Unbekannter Fisch' }}</p>
                                    <p class="truncate text-sm text-(--app-muted)">
                                        {{ catched.lake?.name ?? catched.river?.name ?? 'Unbekanntes Gewässer' }}
                                        <span class="font-log text-xs text-(--app-subtle)"> · {{ time(catched.date) }}</span>
                                    </p>
                                </div>
                                <div class="hidden shrink-0 gap-1.5 sm:flex">
                                    <span v-if="catched.length" class="ui-badge font-log">{{ catched.length }} cm</span>
                                    <span v-if="catched.weight" class="ui-badge font-log">{{ catched.weight }} g</span>
                                </div>
                                <ChevronRightIcon class="size-5 shrink-0 text-(--app-subtle) transition group-hover:translate-x-0.5" />
                            </Link>
                        </li>
                    </ul>
                </transition>
            </section>

            <!-- Load More Button -->
            <div v-if="Object.keys(groupedCatcheds).length" class="my-8 text-center">
                <VButton v-if="nextPageUrl" variant="secondary" @click="loadMore" :disabled="isLoadingMore"
                    :loading="isLoadingMore">
                    {{ isLoadingMore ? 'Lade …' : 'Weitere Fänge laden' }}
                </VButton>
                <span v-else class="ui-kicker">Das waren alle Fänge im Zeitraum</span>
            </div>

        </div>
    </PageWrapper>
</template>

<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { ref, watch, onMounted, computed } from 'vue';
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
import VDateRangePicker from '@/components/VDateRangePicker.vue';
import VButton from "@/components/VButton.vue";
import ResetButton from '@/components/pagination/ResetButton.vue';
import MaxEntriesReachedBanner from '@/components/MaxEntriesReachedBanner.vue';
import { ChevronRightIcon, ChevronDownIcon, PlusIcon } from '@heroicons/vue/24/outline';

interface Props {
    totalCatchedCount: number,
    groupedCatcheds: Record<string, any[]>,
    dateRange: { startDate: string; endDate: string } | null,
    createUrl: string,
    currentUrl: string,
    pagination?: { next_page_url: string | null }
}

const page = usePage()
const props = defineProps<Props>();

const eyebrow = computed(() => `${props.totalCatchedCount} ${props.totalCatchedCount === 1 ? 'Fang' : 'Fänge'} im Fangbuch`);

// Date Range & Filters (no date range when there are no catches yet)
const originalDateRange = ref(props.dateRange ? { ...props.dateRange } : null);
const dateRange = ref(props.dateRange ? { ...props.dateRange } : null);

const groupedCatcheds = ref({ ...props.groupedCatcheds });
const nextPageUrl = ref(props.pagination?.next_page_url ?? null);
const isLoadingMore = ref(false);

// Watcher für DateRange
watch(
    dateRange,
    (newRange, oldRange) => {
        if (!newRange || !oldRange) return;
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

// Day sections are open by default; we remember the ones the user collapsed.
const closedKeys = ref<string[]>([]);
const isOpen = (date: string) => !closedKeys.value.includes(date);
const toggleOpen = (date: string) => {
    closedKeys.value = isOpen(date) ? [...closedKeys.value, date] : closedKeys.value.filter(k => k !== date);
    sessionStorage.setItem('catched-closed', JSON.stringify(closedKeys.value));
};

onMounted(() => {
    const stored = sessionStorage.getItem('catched-closed');
    if (stored) {
        try { closedKeys.value = JSON.parse(stored); } catch (_) { closedKeys.value = []; }
    }
    sessionStorage.setItem('lastOrigin', window.location.href);
});

// Display helpers – group keys are "dd.mm.yyyy"
const weekday = (date: string) => {
    const [d, m, y] = date.split('.').map(Number);
    return new Date(y, m - 1, d).toLocaleDateString('de-AT', { weekday: 'short' });
};
const time = (value: string) => {
    const d = new Date(value);
    return `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`;
};
const thumb = (catched: any) => {
    const media = (catched.media ?? []).slice().sort((a: any, b: any) => a.order_column - b.order_column);
    return media[0]?.original_url ?? null;
};

// Search / Filter
let isUpdating = false;
const search = () => {
    if (isUpdating || !dateRange.value) return;
    isUpdating = true;

    router.get(
        props.currentUrl,
        {
            startDate: dateRange.value.startDate,
            endDate: dateRange.value.endDate,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['groupedCatcheds', 'pagination'],
            onSuccess: (page) => {
                // Neue Daten setzen
                groupedCatcheds.value = page.props.groupedCatcheds as Record<string, any[]>;
                nextPageUrl.value = (page.props.pagination as any)?.next_page_url || null;

                closedKeys.value = [];
                sessionStorage.removeItem('catched-closed');
            },
            onFinish: () => {
                isUpdating = false;
            }
        }
    );
};


const resetDateRange = () => {
    if (!originalDateRange.value) return;
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
            Object.entries(page.props.groupedCatcheds as Record<string, any[]>).forEach(([date, items]) => {
                if (!groupedCatcheds.value[date]) {
                    groupedCatcheds.value[date] = items;
                } else {
                    groupedCatcheds.value[date].push(...items);
                }
            });
            nextPageUrl.value = (page.props.pagination as any).next_page_url;
        },
        onFinish: () => { isLoadingMore.value = false; }
    });
};
</script>
