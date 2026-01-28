<template>
    <PageWrapper :title="'Petri Heil ' + $page.props.auth.user.name" hideBackButton>
        <div class="space-y-5">

            <EmailVerificationBanner />

            <PwaBanner />

            <div class="flex space-x-5 w-full">

                <div class="flex flex-wrap lg:flex-nowrap w-full gap-5">

                    <div
                        class="w-full lg:w-1/3 bg-white dark:bg-gray-800 shadow-md hover:shadow-xl rounded-lg p-8 flex transition-all duration-200 transform hover:scale-105 flex-col items-center text-center">

                        <DocumentPlusIcon class="h-12 w-12 text-blue-500 mb-4" />
                        <h1 class="text-2xl font-bold mb-2 text-gray-800 dark:text-gray-100">Fisch gefangen?</h1>
                        <p class="text-gray-700 dark:text-gray-300 mb-5">Gratulation! Trage deinen Fang jetzt ein.</p>

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

                <div class="flex flex-wrap lg:flex-nowrap w-full gap-5">

                    <div
                        class="w-full lg:w-1/2 bg-white dark:bg-gray-800 shadow-md hover:shadow-xl rounded-lg p-8 transition-all duration-200 transform hover:scale-105 flex flex-col">
                        <LongestCatchCard :catched="longestCatch" :route="routeLongest" />
                    </div>

                    <div
                        class="w-full lg:w-1/2 bg-white dark:bg-gray-800 shadow-md hover:shadow-xl rounded-lg p-8 transition-all duration-200 transform hover:scale-105 flex flex-col">
                        <HeaviestCatchCard :catched="heaviestCatch" :route="routeHeaviest" />
                    </div>
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

const props = defineProps({
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
    routeLongest: String
})


const page = usePage()

// User aus den Inertia-Props
const user = computed(() => page.props.auth.user)

onMounted(() => {
    sessionStorage.setItem('lastOrigin', window.location.href);
});


</script>