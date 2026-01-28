<template>
    <PageWrapper title="Fisch Verwaltung" backTo="/admin">

        <template v-slot:actions>
            <VButton href="/admin/fish/create">hinzufügen</VButton>
        </template>

        <div class="space-y-5">

            <!-- Suchfeld -->
            <div class="px-5 mt-4">
                <input type="text" v-model="search" placeholder="Suche nach Fischen..."
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
                                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-400">
                                                Verwendet in Fluss
                                            </th>
                                            <th scope="col"
                                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-400">
                                                Verwendet in See
                                            </th>
                                               <th scope="col"
                                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-400">
                                                Anzeigen
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        <tr class="cursor-pointer hover:bg-gray-100 hover:dark:bg-gray-700"
                                            v-for="fish in filteredFish" :key="fish.id">
                                            <td @click="goToEdit(fish.id)"
                                                class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-gray-400 sm:pl-0">
                                                {{ fish.name }}
                                            </td>
                                            <td @click="goToEdit(fish.id)"
                                                class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                                {{ fish.rivers_count }}
                                            </td>
                                            <td @click="goToEdit(fish.id)"
                                                class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                                {{ fish.lakes_count }}
                                            </td>
                                            <td
                                                class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-gray-400 sm:pl-0">
                                                <VButton :href="`/fish/${fish.id}`">anzeigen</VButton>
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

interface Fish {
    id: number;
    name: string;
    desc: string;
    lakes_count: number;
    rivers_count: number;
}

const props = defineProps<{
    fishs: Fish[];
}>();

const search = ref(''); // Suchbegriff

// Computed für gefilterte Fische
const filteredFish = computed(() => {
    if (!search.value) return props.fishs;
    return props.fishs.filter(f =>
        f.name.toLowerCase().includes(search.value.toLowerCase())
    );
});

function goToEdit(fishId: number) {
    Inertia.visit(route('admin.fish.edit', fishId), {
        preserveScroll: true,
        preserveState: true,
    });
}
</script>
