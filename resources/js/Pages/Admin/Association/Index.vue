<template>
    <PageWrapper title="Verein Verwaltung" backTo="/admin">

        <template v-slot:actions>
            <VButton href="/admin/association/create">hinzufügen</VButton>
        </template>

        <div class="space-y-5">

            <!-- Suchfeld -->
            <div class="px-5 mt-4">
                <input type="text" v-model="search" placeholder="Suche nach Seen..."
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200" />
            </div>


            <div class="mt-8 mx-5 overflow-x-auto bg-white dark:bg-gray-800 rounded-lg">
                <div class="p-4">
                    <table class="min-w-full divide-y divide-gray-300">
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
                                    Anzeigen</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr class="cursor-pointer hover:bg-gray-100 hover:dark:bg-gray-700"
                                v-for="assoc in filteredAssociation" :key="assoc.id">
                                <td @click="goToEdit(assoc.id)"
                                    class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-gray-400 sm:pl-0">
                                    {{ assoc.name }}
                                </td>
                                <td @click="goToEdit(assoc.id)"
                                    class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-700 dark:text-gray-300">
                                    <span
                                        class="inline-block mr-2 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-2 py-1 rounded text-xs">
                                        {{ assoc.state.name }}
                                    </span>
                                </td>
                                <td
                                    class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-gray-400 sm:pl-0">
                                    <VButton :href="`/states/${assoc.state.id}/associations/${assoc.id}`">anzeigen
                                    </VButton>
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
import { Inertia } from '@inertiajs/inertia';
import { ref, computed } from 'vue';

interface State {
    id: number;
    name: string;
}

interface Association {
    id: number;
    name: string;
    desc: string;
    state: State
}

const props = defineProps<{
    associations: Association[];
}>();

const search = ref(''); // Suchbegriff

// Computed für gefilterte Seen
const filteredAssociation = computed(() => {
    if (!search.value) return props.associations;
    return props.associations.filter(association =>
        association.name.toLowerCase().includes(search.value.toLowerCase())
    );
});

function goToEdit(associationId: number) {
    Inertia.visit(route('admin.association.edit', associationId), {
        preserveScroll: true,
        preserveState: true,
    });
}
</script>
