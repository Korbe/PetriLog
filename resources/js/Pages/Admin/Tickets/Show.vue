<template>
    <PageWrapper :title="`Ticket #${ticket.id}`" :eyebrow="`Erstellt am ${ticket.created_at}`" backTo="/admin/tickets">
        <template v-slot:actions>
            <VButton @click="confirmDelete" variant="danger">
                <TrashIcon class="size-4" /> Löschen
            </VButton>
        </template>

        <div class="grid gap-5 md:gap-6 lg:grid-cols-12">
            <div class="space-y-5 md:space-y-6 lg:col-span-8">
                <section class="ui-card p-5 md:p-7">
                    <p class="ui-kicker">Titel</p>
                    <h2 class="mt-1 text-xl font-semibold">{{ ticket.title }}</h2>

                    <p class="ui-kicker mt-6">Beschreibung</p>
                    <p v-if="description" class="ui-muted mt-2 whitespace-pre-line text-sm leading-relaxed">{{ description }}</p>
                    <p v-else class="ui-muted mt-2 text-sm">Keine Beschreibung angegeben.</p>
                </section>

                <section class="ui-card p-5 md:p-7">
                    <p class="ui-kicker">Reproduktionsschritte</p>
                    <p v-if="steps" class="ui-muted mt-2 whitespace-pre-line text-sm leading-relaxed">{{ steps }}</p>
                    <p v-else class="ui-muted mt-2 text-sm">Keine Schritte angegeben.</p>
                </section>
            </div>

            <aside class="lg:col-span-4">
                <dl class="ui-card divide-y divide-(--app-border) text-sm">
                    <div class="p-5">
                        <dt class="ui-kicker">Kategorie</dt>
                        <dd class="mt-1.5"><span class="ui-badge ui-badge-accent">{{ ticket.category || '–' }}</span></dd>
                    </div>
                    <div class="p-5">
                        <dt class="ui-kicker">User</dt>
                        <dd class="mt-1.5 font-semibold">{{ ticket.user }}</dd>
                        <dd><a :href="`mailto:${ticket.email}`" class="ui-link text-sm">{{ ticket.email }}</a></dd>
                    </div>
                    <div class="p-5">
                        <dt class="ui-kicker">Betroffene URL</dt>
                        <dd class="mt-1.5">
                            <a v-if="ticket.url" :href="ticket.url" target="_blank" rel="noopener" class="ui-link break-all">
                                {{ ticket.url }}
                            </a>
                            <span v-else class="text-(--app-subtle)">Keine URL angegeben</span>
                        </dd>
                    </div>
                </dl>
            </aside>
        </div>
    </PageWrapper>
</template>

<script setup>
import VButton from '@/components/VButton.vue'
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue'
import { TrashIcon } from '@heroicons/vue/24/outline'
import { Inertia } from '@inertiajs/inertia'
import { computed } from 'vue'
import { confirmDialog } from '@/composables/useDialog'

const props = defineProps({
    ticket: Object,
})

// Ticket texts come from the user's rich text editor and are not sanitized server-side,
// so they are shown as plain text (never v-html).
const toText = (html) => {
    if (!html) return ''
    const withBreaks = html.replace(/<br\s*\/?>/gi, '\n').replace(/<\/(p|div|li|h[1-6])>/gi, '\n')
    if (typeof DOMParser === 'undefined') return withBreaks.replace(/<[^>]*>/g, '').trim()
    return (new DOMParser().parseFromString(withBreaks, 'text/html').body.textContent ?? '').trim()
}
const description = computed(() => toText(props.ticket.description))
const steps = computed(() => toText(props.ticket.steps))

async function confirmDelete() {
    const confirmed = await confirmDialog({
        title: 'Ticket löschen?',
        message: `Ticket #${props.ticket.id} „${props.ticket.title}“ wird dauerhaft gelöscht.`,
        confirmText: 'Ticket löschen',
        tone: 'danger',
    })
    if (confirmed) {
        Inertia.delete(`/admin/tickets/${props.ticket.id}`)
    }
}
</script>
