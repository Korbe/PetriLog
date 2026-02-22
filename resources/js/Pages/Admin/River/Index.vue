<template>
    <PageWrapper title="Fluss Verwaltung" backTo="/admin">

        <template v-slot:actions>
            <VButton href="/admin/river/create">hinzufügen</VButton>
        </template>

        <div class="space-y-5">

            <!-- Suchfeld -->
            <div class="px-5 mt-4">
                <input type="text" v-model="search" placeholder="Suche nach Flüssen..."
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200" />
            </div>

            <div class="mt-8 mx-5 overflow-x-auto bg-white dark:bg-gray-800 rounded-lg">
                <div class="p-4">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead>
                            <tr>
                                <th
                                    class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-400 sm:pl-0">
                                    Name
                                </th>
                                <th
                                    class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-400">
                                    Bundesländer
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="river in filteredRivers" :key="river.id"
                                class="hover:bg-gray-100 dark:hover:bg-gray-700">
                                <!-- Inertia Link statt @click -->
                                <td
                                    class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-gray-400 sm:pl-0">
                                    <Link :href="route('admin.river.edit', river.id)">
                                        {{ river.name }}
                                    </Link>
                                </td>

                                <td
                                    class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-700 dark:text-gray-300 space-x-2">
                                    <VButton v-for="state in river.states" :key="state.id"
                                        :href="`/states/${state.id}/rivers/${river.id}`">{{ state.name }}</VButton>
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
import { ref, computed } from 'vue';

interface State {
    id: number;
    name: string;
}

interface River {
    id: number;
    name: string;
    states: State[];
}

const props = defineProps<{
    rivers: River[];
}>();

const search = ref(''); // Suchbegriff

const filteredRivers = computed(() => {
    if (!search.value) return props.rivers;
    return props.rivers.filter(river =>
        river.name.toLowerCase().includes(search.value.toLowerCase())
    );
});
</script>