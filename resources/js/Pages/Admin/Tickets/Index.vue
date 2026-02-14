<template>
    <PageWrapper title="Tickets" backTo="/admin">
        <div class="space-y-5">

            <div class="mt-8 mx-5 overflow-x-auto bg-white dark:bg-gray-800 rounded-lg">
                <div class="p-4">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead>
                            <tr>
                                <th scope="col"
                                    class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-400 sm:pl-0">
                                    ID</th>
                                <th scope="col"
                                    class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-400">
                                    Title
                                </th>
                                <th scope="col"
                                    class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-400">
                                    Category
                                </th>
                                <th scope="col"
                                    class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-400">
                                    User
                                </th>
                                <th scope="col"
                                    class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-400">
                                    Created
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">

                            <tr class="cursor-pointer hover:bg-gray-100 hover:dark:bg-gray-700"
                                @click="$inertia.get(ticket.url)" v-for="ticket in tickets" :key="ticket.id">
                                <td
                                    class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-gray-400 sm:pl-0">
                                    {{ ticket.id }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500  dark:text-gray-400">
                                    {{
                                        ticket.title }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500  dark:text-gray-400">
                                    {{
                                        ticket.category }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500  dark:text-gray-400">
                                    {{ ticket.user
                                    }}
                                    ({{ ticket.email }})
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500  dark:text-gray-400">
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