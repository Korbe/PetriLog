<template>
    <PageWrapper title="Ticket" backTo="/admin/tickets">
        <div class="space-y-6">

            <!-- Ticket Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">

                <!-- Header -->
                <div
                    class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 p-6 border-b dark:border-gray-700">
                    <div>
                        <h1 class="text-2xl font-bold">
                            Ticket #{{ ticket.id }}
                        </h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Erstellt am {{ ticket.created_at }}
                        </p>
                    </div>

                    <div class="flex gap-2">
                        <VButton @click="confirmDelete" variant="danger">
                            Löschen
                        </VButton>
                    </div>
                </div>

                <!-- Meta Info -->
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
                    <div>
                        <div class="text-gray-500 dark:text-gray-400">Titel</div>
                        <div class="font-medium">{{ ticket.title }}</div>
                    </div>

                    <div>
                        <div class="text-gray-500 dark:text-gray-400">Kategorie</div>
                        <div class="font-medium capitalize">{{ ticket.category }}</div>
                    </div>

                    <div>
                        <div class="text-gray-500 dark:text-gray-400">User</div>
                        <div class="font-medium">{{ ticket.user }} ({{ ticket.email }})</div>
                    </div>

                    <div class="sm:col-span-2 lg:col-span-3">
                        <div class="text-gray-500 dark:text-gray-400">URL</div>
                        <a v-if="ticket.url" :href="ticket.url" target="_blank"
                            class="text-blue-600 hover:underline break-all">
                            {{ ticket.url }}
                        </a>
                        <span v-else class="italic text-gray-400">Keine URL angegeben</span>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-6 space-y-6 border-t dark:border-gray-700">

                    <div>
                        <h2 class="font-semibold mb-2">Beschreibung</h2>
                        <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">
                            {{ ticket.description || 'Keine Beschreibung angegeben.' }}
                        </p>
                    </div>

                    <div>
                        <h2 class="font-semibold mb-2">Reproduktionsschritte</h2>
                        <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">
                            {{ ticket.steps || 'Keine Schritte angegeben.' }}
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </PageWrapper>
</template>

<script setup>
import VButton from '@/components/VButton.vue'
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue'
import { Inertia } from '@inertiajs/inertia'

const props = defineProps({
    ticket: Object,
})

function confirmDelete() {
    if (confirm('Willst du dieses Ticket wirklich löschen?')) {
        Inertia.delete(`/admin/tickets/${props.ticket.id}`)
    }
}
</script>
