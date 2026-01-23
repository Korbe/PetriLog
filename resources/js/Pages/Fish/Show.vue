<template>
    <PageWrapper :title="fish.name">

        <div class="w-full md:w-1/2 mx-auto">
            <img :src="fish.media[0]?.url || '/images/fish-default.jpg'" :alt="fish.name"
                class="mb-5 w-full h-40 md:h-80 object-contain rounded-t-lg" />

            <div class="bg-white dark:bg-gray-800 p-3 md:p-10 rounded-b-lg">
                <h1 class="text-center font-bold text-4xl mb-2">{{ fish.name }}</h1>
                <p class="pt-5" v-html="fish.desc"></p>
            </div>
        </div>

    </PageWrapper>
</template>

<script setup>
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';

defineProps({
    fish: Object
});
</script>
