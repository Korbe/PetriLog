<template>
    <PageWrapper :title="waters.name" :backTo="`/waters/${waters.state}`">

        <div class="w-full md:w-1/2 mx-auto">
            <img :src="waters.link" :alt="waters" class="w-full h-40 md:h-80 object-cover rounded-lg" />
            <div class="bg-white p-3 md:p-10">

                <p class="pt-5">{{ waters.desc }}</p>

                <p class="font-bold text-lg pt-5 pb-2">Diese Fische sind hier heimisch</p>
                <ul v-for="(fish, name) in waters.fish" :key="fish">
                    <Link :href="`/fish/${name}`">
                    <li class="flex items-center gap-5 p-3">
                        <img :src="fish.link" :alt="name" class="w-30 h-30 object-cover rounded-lg" />
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
import { onMounted } from 'vue';
defineProps({
    waters: Object
});

onMounted(() => {
    sessionStorage.setItem('lastOrigin', window.location.href);
});
</script>
