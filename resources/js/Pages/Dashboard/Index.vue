<template>
    <PageWrapper :title="'Petri Heil ' + $page.props.auth.user.name" hideBackButton>
        <div class="space-y-2">

            <EmailVerificationBanner />

            <PwaBanner />

            <!-- Empty state and stats cards -->
            <div v-if="catchedCount == 0" class="mt-20">
                <img src="images/fisher.webp" alt="Fischer" class="mx-auto mb-1 w-auto h-64" />

                <div
                    class="mx-auto w-full lg:w-1/3 bg-white dark:bg-gray-800 shadow-md hover:shadow-xl rounded-lg p-8 flex transition-all duration-200 transform hover:scale-105 flex-col items-center text-center">
                    <h1 class="text-2xl font-bold mb-2 text-gray-800 dark:text-gray-100">Petri Heil
                        {{ $page.props.auth.user.name }}, dein Fangbuch ist
                        noch
                        leer!</h1>
                    <p class="text-gray-700 dark:text-gray-300 mb-5">der nächste Biss kommt bestimmt 🎣</p>

                    <VButton class="px-6 py-3 text-lg rounded-lg font-semibold" :href="createUrl">
                        Ersten Fang eintragen
                    </VButton>
                </div>
            </div>

            <div v-else>

                <div class="flex space-x-5 w-full">

                    <div class="flex flex-wrap lg:flex-nowrap w-full gap-5">

                        <div
                            class="w-full lg:w-1/3 bg-white dark:bg-gray-800 shadow-md hover:shadow-xl rounded-lg p-8 flex transition-all duration-200 transform hover:scale-105 flex-col items-center text-center">

                            <DocumentPlusIcon class="h-12 w-12 text-blue-500 mb-4" />
                            <h1 class="text-2xl font-bold mb-2 text-gray-800 dark:text-gray-100">Fisch gefangen?</h1>
                            <p class="text-gray-700 dark:text-gray-300 mb-5">Petri Heil! Trage deinen Fang jetzt ein.
                            </p>

                            <VButton class="px-6 py-3 text-lg rounded-lg font-semibold" :href="createUrl">
                                Eintragen
                            </VButton>
                        </div>

                        <div
                            class="w-full lg:w-1/3 bg-white dark:bg-gray-800 shadow-md hover:shadow-xl rounded-lg p-8 transition-all duration-200 transform hover:scale-105">
                            <CatchedStatsMonthlyCard :data="catchedStatsMonthly" />
                        </div>

                        <div
                            class="w-full lg:w-1/3 bg-white dark:bg-gray-800 shadow-md hover:shadow-xl rounded-lg p-8 transition-all duration-200 transform hover:scale-105">
                            <CatchedStatsYearlyCard :data="catchedStatsYearly" />
                        </div>

                    </div>

                </div>

                <div class="flex space-x-5 w-full">

                    <div class="flex flex-wrap lg:flex-nowrap w-full gap-5 py-5">

                        <LongestCatchCard v-if="longestCatch" :catched="longestCatch" :route="routeLongest" />

                        <HeaviestCatchCard v-if="heaviestCatch" :catched="heaviestCatch" :route="routeHeaviest" />

                        <!-- <HighlightCard :favoriteFish="favoriteFish" :favoriteLocation="favoriteLocation"
                            :mostCatchesDay="mostCatchesDay" />-->

                        <RecentCatches :recentCatches="recentCatches" /> 

                    </div>

                </div>

                <div class="flex justify-center mt-10">
                    <p>Im Dashboard fehlt noch was? Schreib uns eine Mail an <a class="underline"
                            href="mailto:info@petrilog.com">info@petrilog.com</a></p>
                </div>


            </div>
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
import LongestCatchCard from './partials/LongestCatchCard.vue';
import HeaviestCatchCard from './partials/HeaviestCatchCard.vue';
import { DocumentPlusIcon } from '@heroicons/vue/24/solid';
import PwaBanner from './partials/PwaBanner.vue';
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

onMounted(() => {
    sessionStorage.setItem('lastOrigin', window.location.href);
});




</script>