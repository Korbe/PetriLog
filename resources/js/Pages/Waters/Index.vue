<template>
    <PageWrapper title="Gewässer" :backTo="route('dashboard')">

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div v-for="(stateWaters, state) in states" :key="state" class="pb-4 ">

                <Link :href="route('waters.state', state)">
                <div class="flex flex-col items-center pb-10">
                    <img :src="stateWaters.link" :alt="state" class="w-44 h-44 object-fill rounded-lg" />
                    <h1 class="text-xl font-bold pt-3">{{ state }}</h1>
                    <span class="font-medium text-lg">
                        {{ Object.keys(stateWaters.lakes).length }} Seen und {{ Object.keys(stateWaters.rivers).length
                        }} Flüsse
                    </span>
                </div>
                </Link>

            </div>
        </div>

    </PageWrapper>
</template>
<script setup>
import PageWrapper from '@/Layouts/PageWrapper.vue';
import { onMounted } from 'vue';

defineProps({
    states: Object
});

onMounted(() => {
    sessionStorage.setItem('lastOrigin', window.location.href);
});

</script>