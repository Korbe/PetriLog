<template>
    <PageWrapper :title="state.name" :backTo="`/states`">

        <div class="w-full md:w-1/2 mx-auto">

            <div class="bg-white dark:bg-gray-800 p-3 md:p-10 md:pt-5 rounded-b-lg">

                <h1 class="text-center font-bold text-4xl mb-2">{{ state.name }}</h1>

                <p class="pt-5" v-html="state.desc"></p>

                <!-- Vereine -->
                <div v-if="state.associations && state.associations.length > 0">
                    <p class="font-bold text-lg pt-5 pb-2">Vereine</p>
                    <ul>
                        <li v-for="assoc in state.associations" :key="assoc.id"
                            class="hover:bg-gray-100 dark:hover:bg-gray-700 rounded">

                            <Link :href="`/states/${state.id}/associations/${assoc.id}`"
                                class="flex items-center justify-between gap-2 p-2 text-blue-500 hover:text-blue-700 rounded">
                                <span>{{ assoc.name }}</span>
                                <ArrowRightIcon class="w-5 h-5" />
                            </Link>
                            <!-- Beschreibung darunter -->
                            <p v-if="assoc.desc" class="pl-2 pt-1 text-gray-700 dark:text-gray-300 mb-5" v-text="truncateDesc(assoc.desc)"></p>
                        </li>
                    </ul>
                </div>

                <!-- Seen -->
                <div v-if="state.lakes && state.lakes.length > 0">
                    <p class="font-bold text-lg pt-5 pb-2">Seen</p>
                    <ul>
                        <li v-for="lake in state.lakes" :key="lake.id"
                            class="hover:bg-gray-100 dark:hover:bg-gray-700 rounded">
                            <Link :href="`/states/${state.id}/lakes/${lake.id}`"
                                class="flex items-center justify-between gap-2 p-2 text-blue-500 hover:text-blue-700">
                            <span>{{ lake.name }}</span>
                            <ArrowRightIcon class="w-5 h-5" />
                            </Link>
                        </li>
                    </ul>
                </div>

                <!-- Flüsse -->
                <div v-if="state.rivers && state.rivers.length > 0">
                    <p class="font-bold text-lg pt-5 pb-2">Flüsse</p>
                    <ul>
                        <li v-for="river in state.rivers" :key="river.id"
                            class="hover:bg-gray-100 dark:hover:bg-gray-700 rounded">
                            <Link :href="`/states/${state.id}/rivers/${river.id}`"
                                class="flex items-center justify-between gap-2 p-2 text-blue-500 hover:text-blue-700">
                            <span>{{ river.name }}</span>
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
import { ArrowRightIcon, ArrowTopRightOnSquareIcon } from '@heroicons/vue/24/solid';

defineProps({
    state: Object
});

const truncateDesc = (desc) => {
  if (!desc) return '';
  const plainText = desc.replace(/<[^>]*>/g, ''); // Entferne HTML-Tags für sichere Kürzung
  return plainText.length > 100 ? plainText.substring(0, 100) + '...' : plainText;
};
</script>
