<template>
    <PageWrapper :title="config.title" eyebrow="Administration · Wiki" backTo="/admin">
        <template v-slot:actions>
            <VButton :href="`/admin/${type}/create`"><PlusIcon class="size-4" /> {{ config.create }}</VButton>
        </template>

        <div class="space-y-5">
            <!-- Kennzahlen -->
            <dl class="grid grid-cols-2 gap-3 md:grid-cols-4">
                <div v-for="stat in stats" :key="stat.label" class="ui-card p-4">
                    <dt class="text-xs text-(--app-muted)">{{ stat.label }}</dt>
                    <dd class="font-display mt-1.5 text-3xl leading-none">{{ stat.value }}</dd>
                </div>
            </dl>

            <!-- Suche, Filter, Sortierung -->
            <div class="flex flex-col gap-3 lg:flex-row">
                <div class="ui-search flex-1">
                    <MagnifyingGlassIcon />
                    <input v-model="search" type="search" :placeholder="`${config.singular} suchen …`" class="form-input w-full rounded-full!"
                        :aria-label="`${config.singular} suchen`" />
                </div>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:flex">
                    <select v-model="stateFilter" class="form-select rounded-full! lg:w-48" aria-label="Bundesland filtern">
                        <option :value="null">Alle Bundesländer</option>
                        <option v-for="state in states" :key="state.id" :value="state.id">{{ state.name }}</option>
                        <option value="none">Ohne Bundesland</option>
                    </select>
                    <select v-model="quality" class="form-select rounded-full! lg:w-48" aria-label="Vollständigkeit filtern">
                        <option value="all">Alle Einträge</option>
                        <option value="incomplete">Unvollständig</option>
                        <option value="complete">Vollständig</option>
                        <option value="noFish">Ohne Fischarten</option>
                    </select>
                    <select v-model="sort" class="form-select col-span-2 rounded-full! sm:col-span-1 lg:w-48" aria-label="Sortierung">
                        <option value="name">Name A–Z</option>
                        <option value="catches">Meiste Fänge</option>
                        <option value="incomplete">Am unvollständigsten</option>
                        <option value="updated">Zuletzt geändert</option>
                    </select>
                </div>
            </div>

            <p class="ui-muted text-sm">{{ visible.length }} von {{ items.length }} {{ config.plural }}</p>

            <div class="ui-card overflow-x-auto">
                <table class="ui-table min-w-full">
                    <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Bundesland</th>
                            <th scope="col">Wiki-Angaben</th>
                            <th scope="col" class="text-right">Fischarten</th>
                            <th scope="col" class="text-right">Fänge</th>
                            <th scope="col"><span class="sr-only">Aktionen</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in visible" :key="item.id">
                            <td>
                                <Link :href="`/admin/${type}/${item.id}/edit`" class="font-semibold text-(--app-ink) hover:text-(--app-accent-ink)">
                                    {{ item.name }}
                                </Link>
                                <span v-if="item.is_default" class="ui-badge ml-1.5">Platzhalter</span>
                                <p class="text-xs text-(--app-subtle)">geändert {{ formatDate(item.updated_at) }}<template v-if="item.updated_by"> von {{ item.updated_by }}</template></p>
                            </td>
                            <td>
                                <div v-if="item.states.length" class="flex flex-wrap gap-1">
                                    <span v-for="state in item.states" :key="state.id" class="ui-badge">{{ state.name }}</span>
                                </div>
                                <span v-else-if="!item.is_default" class="ui-badge ui-badge-float">fehlt</span>
                                <span v-else class="text-(--app-subtle)">–</span>
                            </td>
                            <td class="whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="flex gap-1" role="img" :aria-label="`${item.completeness_score} von ${FIELDS.length} Angaben vorhanden`">
                                        <span v-for="field in FIELDS" :key="field.key" class="h-2 w-4 rounded-full"
                                            :class="item.completeness[field.key] ? 'bg-(--app-success)' : 'bg-(--app-border-strong)'"
                                            :title="`${field.label}: ${item.completeness[field.key] ? 'vorhanden' : 'fehlt'}`"></span>
                                    </div>
                                    <span class="font-log text-xs text-(--app-muted)">{{ item.completeness_score }}/{{ FIELDS.length }}</span>
                                </div>
                                <p v-if="missing(item).length" class="mt-1 max-w-56 truncate text-xs text-(--app-subtle)" :title="missing(item).join(', ')">
                                    fehlt: {{ missing(item).join(', ') }}
                                </p>
                            </td>
                            <td class="font-log whitespace-nowrap text-right">{{ item.fish_count }}</td>
                            <td class="font-log whitespace-nowrap text-right">{{ formatNumber(item.catches_count) }}</td>
                            <td class="whitespace-nowrap text-right">
                                <div class="flex justify-end gap-1">
                                    <Link v-if="item.states.length" :href="`/states/${item.states[0].id}/${config.wikiSegment}/${item.id}`" class="ui-icon-btn"
                                        :aria-label="`${item.name} im Wiki ansehen`" title="Im Wiki ansehen">
                                        <EyeIcon class="size-4" />
                                    </Link>
                                    <Link :href="`/admin/${type}/${item.id}/edit`" class="ui-icon-btn" :aria-label="`${item.name} bearbeiten`" title="Bearbeiten">
                                        <PencilSquareIcon class="size-4" />
                                    </Link>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!visible.length">
                            <td colspan="6" class="py-10 text-center text-sm text-(--app-muted)">Keine {{ config.plural }} gefunden.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </PageWrapper>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import VButton from '@/components/VButton.vue'
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue'
import { EyeIcon, MagnifyingGlassIcon, PencilSquareIcon, PlusIcon } from '@heroicons/vue/24/outline'
import { formatDate, formatNumber, normalize } from '@/utils/format'

const props = defineProps({
    // lake | river
    type: { type: String, required: true },
    items: { type: Array, default: () => [] },
    states: { type: Array, default: () => [] },
})

const CONFIG = {
    lake: { title: 'Seen', singular: 'See', plural: 'Seen', create: 'See anlegen', wikiSegment: 'lakes' },
    river: { title: 'Flüsse', singular: 'Fluss', plural: 'Flüsse', create: 'Fluss anlegen', wikiSegment: 'rivers' },
}
const config = computed(() => CONFIG[props.type])

const FIELDS = [
    { key: 'desc', label: 'Beschreibung' },
    { key: 'hint', label: 'Tipp' },
    { key: 'fishing_rights', label: 'Fischereirecht' },
    { key: 'ticket_sales', label: 'Kartenverkauf' },
    { key: 'fish', label: 'Fischarten' },
]
const missing = (item) => FIELDS.filter(f => !item.completeness[f.key]).map(f => f.label)
const isComplete = (item) => item.completeness_score === FIELDS.length

const stats = computed(() => [
    { label: config.value.plural, value: props.items.length },
    { label: 'Vollständig', value: props.items.filter(isComplete).length },
    { label: 'Ohne Fischarten', value: props.items.filter(i => !i.fish_count).length },
    { label: 'Mit Fängen', value: props.items.filter(i => i.catches_count).length },
])

const search = ref('')
const stateFilter = ref(null)
const quality = ref('all')
const sort = ref('name')

const QUALITY = {
    all: () => true,
    incomplete: (i) => !isComplete(i),
    complete: isComplete,
    noFish: (i) => !i.fish_count,
}

const SORTERS = {
    name: (a, b) => (b.is_default - a.is_default) || a.name.localeCompare(b.name, 'de'),
    catches: (a, b) => b.catches_count - a.catches_count,
    incomplete: (a, b) => a.completeness_score - b.completeness_score,
    updated: (a, b) => new Date(b.updated_at) - new Date(a.updated_at),
}

const visible = computed(() => {
    const term = normalize(search.value.trim())
    return props.items
        .filter(QUALITY[quality.value])
        .filter(i => stateFilter.value === null
            || (stateFilter.value === 'none' ? !i.states.length : i.states.some(s => s.id === stateFilter.value)))
        .filter(i => !term || normalize(i.name).includes(term))
        .sort(SORTERS[sort.value])
})
</script>
