<template>
    <PageWrapper title="Tickets" backTo="/admin">
        <div class="space-y-5">

            <div class="ui-card overflow-x-auto">
                <div>
                    <table class="ui-table min-w-full">
                        <thead>
                            <tr>
                                <th scope="col">
                                    ID</th>
                                <th scope="col">
                                    Title
                                </th>
                                <th scope="col">
                                    Category
                                </th>
                                <th scope="col">
                                    User
                                </th>
                                <th scope="col">
                                    Created
                                </th>
                            </tr>
                        </thead>
                        <tbody>

                            <tr class="cursor-pointer"
                                @click="$inertia.get(ticket.url)" v-for="ticket in tickets" :key="ticket.id">
                                <td
                                    class="ui-table-primary whitespace-nowrap">
                                    {{ ticket.id }}</td>
                                <td class="whitespace-nowrap">
                                    {{
                                        ticket.title }}
                                </td>
                                <td class="whitespace-nowrap">
                                    {{
                                        ticket.category }}
                                </td>
                                <td class="whitespace-nowrap">
                                    {{ ticket.user
                                    }}
                                    ({{ ticket.email }})
                                </td>
                                <td class="whitespace-nowrap">
                                    {{
                                        ticket.created_at }}
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
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';

interface Ticket {
    id: number;
    title: string;
    category: string;
    user: string;
    email: string;
    created_at: string;
    url: string;
}

const props = defineProps<{
    tickets: Ticket[];
}>()
</script>