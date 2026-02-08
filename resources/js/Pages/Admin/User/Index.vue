<template>
    <PageWrapper title="User Verwaltung" backTo="/admin">

        <div class="space-y-5">

            <!-- Suche -->
            <div class="px-5 mt-4">
                <input v-model="search" type="text" placeholder="Suche nach Name oder E-Mail..." class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2
                           focus:outline-none focus:ring-2 focus:ring-blue-500
                           dark:bg-gray-700 dark:text-gray-200" />
            </div>

            <div class="mt-8 mx-5 overflow-x-auto bg-white dark:bg-gray-800 rounded-lg">
                <div class="p-4">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead>
                            <tr>
                                <th
                                    class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-400">
                                    ID</th>
                                <th
                                    class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-400">
                                    Name</th>
                                <th
                                    class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-400">
                                    E-Mail</th>

                                <th
                                    class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-400">
                                    Registriert am</th>
                                <th
                                    class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-400">
                                    Bundesland</th>
                                <th
                                    class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-400">
                                    Abonniert</th>
                                <th
                                    class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-400">
                                    Läuft ab am</th>
                                <th
                                    class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-400">
                                    Gekündigt/nie abonniert</th>
                                <th
                                    class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-400">
                                    Admin</th>
                                <th
                                    class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-400">
                                    Aktionen</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="user in filteredUsers" :key="user.id"
                                class="hover:bg-gray-100 dark:hover:bg-gray-700">
                                <td
                                    class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-gray-400">
                                    {{ user.id }}</td>
                                <td
                                    class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-gray-400">
                                    {{ user.name }}</td>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-700 dark:text-gray-300">{{
                                    user.email }}</td>

                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-700 dark:text-gray-300">
                                    {{ new Date(user.created_at).toLocaleDateString() }}
                                </td>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-700 dark:text-gray-300">
                                    {{ user.state }}
                                </td>

                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-700 dark:text-gray-300">
                                    <CheckIcon v-if="user.subscribed" class="h-5 w-5 text-green-600" />
                                    <XMarkIcon v-else class="h-5 w-5 text-red-500" />
                                </td>

                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-700 dark:text-gray-300">
                                    {{ new Date(user.subscription_expires_at).toLocaleDateString() }}
                                </td>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-700 dark:text-gray-300">
                                    <CheckIcon v-if="user.canceled" class="h-5 w-5 text-green-600" />
                                    <XMarkIcon v-else class="h-5 w-5 text-red-500" />
                                </td>

                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-700 dark:text-gray-300">
                                    <CheckIcon v-if="user.is_admin" class="h-5 w-5 text-green-600" />
                                    <XMarkIcon v-else class="h-5 w-5 text-red-500" />
                                </td>
                                <!-- Aktionen -->
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm">
                                    <VButton :href="route('admin.impersonate', user.id)" size="sm" variant="secondary"
                                        :disabled="user.id === $page.props.auth.user.id"
                                        class="flex items-center gap-1">
                                        <UserCircleIcon class="w-5 h-5" />
                                        Impersonate
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
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue'
import { CheckIcon, XMarkIcon, UserCircleIcon } from '@heroicons/vue/24/solid'
import { ref, computed } from 'vue'
import VButton from '@/components/VButton.vue'

interface User {
    id: number
    name: string
    email: string
    created_at: string
    is_admin: boolean

    // Subscription (Laravel Spark / Cashier)
    subscribed: boolean
    subscription_expires_at: string | null,
    canceled: boolean,

    // Relation
    state: string | null
}

const props = defineProps<{
    users: User[]
    currentUserId: number
}>()

const search = ref('')

const filteredUsers = computed(() => {
    if (!search.value) return props.users
    return props.users.filter(user =>
        user.name.toLowerCase().includes(search.value.toLowerCase()) ||
        user.email.toLowerCase().includes(search.value.toLowerCase())
    )
})
</script>
