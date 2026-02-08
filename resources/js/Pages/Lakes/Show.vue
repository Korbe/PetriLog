<template>
    <PageWrapper :title="lake.name" :backTo="`/states/${state_id}`">


        <div class="w-full md:w-1/2 mx-auto">
            <div class="bg-white dark:bg-gray-800 p-3 md:p-10 rounded-b-lg">

                <h1 class="text-center font-bold text-4xl mb-2">{{ lake.name }}</h1>

                <div v-if="isAdmin" class="flex justify-center mb-6">
                    <VButton :href="`/admin/lake/${lake.id}/edit`" size="sm">
                        See bearbeiten
                    </VButton>
                </div>

                <p class="pt-5" v-html="lake.desc"></p>

                <h2 class="font-bold pt-5" v-if="lake.hint">Tipp:</h2>
                <p v-html="lake.hint"></p>

                <h2 class="font-bold pt-5" v-if="lake.fishing_rights">Fischereirechte:</h2>
                <p class="pt-5" v-html="lake.fishing_rights"></p>

                <h2 class="font-bold pt-5" v-if="lake.ticket_sales">Kartenverkauf:</h2>
                <p class="pt-5" v-html="lake.ticket_sales"></p>

                <ExpandableList :items="lake.fish" title="Diese Fische sind hier heimisch" base-url="/fish" />

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
    lake: Object
});

const page = usePage();

const isAdmin = computed(() => {
    return page.props.auth?.user?.is_admin === true;
});

onMounted(() => {
    sessionStorage.setItem('lastOrigin', window.location.href);
});
</script>
