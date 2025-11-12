<template>
    <PageWrapper title="Gewässer" :backTo="`/dashboard`">

        <div
            class="flex items-center gap-2 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 text-sm">
            <span>Diese Liste ist unvollständig und wird laufend ergänzt.</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-10">
            <div v-for="(stateWaters, state) in states" :key="state" class="pb-4 ">

                <Link :href="`/waters/${state}`">
                <div class="flex flex-col items-center pb-10">
                    <img :src="stateWaters.link" :alt="state" class="w-44 h-44 object-fill rounded-lg" />
                    <h1 class="text-xl font-bold pt-3">{{ state }}</h1>
                    <span class="font-medium text-lg">
                        {{ Object.keys(stateWaters.lakes).length }}
                        {{ Object.keys(stateWaters.lakes).length === 1 ? 'See' : 'Seen' }}
                        und
                        {{ Object.keys(stateWaters.rivers).length }}
                        {{ Object.keys(stateWaters.rivers).length === 1 ? 'Fluss' : 'Flüsse' }}
                    </span>
                </div>
                </Link>

            </div>
        </div>

    </PageWrapper>
</template>
<script setup>
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
import { onMounted } from 'vue';

defineProps({
    states: Object
});

onMounted(() => {
    sessionStorage.setItem('lastOrigin', window.location.href);
});

</script>