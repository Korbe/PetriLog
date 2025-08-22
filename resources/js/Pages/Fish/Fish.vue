<template>
    <PageWrapper :title="fish.name">
        
        <div class="w-full md:w-1/2 mx-auto">
            <img :src="fish.link" :alt="fish" class="w-full h-40 md:h-80 object-cover rounded-lg" />
            <div class="bg-white p-3 md:p-10">

                <p class="pt-5">{{ fish.desc }}</p>

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
