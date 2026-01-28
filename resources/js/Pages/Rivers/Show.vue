<template>
    <PageWrapper :title="river.name" :backTo="`/states/${state_id}`">


        <div class="w-full md:w-1/2 mx-auto">
            <div class="bg-white dark:bg-gray-800 p-3 md:p-10 rounded-b-lg">

                <h1 class="text-center font-bold text-4xl mb-2">{{ river.name }}</h1>

                <p class="pt-5" v-html="river.desc"></p>

                <h2 class="font-bold pt-5" v-if="lake.hint">Tipp:</h2>
                <p v-html="lake.hint"></p>

                <h2 class="font-bold pt-5" v-if="lake.fishing_rights">Fischereirechte:</h2>
                <p class="pt-5" v-html="lake.fishing_rights"></p>

                <h2 class="font-bold pt-5" v-if="lake.ticket_sales">Kartenverkauf:</h2>
                <p class="pt-5" v-html="lake.ticket_sales"></p>

                <div v-if="river.fish && river.fish.length > 0">
                    <p class="font-bold text-lg pt-5 pb-2">Diese Fische sind hier heimisch</p>
                    <ul>
                        <li v-for="fish in river.fish" :key="fish.id"
                            class="hover:bg-gray-100 dark:hover:bg-gray-700 rounded">
                            <Link :href="`/fish/${fish.id}`"
                                class="flex items-center justify-between gap-2 p-2 text-blue-500 hover:text-blue-700">
                            <span>{{ fish.name }}</span>
                            <ArrowRightIcon class="w-5 h-5" />
                            </Link>
                        </li>
                    </ul>
                </div>

            </div>
        </div>

    </PageWrapper>
</template>
<script setup>
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
import { ArrowRightIcon } from '@heroicons/vue/24/solid';
import { onMounted } from 'vue';
defineProps({
    state_id: String,
    river: Object
});

onMounted(() => {
    sessionStorage.setItem('lastOrigin', window.location.href);
});
</script>
