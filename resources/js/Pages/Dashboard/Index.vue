<template>
    <PageWrapper :title="`Petri Heil, ${firstName}!`" :eyebrow="today" hideBackButton>
        <div class="space-y-5 md:space-y-6">

            <EmailVerificationBanner />

            <InstallAppCard />

            <MaxEntriesReachedBanner v-if="limitReached" />

            <!-- Empty state -->
            <div v-if="catchedCount == 0"
                class="ui-card app-fade-up grid items-center gap-8 overflow-hidden p-6 md:grid-cols-[1fr_auto] md:p-10">
                <div>
                    <p class="ui-kicker">Dein Fangbuch</p>
                    <h2 class="font-display mt-2 text-4xl leading-tight md:text-5xl">Noch leer – aber der nächste Biss
                        kommt bestimmt.</h2>
                    <p class="ui-muted mt-3 max-w-xl">Trag deinen ersten Fang ein. Mit dem Express-Eintrag geht das in
                        wenigen Sekunden direkt am Wasser.</p>

                    <ul class="mt-6 grid gap-3 sm:grid-cols-3">
                        <li v-for="tip in tips" :key="tip.title" class="ui-panel p-4">
                            <p class="text-sm font-semibold">{{ tip.title }}</p>
                            <p class="ui-muted mt-1 text-xs leading-relaxed">{{ tip.text }}</p>
                        </li>
                    </ul>

                    <VButton class="mt-7" size="lg" :href="createUrl">
                        <PlusIcon class="size-5" />
                        Ersten Fang eintragen
                    </VButton>
                </div>
                <img src="/images/fisher.webp" alt="" class="mx-auto h-56 w-auto md:h-72" />
            </div>

            <template v-else>
                <!-- Key figures -->
                <section class="grid grid-cols-2 gap-3 md:gap-5 lg:grid-cols-4" aria-label="Kennzahlen">
                    <div v-for="(stat, i) in stats" :key="stat.label" class="ui-card app-fade-up p-5"
                        :style="{ animationDelay: `${i * 60}ms` }">
                        <p class="ui-kicker">{{ stat.label }}</p>
                        <p class="ui-stat-value mt-3">{{ stat.value ?? '–' }}</p>
                        <p class="mt-1 text-xs text-(--app-subtle)">{{ stat.hint }}</p>
                    </div>
                </section>

                <!-- Season + highlights -->
                <section class="grid gap-5 md:gap-6 lg:grid-cols-3">
                    <div class="ui-card p-6 lg:col-span-2">
                        <CatchedStatsYearlyCard :data="catchedStatsYearly" />
                    </div>
                    <HighlightCard :favoriteFish="favoriteFish" :favoriteLocation="favoriteLocation"
                        :mostCatchesDay="mostCatchesDay" />
                </section>

                <!-- Records + month -->
                <section class="grid gap-5 md:gap-6 md:grid-cols-2 xl:grid-cols-3">
                    <LongestCatchCard :catched="longestCatch" :route="routeLongest" />
                    <HeaviestCatchCard :catched="heaviestCatch" :route="routeHeaviest" />
                    <div class="ui-card p-6 md:col-span-2 xl:col-span-1">
                        <CatchedStatsMonthlyCard :data="catchedStatsMonthly" />
                    </div>
                </section>

                <!-- Recent + quick action -->
                <section class="grid gap-5 md:gap-6 lg:grid-cols-3">
                    <div class="lg:col-span-2">
                        <RecentCatches :recentCatches="recentCatches" />
                    </div>
                    <Link :href="createUrl"
                        class="group relative flex flex-col justify-between overflow-hidden rounded-[1.25rem] bg-linear-to-br from-[#06212b] via-[#0a3140] to-[#104456] p-6 text-[#f3eee3] shadow-(--app-shadow)">
                        <div class="absolute -right-10 -top-14 size-48 rounded-full bg-primary-500/30 blur-3xl"
                            aria-hidden="true"></div>
                        <div class="relative">
                            <p class="font-log text-[11px] uppercase tracking-[0.2em] text-[#ff8a5c]">Fisch gefangen?</p>
                            <p class="font-display mt-2 text-4xl leading-tight">Petri Heil! Trag ihn gleich ein.</p>
                        </div>
                        <span
                            class="relative mt-8 inline-flex items-center gap-2 self-start rounded-full bg-white px-5 py-3 text-sm font-semibold text-[#0f1d22] transition group-hover:bg-[#f3eee3]">
                            <PlusIcon class="size-4" /> Fang eintragen
                        </span>
                    </Link>
                </section>

                <p class="ui-muted pt-2 text-center text-sm">
                    Im Dashboard fehlt noch was?
                    <Link href="/ticket" class="ui-link">Sag es uns</Link>
                    oder schreib an <a class="ui-link" href="mailto:info@petrilog.com">info@petrilog.com</a>.
                </p>
            </template>
        </div>
    </PageWrapper>
</template>
<script setup>
import VButton from '@/components/VButton.vue';
import CatchedStatsMonthlyCard from './partials/CatchedStatsMonthlyCard.vue';
import CatchedStatsYearlyCard from './partials/CatchedStatsYearlyCard.vue';
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
import { computed, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import EmailVerificationBanner from '@/components/EmailVerificationBanner.vue';
import MaxEntriesReachedBanner from '@/components/MaxEntriesReachedBanner.vue';
import LongestCatchCard from './partials/LongestCatchCard.vue';
import HeaviestCatchCard from './partials/HeaviestCatchCard.vue';
import { PlusIcon } from '@heroicons/vue/24/outline';
import InstallAppCard from '@/components/InstallAppCard.vue';
import HighlightCard from './partials/HighlightCard.vue';
import RecentCatches from './partials/RecentCatches.vue';

const props = defineProps({
    catchedCount: Number,
    catchedStatsMonthly: {
        total: {
            type: Number,
            required: true,
        },
        timestamps: {
            type: Array,
            required: true,
        },
        scores: {
            type: Array,
            required: true,
        },
    },
    catchedStatsYearly: {
        total: {
            type: Number,
            required: true,
        },
        timestamps: {
            type: Array,
            required: true,
        },
        scores: {
            type: Array,
            required: true,
        },
    },
    heaviestCatch: Object,
    longestCatch: Object,

    createUrl: String,
    routeHeaviest: String,
    routeLongest: String,
    favoriteFish: String,
    favoriteLocation: String,
    mostCatchesDay: Number,
    recentCatches: Array
})

const page = usePage();
const user = computed(() => page.props.auth.user);
const firstName = computed(() => (user.value?.name ?? '').split(' ')[0] || 'Angler');
const limitReached = computed(() => !user.value?.subscribed && props.catchedCount >= 5);

const today = new Date().toLocaleDateString('de-AT', { weekday: 'long', day: 'numeric', month: 'long' });

const stats = computed(() => [
    { label: 'Fänge gesamt', value: props.catchedCount, hint: 'in deinem Fangbuch' },
    { label: 'Diesen Monat', value: props.catchedStatsMonthly?.total, hint: new Date().toLocaleDateString('de-AT', { month: 'long', year: 'numeric' }) },
    { label: 'Dieses Jahr', value: props.catchedStatsYearly?.total, hint: `Saison ${new Date().getFullYear()}` },
    { label: 'Längster Fang', value: props.longestCatch?.length ? `${props.longestCatch.length} cm` : null, hint: props.longestCatch?.fish?.name ?? 'noch kein Rekord' },
]);

const tips = [
    { title: 'Express-Eintrag', text: 'Nur Datum, Länge, Fisch und Gewässer – den Rest später.' },
    { title: 'Bis zu 3 Fotos', text: 'Das erste Foto wird zum Titelbild deines Fangs.' },
    { title: 'GPS-Spot', text: 'Tippe auf die Karte und finde den Spot später wieder.' },
];

onMounted(() => {
    sessionStorage.setItem('lastOrigin', window.location.href);
});
</script>
