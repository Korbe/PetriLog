<template>
    <PageWrapper :title="fish.name">
        
        <div class="w-full md:w-1/2 mx-auto">
            <img :src="fish.link" :alt="fish" class="w-full h-40 md:h-80 object-cover rounded-t-lg" />
            <div class="bg-white dark:bg-gray-800 p-3 md:p-10 rounded-b-lg">

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
