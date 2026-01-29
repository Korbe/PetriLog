<template>
    <PageWrapper :title="river.name" :backTo="`/states/${state_id}`">


        <div class="w-full md:w-1/2 mx-auto">
            <div class="bg-white dark:bg-gray-800 p-3 md:p-10 rounded-b-lg">

                <h1 class="text-center font-bold text-4xl mb-2">{{ river.name }}</h1>

                <div v-if="isAdmin" class="flex justify-center mb-6">
                    <VButton :href="`/admin/river/${river.id}/edit`" size="sm">
                        Fluss bearbeiten
                    </VButton>
                </div>

                <p class="pt-5" v-html="river.desc"></p>

                <h2 class="font-bold pt-5" v-if="river.hint">Tipp:</h2>
                <p v-html="river.hint"></p>

                <h2 class="font-bold pt-5" v-if="river.fishing_rights">Fischereirechte:</h2>
                <p class="pt-5" v-html="river.fishing_rights"></p>
                <h2 class="font-bold pt-5" v-if="river.ticket_sales">Kartenverkauf:</h2>
                <p class="pt-5" v-html="river.ticket_sales"></p>

                <ExpandableList :items="river.fish" title="Diese Fische sind hier heimisch" base-url="/fish" />

            </div>
        </div>

    </PageWrapper>
</template>
<script setup>
import ExpandableList from '@/components/ExpandableList.vue';
import VButton from '@/components/VButton.vue';
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
import { usePage } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';

defineProps({
    state_id: String,
    river: Object
});

const page = usePage();

const isAdmin = computed(() => {
    return page.props.auth?.user?.isAdmin === true;
});

onMounted(() => {
    sessionStorage.setItem('lastOrigin', window.location.href);
});
</script>
