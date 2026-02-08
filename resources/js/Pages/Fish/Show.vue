<template>
    <PageWrapper :title="fish.name">

        <div class="w-full md:w-1/2 mx-auto">
            <img :src="fish.media[0]?.url || '/images/fish-default.png'" :alt="fish.name"
                class="mb-5 w-full h-40 md:h-80 object-contain rounded-t-lg" />

            <div class="bg-white dark:bg-gray-800 p-3 md:p-10 rounded-b-lg">
                <h1 class="text-center font-bold text-4xl mb-2">{{ fish.name }}</h1>

                <div v-if="isAdmin" class="flex justify-center mb-6">
                    <VButton :href="`/admin/fish/${fish.id}/edit`" size="sm">
                        Fisch bearbeiten
                    </VButton>
                </div>

                <p class="pt-5" v-html="fish.desc"></p>
            </div>
        </div>

    </PageWrapper>
</template>

<script setup>
import VButton from '@/components/VButton.vue';
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
import { usePage } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';

defineProps({
    fish: Object
});

const page = usePage();

const isAdmin = computed(() => {
    return page.props.auth?.user?.is_admin === true;
});

onMounted(() => {
    sessionStorage.setItem('lastOrigin', window.location.href);
});
</script>
