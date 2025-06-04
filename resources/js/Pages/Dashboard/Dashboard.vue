<template>
    <Layout :title="'Petri Heil ' + $page.props.auth.user.name">
        <template v-slot:actions>
        </template>

        <div class="space-y-5">
            <div class="flex space-x-5 w-full">

                <div class="flex flex-wrap lg:flex-nowrap w-full gap-5">
                    <div
                        class="w-full lg:w-1/3 bg-white flex flex-col text-center justify-center dark:bg-gray-800 shadow-xs rounded-lg p-5">
                        <h1 class="text-xl">Fisch gefangen?</h1>
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
    </Layout>
</template>
<script setup>
import Layout from '@/Layouts/Layout.vue';
import VButton from '@/components/VButton.vue';
import CatchedStatsMonthlyCard from './CatchedStatsMonthlyCard.vue';
import CatchedStatsYearlyCard from './CatchedStatsYearlyCard.vue';

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



</script>