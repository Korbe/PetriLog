<template>
    <PageWrapper title="Fisch Verwaltung" backTo="/admin">

        <template v-slot:actions>
            <VButton href="/admin/fish/create">hinzufügen</VButton>
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
                                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-400">
                                                Verwendet in Fluss
                                            </th>
                                            <th scope="col"
                                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-400">
                                                Verwendet in See
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">

                                        <tr class="cursor-pointer hover:bg-gray-100 hover:dark:bg-gray-700"
                                            v-for="fish in fishs" :key="fish.id" @click="goToEdit(fish.id)">
                                            <td
                                                class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-gray-400 sm:pl-0">
                                                {{ fish.name }}</td>
                                            <td
                                                class="whitespace-nowrap px-3 py-4 text-sm text-gray-500  dark:text-gray-400">
                                                {{
                                                    fish.rivers_count }}
                                            </td>
                                            <td
                                                class="whitespace-nowrap px-3 py-4 text-sm text-gray-500  dark:text-gray-400">
                                                {{
                                                    fish.lakes_count }}
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

interface Fish {
    id: number;
    name: string;
    desc: string;
    lakes_count: number,
    rivers_count: number
}

const props = defineProps<{
    fishs: Fish[];
}>()

function goToEdit(fishId) {
  Inertia.visit(route('admin.fish.edit', fishId), {
    preserveScroll: true,
    preserveState: true,
  });
}
</script>