<template>
    <PageWrapper :title="state.name" :backTo="`/waters`">

        <div class="w-full md:w-1/2 mx-auto">
            <img :src="state.wallpaper" :alt="state" class="w-full h-40 md:h-80 object-cover rounded-t-lg" />
            <div class="bg-white dark:bg-gray-800 p-3 md:p-10 md:pt-5 rounded-b-lg">
                <p class="pt-5">{{ state.desc }}</p>

                <div v-if="state.lakes && Object.keys(state.lakes).length > 0">
                    <p class="font-bold text-lg pt-5 pb-2">Seen</p>
                    <ul v-for="(lake, name) in state.lakes" :key="lake">
                        <Link class="cursor-pointer" :href="`/waters/${state.name}/${name}`">
                        <li class="flex items-center gap-5 p-3">
                            <img :src="lake.link" :alt="name" class="w-30 h-30 object-cover rounded-lg" />
                            <p class="font-bold">{{ name }}</p>
                        </li>
                        </Link>
                    </ul>
                </div>

                <div v-if="state.rivers && Object.keys(state.rivers).length > 0">
                    <p class="font-bold text-lg pt-5 pb-2">Flüsse</p>
                    <ul v-for="(river, name) in state.rivers" :key="river">
                        <Link class="cursor-pointer" :href="`/waters/${state.name}/${name}`">
                        <li class="flex items-center gap-5 p-3">
                            <img :src="river.link" :alt="name" class="w-30 h-30 object-cover rounded-lg" />
                            <p class="font-bold">{{ name }}</p>
                        </li>
                        </Link>
                    </ul>
                </div>
            </div>
        </div>

    </PageWrapper>
</template>
<script setup>
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
defineProps({
    state: Object
});
</script>
