<template>
    <PageWrapper :title="state.name" :backTo="`/states`">

        <div class="w-full md:w-1/2 mx-auto">

            <div class="bg-white dark:bg-gray-800 p-3 md:p-10 md:pt-5 rounded-b-lg">

                <h1 class="text-center font-bold text-4xl mb-2">{{ state.name }}</h1>

                <div v-if="isAdmin" class="flex justify-center mb-6">
                    <VButton :href="`/admin/state/${state.id}/edit`" size="sm">
                        Bundesland bearbeiten
                    </VButton>
                </div>

                <p class="pt-5" v-html="state.desc"></p>

                <!-- Vereine -->
                <ExpandableSlotList title="Vereine" :items="state.associations">
                    <template v-slot="{ item }">
                        <Link :href="`/states/${state.id}/associations/${item.id}`"
                            class="flex items-center justify-between gap-2 p-2 text-blue-500 hover:text-blue-700 rounded">
                        <span>{{ item.name }}</span>
                        <ArrowRightIcon class="w-5 h-5" />
                        </Link>

                        <p v-if="item.desc" class="pl-2 pt-1 text-gray-700 dark:text-gray-300 mb-5">
                            {{ truncateDesc(item.desc) }}
                        </p>
                    </template>
                </ExpandableSlotList>

                <!-- Seen -->
                <ExpandableList :items="state.lakes" title="Seen" :base-url="`/states/${state.id}/lakes`" />

                <!-- Flüsse -->
                <ExpandableList :items="state.rivers" title="Flüsse" :base-url="`/states/${state.id}/rivers`" />
            </div>
        </div>

    </PageWrapper>
</template>

<script setup>
import ExpandableList from '@/components/ExpandableList.vue';
import ExpandableSlotList from '@/components/ExpandableSlotList.vue';
import VButton from '@/components/VButton.vue';
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
import { ArrowRightIcon } from '@heroicons/vue/24/solid';
import { usePage } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';

defineProps({
    state: Object
});

const page = usePage();

const isAdmin = computed(() => {
    return page.props.auth?.user?.is_admin === true;
});

const truncateDesc = (desc) => {
    if (!desc) return '';
    const plainText = desc.replace(/<[^>]*>/g, ''); // Entferne HTML-Tags für sichere Kürzung
    return plainText.length > 100 ? plainText.substring(0, 100) + '...' : plainText;
};

onMounted(() => {
    sessionStorage.setItem('lastOrigin', window.location.href);
});
</script>
