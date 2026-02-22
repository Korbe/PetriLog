<template>
    <PageWrapper title="Bundesland Verwaltung" backTo="/admin">
        <div class="space-y-5">
            <div class="mt-8 mx-5 overflow-x-auto bg-white dark:bg-gray-800 rounded-lg">
                <div class="p-4">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead>
                            <tr>
                                <th scope="col"
                                    class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-400 sm:pl-0">
                                    Name
                                </th>
                                <th scope="col"
                                    class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-400 sm:pl-0">
                                    Anzeigen
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="state in states" :key="state.id"
                                class="hover:bg-gray-100 dark:hover:bg-gray-700">

                                <!-- Name klickbar -->
                                <td
                                    class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-gray-400 sm:pl-0">
                                    <Link :href="route('admin.state.edit', state.id)">
                                        {{ state.name }}
                                    </Link>
                                </td>

                                <!-- Anzeigen Button -->
                                <td
                                    class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-gray-400 sm:pl-0">
                                    <VButton :href="`/states/${state.id}`">anzeigen</VButton>
                                </td>

                            </tr>
                        </tbody>

                    </table>
                </div>
            </div>
        </div>
    </PageWrapper>
</template>

<script setup lang="ts">
import VButton from '@/components/VButton.vue';
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
import { Link } from '@inertiajs/vue3';

interface State {
    id: number;
    name: string;
}

const props = defineProps<{
    states: State[];
}>();
</script>