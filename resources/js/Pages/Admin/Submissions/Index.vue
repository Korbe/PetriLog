<template>
    <PageWrapper title="Vorschläge" eyebrow="Administration" backTo="/admin">
        <div class="mb-5 flex gap-1 overflow-x-auto rounded-full bg-(--app-surface-2) p-1 sm:inline-flex" role="tablist">
            <button v-for="tab in tabs" :key="tab.key" type="button" role="tab" :aria-selected="filter === tab.key"
                class="flex shrink-0 items-center gap-1.5 rounded-full px-4 py-2 text-sm font-semibold transition"
                :class="filter === tab.key ? 'bg-(--app-surface) text-(--app-ink) shadow-(--app-shadow)' : 'text-(--app-muted) hover:text-(--app-ink)'"
                @click="filter = tab.key">
                {{ tab.label }}
                <span class="font-log text-[11px] text-(--app-subtle)">{{ tab.count }}</span>
            </button>
        </div>

        <div v-if="!visible.length" class="ui-card flex flex-col items-center px-6 py-16 text-center">
            <CheckBadgeIcon class="size-11 text-(--app-success)" />
            <p class="font-display mt-4 text-3xl">Alles geprüft</p>
            <p class="ui-muted mt-2 text-sm">Aktuell warten keine Vorschläge auf eine Freigabe.</p>
        </div>

        <div v-else class="grid gap-5 xl:grid-cols-2">
            <article v-for="entry in visible" :key="`${entry.type}-${entry.id}`" class="ui-card flex flex-col p-5 md:p-6">
                <header class="flex items-start gap-3">
                    <span class="ui-badge shrink-0" :class="TYPES[entry.type].badge">{{ TYPES[entry.type].label }}</span>
                    <span class="font-log ml-auto shrink-0 text-xs text-(--app-subtle)">{{ formatDate(entry.created_at) }}</span>
                </header>

                <h2 class="font-display mt-3 text-3xl leading-tight">{{ entry.name }}</h2>
                <p class="mt-1 text-sm text-(--app-muted)">
                    von <strong class="text-(--app-ink)">{{ entry.submitter?.name ?? 'gelöschtem User' }}</strong>
                    <template v-if="entry.submitter"> · <a :href="`mailto:${entry.submitter.email}`" class="ui-link">{{ entry.submitter.email }}</a></template>
                </p>

                <div v-if="entry.states.length" class="mt-3 flex flex-wrap gap-1.5">
                    <span v-for="state in entry.states" :key="state" class="ui-badge">{{ state }}</span>
                </div>

                <dl class="mt-4 space-y-3 text-sm">
                    <div v-for="field in details(entry)" :key="field.label">
                        <dt class="ui-kicker">{{ field.label }}</dt>
                        <!-- Vom Server als escapter Klartext gespeichert -->
                        <dd class="ui-prose mt-1" v-html="field.html"></dd>
                    </div>
                    <p v-if="!details(entry).length" class="ui-muted italic">Keine weiteren Angaben.</p>
                </dl>

                <p class="ui-panel mt-4 px-3.5 py-2.5 text-sm text-(--app-muted)">
                    Wird in <strong class="text-(--app-ink)">{{ entry.catches }} {{ entry.catches === 1 ? 'Fang' : 'Fängen' }}</strong> verwendet.
                </p>

                <footer class="mt-5 flex flex-wrap items-center gap-2 border-t border-(--app-border) pt-4">
                    <VButton variant="ghost" size="sm" :href="`/admin/${entry.type}/${entry.id}/edit`">
                        <PencilSquareIcon class="size-4" /> Bearbeiten
                    </VButton>
                    <div class="ml-auto flex gap-2">
                        <VButton variant="secondary" size="sm" class="text-(--app-danger)!" @click="openReject(entry)">
                            <XMarkIcon class="size-4" /> Ablehnen
                        </VButton>
                        <VButton size="sm" :loading="busy === key(entry)" :disabled="busy !== null" @click="approve(entry)"
                        :title="entry.submitter ? 'Freigeben und User per E-Mail informieren' : 'Freigeben'">
                            <CheckIcon class="size-4" /> Freigeben
                        </VButton>
                    </div>
                </footer>
            </article>
        </div>

        <!-- Ablehnen -->
        <Modal :show="rejecting !== null" max-width="md" @close="rejecting = null">
            <form v-if="rejecting" class="p-6" @submit.prevent="reject">
                <span class="flex size-12 items-center justify-center rounded-2xl bg-(--app-danger-soft) text-(--app-danger)">
                    <XMarkIcon class="size-6" />
                </span>
                <h2 class="font-display mt-4 text-3xl">„{{ rejecting.name }}“ ablehnen?</h2>
                <p class="ui-muted mt-2 text-sm">
                    Der Vorschlag wird gelöscht.
                    <template v-if="rejecting.catches">
                        Die {{ rejecting.catches }} {{ rejecting.catches === 1 ? 'zugehörige Fang wird' : 'zugehörigen Fänge werden' }}
                        dem folgenden Eintrag zugeordnet – praktisch bei Dubletten oder Tippfehlern.
                    </template>
                </p>

                <div v-if="rejecting.catches" class="mt-5">
                    <label for="replacement" class="block text-sm font-medium mb-1.5 text-(--app-ink)">Fänge zuordnen zu</label>
                    <select id="replacement" v-model="replacementId" class="form-select w-full">
                        <option v-for="option in options[rejecting.type]" :key="option.id" :value="option.id">
                            {{ option.name }}
                        </option>
                    </select>
                    <div v-if="rejectError" class="text-xs mt-1.5 font-medium text-(--app-danger)">{{ rejectError }}</div>
                </div>

                <div class="mt-5">
                    <label for="reject-reason" class="block text-sm font-medium mb-1.5 text-(--app-ink)">
                        Begründung für den User <span class="font-normal text-(--app-muted)">(optional)</span>
                    </label>
                    <textarea id="reject-reason" v-model="reason" rows="3" maxlength="1000" class="form-textarea w-full"
                        placeholder="z. B. Gibt es bereits als „Hecht“."></textarea>
                    <p class="text-xs mt-1.5 text-(--app-muted)">
                        <template v-if="rejecting.submitter">Der User wird per E-Mail informiert.</template>
                        <template v-else>Das Konto existiert nicht mehr – es wird keine E-Mail verschickt.</template>
                    </p>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <VButton variant="secondary" @click="rejecting = null">Abbrechen</VButton>
                    <VButton type="submit" variant="danger" :loading="busy !== null" :disabled="busy !== null">Ablehnen</VButton>
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
import { CheckBadgeIcon, CheckIcon, PencilSquareIcon, XMarkIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    submissions: { type: Array, default: () => [] },
    options: { type: Object, default: () => ({}) },
})

const TYPES = {
    fish: { label: 'Fischart', badge: 'ui-badge-float' },
    lake: { label: 'See', badge: 'ui-badge-accent' },
    river: { label: 'Fluss', badge: 'ui-badge-success' },
}

const filter = ref('all')
const tabs = computed(() => [
    { key: 'all', label: 'Alle', count: props.submissions.length },
    { key: 'fish', label: 'Fischarten', count: props.submissions.filter(s => s.type === 'fish').length },
    { key: 'lake', label: 'Seen', count: props.submissions.filter(s => s.type === 'lake').length },
    { key: 'river', label: 'Flüsse', count: props.submissions.filter(s => s.type === 'river').length },
])
const visible = computed(() => filter.value === 'all' ? props.submissions : props.submissions.filter(s => s.type === filter.value))

const details = (entry) => [
    { label: 'Beschreibung', html: entry.desc },
    { label: 'Fischereirecht / Verein', html: entry.fishing_rights },
    { label: 'Lizenz erhältlich bei', html: entry.ticket_sales },
].filter(field => field.html)

const formatDate = (date) => new Date(date).toLocaleDateString('de-AT', { day: '2-digit', month: '2-digit', year: 'numeric' })
const key = (entry) => `${entry.type}-${entry.id}`

const busy = ref(null)
const visitOptions = { preserveScroll: true, onFinish: () => { busy.value = null } }

function approve(entry) {
    busy.value = key(entry)
    router.post(route('admin.submissions.approve', { type: entry.type, id: entry.id }), {}, visitOptions)
}

const rejecting = ref(null)
const replacementId = ref(null)
const rejectError = ref('')
const reason = ref('')

function openReject(entry) {
    rejecting.value = entry
    rejectError.value = ''
    reason.value = ''
    // Vorauswahl: „Anderes / nicht gelistet“
    replacementId.value = (props.options[entry.type] ?? []).find(o => o.is_default)?.id ?? props.options[entry.type]?.[0]?.id ?? null
}

function reject() {
    const entry = rejecting.value
    busy.value = key(entry)
    router.post(route('admin.submissions.reject', { type: entry.type, id: entry.id }), {
        replacement_id: entry.catches ? replacementId.value : null,
        reason: reason.value.trim() || null,
    }, {
        ...visitOptions,
        onSuccess: () => { rejecting.value = null },
        onError: (errors) => { rejectError.value = errors.replacement_id ?? Object.values(errors)[0] },
    })
}
</script>
