<template>
    <PageWrapper title="See Verwaltung" backTo="/admin">

        <template v-slot:actions>
            <VButton href="/admin/lake/create">hinzufügen</VButton>
        </template>

        <div class="space-y-5">

            <!-- Suchfeld -->
            <div class="px-5 mt-4">
                <input type="text" v-model="search" placeholder="Suche nach Seen..."
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200" />
            </div>

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
                                                Fische Anzahl</th>
                                            <th scope="col"
                                                class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-400 sm:pl-0">
                                                Show</th>

                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        <tr class="cursor-pointer hover:bg-gray-100 hover:dark:bg-gray-700"
                                            v-for="lake in filteredLakes" :key="lake.id">
                                            <td @click="goToEdit(lake.id)"
                                                class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-gray-400 sm:pl-0">
                                                {{ lake.name }}
                                            </td>
                                            <td
                                                class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-700 dark:text-gray-300">
                                                {{ lake.fish.length }}
                                            </td>
                                            <td
                                                class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-700 dark:text-gray-300 space-x-2">
                                                <VButton v-for="state in lake.states" :key="state.id"
                                                    :href="`/states/${state.id}/lakes/${lake.id}`">{{ state.name }}</VButton>
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
import { ref, computed } from 'vue';

interface State {
    id: number;
    name: string;
}

interface Lake {
    id: number;
    name: string;
    states: State[];
    fish: Array<object>;
}

const props = defineProps<{
    lakes: Lake[];
}>();

const search = ref(''); // Suchbegriff

// Computed für gefilterte Seen
const filteredLakes = computed(() => {
    if (!search.value) return props.lakes;
    return props.lakes.filter(lake =>
        lake.name.toLowerCase().includes(search.value.toLowerCase())
    );
});

function goToEdit(lakeId: number) {
    Inertia.visit(route('admin.lake.edit', lakeId), {
        preserveScroll: true,
        preserveState: true,
    });
}
</script>
