<template>
    <PageWrapper title="Verein Verwaltung" backTo="/admin">

        <template v-slot:actions>
            <VButton href="/admin/association/create">hinzufügen</VButton>
        </template>

        <div class="space-y-5">

            <!-- Suchfeld -->
            <div class="ui-search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="m21 21-4.3-4.3M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"/></svg>
                <input type="text" v-model="search" placeholder="Suche nach Vereinen..."
                    class="form-input w-full rounded-full!" />
            </div>

            <div class="ui-card overflow-x-auto">
                <div>
                    <table class="ui-table min-w-full">
                        <thead>
                            <tr>
                                <th scope="col">
                                    Name
                                </th>
                                <th scope="col">
                                    Bundesland
                                </th>
                                <th scope="col">
                                    Anzeigen
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="assoc in filteredAssociation" :key="assoc.id"
                               >

                                <!-- Name -->
                                <td
                                    class="ui-table-primary whitespace-nowrap">
                                    <Link :href="route('admin.association.edit', assoc.id)">
                                        {{ assoc.name }}
                                    </Link>
                                </td>

                                <!-- Bundesland -->
                                <td class="whitespace-nowrap">
                                    <Link :href="route('admin.association.edit', assoc.id)">
                                        <span
                                            class="ui-badge mr-1.5">
                                            {{ assoc.state.name }}
                                        </span>
                                    </Link>
                                </td>

                                <!-- Anzeigen Button -->
                                <td
                                    class="ui-table-primary whitespace-nowrap">
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
import { Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

interface State {
    id: number;
    name: string;
}

interface Association {
    id: number;
    name: string;
    desc: string;
    state: State;
}

const props = defineProps<{
    associations: Association[];
}>();

const search = ref('');

const filteredAssociation = computed(() => {
    if (!search.value) return props.associations;
    return props.associations.filter(a =>
        a.name.toLowerCase().includes(search.value.toLowerCase())
    );
});
</script>