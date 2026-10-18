<template>
    <PageWrapper title="Fehlermeldungen" eyebrow="Administration" backTo="/admin">
        <div class="mb-5 flex gap-1 overflow-x-auto rounded-full bg-(--app-surface-2) p-1 sm:inline-flex" role="tablist">
            <button v-for="tab in tabs" :key="tab.key" type="button" role="tab" :aria-selected="view === tab.key"
                class="flex shrink-0 items-center gap-1.5 rounded-full px-4 py-2 text-sm font-semibold transition"
                :class="view === tab.key ? 'bg-(--app-surface) text-(--app-ink) shadow-(--app-shadow)' : 'text-(--app-muted) hover:text-(--app-ink)'"
                @click="view = tab.key">
                {{ tab.label }}
                <span class="font-log text-[11px] text-(--app-subtle)">{{ tab.count }}</span>
            </button>
        </div>

        <!-- Offen -->
        <template v-if="view === 'open'">
            <div v-if="!open.length" class="ui-card flex flex-col items-center px-6 py-16 text-center">
                <CheckBadgeIcon class="size-11 text-(--app-success)" />
                <p class="font-display mt-4 text-3xl">Alles erledigt</p>
                <p class="ui-muted mt-2 text-sm">Aktuell gibt es keine offenen Fehlermeldungen.</p>
            </div>

            <div v-else class="grid gap-5 xl:grid-cols-2">
                <article v-for="item in open" :key="item.id" class="ui-card flex flex-col p-5 md:p-6">
                    <header class="flex flex-wrap items-center gap-2">
                        <span class="ui-badge" :class="TYPE_BADGE[item.type]">{{ item.type_label }}</span>
                        <span class="ui-badge">{{ item.category }}</span>
                        <span class="font-log ml-auto text-xs text-(--app-subtle)">{{ formatDate(item.created_at) }}</span>
                    </header>

                    <h2 class="font-display mt-3 text-3xl leading-tight">{{ item.entry?.name ?? 'Gelöschter Eintrag' }}</h2>
                    <p class="mt-1 text-sm text-(--app-muted)">
                        von <strong class="text-(--app-ink)">{{ item.user?.name ?? 'gelöschtem User' }}</strong>
                        <template v-if="item.user"> · <a :href="`mailto:${item.user.email}`" class="ui-link">{{ item.user.email }}</a></template>
                    </p>

                    <blockquote class="mt-4 whitespace-pre-line rounded-2xl bg-(--app-surface-2) px-4 py-3 text-sm text-(--app-ink)">{{ item.message }}</blockquote>

                    <p v-if="item.source" class="mt-3 flex items-start gap-1.5 text-sm text-(--app-muted)">
                        <LinkIcon class="mt-0.5 size-4 shrink-0" />
                        <a v-if="isUrl(item.source)" :href="item.source" target="_blank" rel="noopener noreferrer" class="ui-link break-all">{{ item.source }}</a>
                        <span v-else class="break-words">{{ item.source }}</span>
                    </p>

                    <footer class="mt-5 flex flex-wrap items-center gap-2 border-t border-(--app-border) pt-4">
                        <VButton v-if="item.entry" variant="secondary" size="sm" :href="`/admin/${item.type}/${item.entry.id}/edit`">
                            <PencilSquareIcon class="size-4" /> Eintrag bearbeiten
                        </VButton>
                        <VButton v-if="item.wiki_url" variant="ghost" size="sm" :href="item.wiki_url">
                            <EyeIcon class="size-4" /> Im Wiki
                        </VButton>
                        <div class="ml-auto flex gap-2">
                            <VButton variant="secondary" size="sm" class="text-(--app-danger)!" @click="openResolve(item, 'rejected')">
                                <XMarkIcon class="size-4" /> Ablehnen
                            </VButton>
                            <VButton size="sm" @click="openResolve(item, 'done')">
                                <CheckIcon class="size-4" /> Erledigt
                            </VButton>
                        </div>
                    </footer>
                </article>
            </div>
        </template>

        <!-- Zuletzt bearbeitet -->
        <div v-else class="ui-card overflow-hidden">
            <ul v-if="recent.length" class="divide-y divide-(--app-border)">
                <li v-for="item in recent" :key="item.id" class="flex items-start gap-3 px-5 py-4 md:px-6">
                    <span class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-full"
                        :class="item.status === 'done' ? 'bg-(--app-success-soft) text-(--app-success)' : 'bg-(--app-danger-soft) text-(--app-danger)'">
                        <CheckIcon v-if="item.status === 'done'" class="size-4" />
                        <XMarkIcon v-else class="size-4" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm">
                            <strong>{{ item.entry?.name ?? 'Gelöschter Eintrag' }}</strong>
                            <span class="text-(--app-muted)"> · {{ item.category }}</span>
                        </p>
                        <p class="mt-0.5 line-clamp-2 text-sm text-(--app-muted)">{{ item.message }}</p>
                        <p v-if="item.review_note" class="mt-1 text-xs text-(--app-subtle)">Anmerkung: {{ item.review_note }}</p>
                    </div>
                    <p class="shrink-0 text-right text-xs text-(--app-subtle)">
                        {{ item.status === 'done' ? 'erledigt' : 'abgelehnt' }}<br>
                        {{ formatDate(item.reviewed_at) }}<template v-if="item.reviewer"> · {{ item.reviewer }}</template>
                    </p>
                </li>
            </ul>
            <p v-else class="px-6 py-10 text-center text-sm text-(--app-muted)">Noch keine Meldungen bearbeitet.</p>
        </div>

        <!-- Erledigt / Ablehnen -->
        <Modal :show="resolving !== null" max-width="md" @close="resolving = null">
            <form v-if="resolving" class="p-6" @submit.prevent="resolve">
                <span class="flex size-12 items-center justify-center rounded-2xl"
                    :class="resolveStatus === 'done' ? 'bg-(--app-success-soft) text-(--app-success)' : 'bg-(--app-danger-soft) text-(--app-danger)'">
                    <CheckIcon v-if="resolveStatus === 'done'" class="size-6" />
                    <XMarkIcon v-else class="size-6" />
                </span>
                <h2 class="font-display mt-4 text-3xl">
                    {{ resolveStatus === 'done' ? 'Als erledigt markieren?' : 'Meldung ablehnen?' }}
                </h2>
                <p class="ui-muted mt-2 text-sm">
                    <template v-if="resolveStatus === 'done'">
                        Hast du „{{ resolving.entry?.name }}“ schon angepasst? Der User bekommt eine Danke-Mail mit Link zum Eintrag.
                    </template>
                    <template v-else>Der Eintrag bleibt unverändert, der User wird informiert.</template>
                </p>

                <div class="mt-5">
                    <label for="resolve-note" class="block text-sm font-medium mb-1.5 text-(--app-ink)">
                        Anmerkung für den User <span class="font-normal text-(--app-muted)">(optional)</span>
                    </label>
                    <textarea id="resolve-note" v-model="note" rows="3" maxlength="1000" class="form-textarea w-full"
                        :placeholder="resolveStatus === 'done' ? 'z. B. Zander ist jetzt eingetragen.' : 'z. B. Laut Verein ist die Angabe aktuell.'"></textarea>
                    <p v-if="!resolving.user" class="text-xs mt-1.5 text-(--app-muted)">Das Konto existiert nicht mehr – es wird keine E-Mail verschickt.</p>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <VButton variant="secondary" @click="resolving = null">Abbrechen</VButton>
                    <VButton type="submit" :variant="resolveStatus === 'done' ? 'primary' : 'danger'" :loading="busy" :disabled="busy">
                        {{ resolveStatus === 'done' ? 'Erledigt' : 'Ablehnen' }}
                    </VButton>
                </div>
            </form>
        </Modal>
    </PageWrapper>
</template>

<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue'
import VButton from '@/components/VButton.vue'
import Modal from '@/JetstreamComponents/Modal.vue'
import { CheckBadgeIcon, CheckIcon, EyeIcon, LinkIcon, PencilSquareIcon, XMarkIcon } from '@heroicons/vue/24/outline'
import { formatDate } from '@/utils/format'

const props = defineProps({
    open: { type: Array, default: () => [] },
    recent: { type: Array, default: () => [] },
})

const TYPE_BADGE = { fish: 'ui-badge-float', lake: 'ui-badge-accent', river: 'ui-badge-success' }

const view = ref('open')
const tabs = computed(() => [
    { key: 'open', label: 'Offen', count: props.open.length },
    { key: 'recent', label: 'Zuletzt bearbeitet', count: props.recent.length },
])

const isUrl = (value) => /^https?:\/\//i.test(value)

const resolving = ref(null)
const resolveStatus = ref('done')
const note = ref('')
const busy = ref(false)

function openResolve(item, status) {
    resolving.value = item
    resolveStatus.value = status
    note.value = ''
}

function resolve() {
    busy.value = true
    router.post(route('admin.corrections.resolve', resolving.value.id), {
        status: resolveStatus.value,
        note: note.value.trim() || null,
    }, {
        preserveScroll: true,
        onSuccess: () => { resolving.value = null },
        onFinish: () => { busy.value = false },
    })
}
</script>
