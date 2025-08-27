<template>
    <PageWrapper :title="'Petri Heil ' + $page.props.auth.user.name" hideBackButton>
        <div class="space-y-5">

            <EmailVerificationBanner />

            <TrialEndedBanner v-if="!user.subscribed && !isOnTrial" />

            <TrialBanner />

            <PwaInstallBanner />

            <div class="flex space-x-5 w-full">

                <div class="flex flex-wrap lg:flex-nowrap w-full gap-5">

                    <div
                        class="w-full lg:w-1/3 bg-white flex flex-col text-center justify-center dark:bg-gray-800 shadow-xs rounded-lg p-5">
                        <h1 class="text-xl font-medium">Fisch gefangen?</h1>
                        <p>Gratulation, trage deine Fang jetzt ein</p>
                        <VButton class="m-5" :href="route('catched.create')">Eintragen</VButton>
                    </div>

                    <div class="w-full lg:w-1/3 flex flex-col bg-white dark:bg-gray-800 shadow-xs rounded-lg p-5">
                        <CatchedStatsMonthlyCard :data="catchedStatsMonthly" />
                    </div>

                    <div class="w-full lg:w-1/3 flex flex-col bg-white dark:bg-gray-800 shadow-xs rounded-lg p-5">
                        <CatchedStatsYearlyCard :data="catchedStatsYearly" />
                    </div>
                </div>

            </div>
        </div>
    </PageWrapper>
</template>
<script setup>
import VButton from '@/components/VButton.vue';
import CatchedStatsMonthlyCard from './CatchedStatsMonthlyCard.vue';
import CatchedStatsYearlyCard from './CatchedStatsYearlyCard.vue';
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
import { computed, onMounted } from 'vue';
import PwaInstallBanner from '@/components/PwaInstallBanner.vue';
import TrialBanner from '@/components/TrialBanner.vue';
import TrialEndedBanner from '@/components/TrialEndedBanner.vue';
import { usePage } from '@inertiajs/vue3';
import EmailVerificationBanner from '@/components/EmailVerificationBanner.vue';

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
})


const page = usePage()

// User aus den Inertia-Props
const user = computed(() => page.props.auth.user)

// Trial-Status
const isOnTrial = computed(() => user.value?.onTrial)

onMounted(() => {
    sessionStorage.setItem('lastOrigin', window.location.href);
});


</script>