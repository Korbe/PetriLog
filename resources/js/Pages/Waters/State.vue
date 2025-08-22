<template>
    <PageWrapper :title="state.name" :backTo="route('waters.index')">

        <div class="w-full md:w-1/2 mx-auto">
            <img :src="state.wallpaper" :alt="state" class="w-full h-40 md:h-80 object-cover rounded-lg" />
            <div class="bg-white p-3 md:p-10 md:pt-5">
                <p class="pt-5">{{ state.desc }}</p>

                <p class="font-bold text-lg pt-5 pb-2">Seen</p>
                <ul v-for="(lake, name) in state.lakes" :key="lake">

                    <Link class="cursor-pointer" :href="route('waters.state.waters', { state: state.name, lake: name })">
                    <li class="flex items-center gap-5 p-3">
                        <img :src="lake.link" :alt="name" class="w-30 h-30 object-cover rounded-lg" />
                        <p class="font-bold">{{ name }}</p>
                    </li>
                    </Link>

                </ul>

                <p class="font-bold text-lg pt-5 pb-2">Flüße</p>
                <ul v-for="(river, name) in state.rivers" :key="river">
                    
                    <Link class="cursor-pointer" :href="route('waters.state.waters', { state: state.name, lake: name })">
                    <li class="flex items-center gap-5 p-3">
                        <img :src="river.link" :alt="name" class="w-30 h-30 object-cover rounded-lg" />
                        <p class="font-bold">{{ name }}</p>
                    </li>
                    </Link>

                </ul>
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
