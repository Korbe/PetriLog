<template>
    <PageWrapper title="See Verwaltung" backTo="/admin">

        <template v-slot:actions>
            <VButton href="/admin/lake/create">hinzufügen</VButton>
        </template>


        <div class="space-y-5">

            <div class="flex space-x-5 w-full">

                <div class="flex flex-wrap lg:flex-nowrap w-full gap-5">

                    <div class="mt-8 mx-5 flow-root">
                        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                            <div
                                class="bg-white rounded-lg dark:bg-gray-800 inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                                <table class="relative min-w-full divide-y divide-gray-300">
                                    <thead>
                                        <tr>
                                            <th scope="col"
                                                class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-400 sm:pl-0">
                                                Name</th>
                                            <th scope="col"
                                                class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-400 sm:pl-0">
                                                Bundesland</th>
                                            <th scope="col"
                                                class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-400 sm:pl-0">
                                                Fische Anzahl</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">

                                        <tr class="cursor-pointer hover:bg-gray-100 hover:dark:bg-gray-700"
                                            v-for="lake in lakes" :key="lake.id" @click="goToEdit(lake.id)">
                                            <td
                                                class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-gray-400 sm:pl-0">
                                                {{ lake.name }}
                                            </td>
                                            <td
                                                class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-700 dark:text-gray-300">
                                                <span v-for="state in lake.states" :key="state.id"
                                                    class="inline-block mr-2 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-2 py-1 rounded text-xs">
                                                    {{ state.name }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>


                </div>

            </div>
        </div>
    </PageWrapper>
</template>
<script setup lang="ts">
import VButton from '@/components/VButton.vue';
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
import { Inertia } from '@inertiajs/inertia';

interface lake {
    id: number;
    name: string;
    state: object;
    fish: Array<object>;
}

const props = defineProps<{
    lakes: lake[];
}>()

function goToEdit(lakeId) {
    Inertia.visit(route('admin.lake.edit', lakeId), {
        preserveScroll: true,
        preserveState: true,
    });
}
</script>