<template>
    <PageWrapper title="Fangmeldung" :eyebrow="`Saison ${year}`">
        <template v-slot:actions>
            <VButton variant="secondary" :href="exportUrl" :disabled="!totals.count" external>
                <TableCellsIcon class="size-4" /> CSV für Excel
            </VButton>
            <a :href="printUrl" target="_blank" rel="noopener"
                class="inline-flex items-center gap-2 rounded-full border border-(--app-border-strong) bg-(--app-surface) px-4 py-2.5 text-sm font-semibold text-(--app-ink) transition hover:bg-(--app-hover)"
                :class="{ 'pointer-events-none opacity-50': !totals.count }">
                <PrinterIcon class="size-4" /> Drucken
            </a>
            <a :href="pdfUrl" download
                class="inline-flex items-center gap-2 rounded-full bg-primary-500 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-600"
                :class="{ 'pointer-events-none opacity-50': !totals.count }">
                <ArrowDownTrayIcon class="size-4" /> PDF herunterladen
            </a>
        </template>

        <!-- Filter -->
        <div class="ui-card mb-5 flex flex-col gap-3 p-4 sm:flex-row sm:items-center md:mb-6">
            <div class="no-scrollbar -mx-1 flex gap-1 overflow-x-auto px-1" role="radiogroup" aria-label="Jahr">
                <button v-for="y in years" :key="y" type="button" role="radio" :aria-checked="y === year"
                    class="font-log shrink-0 rounded-full px-3.5 py-1.5 text-sm transition"
                    :class="y === year ? 'bg-(--app-ink) text-(--app-surface)' : 'text-(--app-muted) hover:bg-(--app-hover) hover:text-(--app-ink)'"
                    @click="applyFilter({ year: y, water: null })">
                    {{ y }}
                </button>
            </div>
            <select :value="water ?? ''" class="form-select rounded-full! py-2! text-sm sm:ml-auto sm:w-64" aria-label="Gewässer filtern"
                @change="applyFilter({ year, water: $event.target.value || null })">
                <option value="">Alle Gewässer</option>
                <option v-for="w in waters" :key="w.key" :value="w.key">{{ w.name }}</option>
            </select>
        </div>

        <!-- Totals -->
        <dl class="grid grid-cols-2 gap-3 md:grid-cols-4 md:gap-4">
            <div v-for="stat in stats" :key="stat.label" class="ui-card p-4 md:p-5">
                <dt class="ui-kicker">{{ stat.label }}</dt>
                <dd class="font-display mt-2 text-4xl leading-none md:text-5xl" :class="stat.tone">{{ stat.value }}</dd>
            </div>
        </dl>

        <div v-if="totals.unknown" class="mt-5 flex items-start gap-3 rounded-2xl bg-[color-mix(in_srgb,var(--app-float)_12%,transparent)] px-4 py-3 text-sm md:mt-6">
            <ExclamationTriangleIcon class="mt-0.5 size-5 shrink-0 text-(--app-float)" />
            <p>
                <strong>{{ totals.unknown }} {{ totals.unknown === 1 ? 'Fang ist' : 'Fänge sind' }}</strong> noch nicht als entnommen oder
                zurückgesetzt markiert. Für eine vollständige Fangmeldung kannst du das unten in der Liste direkt nachtragen.
            </p>
        </div>

        <div v-if="!totals.count" class="ui-card mt-6 flex flex-col items-center px-6 py-14 text-center">
            <DocumentChartBarIcon class="size-10 text-(--app-subtle)" />
            <p class="font-display mt-4 text-3xl">Keine Fänge in {{ year }}</p>
            <p class="ui-muted mt-2 max-w-md text-sm">Sobald du Fänge einträgst, erscheint hier deine Jahresstatistik für Revier oder Verein.</p>
        </div>

        <template v-else>
            <!-- Summary per water -->
            <div class="mt-6 grid gap-5 md:gap-6 xl:grid-cols-2">
                <section v-for="group in summary" :key="group.water" class="ui-card overflow-hidden">
                    <header class="flex items-center justify-between gap-3 border-b border-(--app-border) px-5 py-4">
                        <h2 class="truncate font-semibold">{{ group.water }}</h2>
                        <span class="ui-badge ui-badge-accent shrink-0">{{ group.count }} {{ group.count === 1 ? 'Fang' : 'Fänge' }}</span>
                    </header>
                    <div class="overflow-x-auto">
                        <table class="ui-table">
                            <thead>
                                <tr>
                                    <th>Fischart</th>
                                    <th class="text-right">Entn.</th>
                                    <th class="text-right">Zurück</th>
                                    <th class="text-right">Gewicht entn.</th>
                                    <th class="text-right">Größter</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="fish in group.fish" :key="fish.name">
                                    <td class="font-medium">
                                        {{ fish.name }}
                                        <span v-if="fish.unknown" class="ml-1 text-xs text-(--app-float)" :title="`${fish.unknown} ohne Angabe`">+{{ fish.unknown }}?</span>
                                    </td>
                                    <td class="font-log text-right">{{ fish.kept }}</td>
                                    <td class="font-log text-right">{{ fish.released }}</td>
                                    <td class="font-log text-right">{{ formatKg(fish.keptWeight) }}</td>
                                    <td class="font-log text-right">{{ fish.maxLength }} cm</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <!-- All catches -->
            <section class="ui-card mt-6 overflow-hidden">
                <header class="flex flex-wrap items-center justify-between gap-3 border-b border-(--app-border) px-5 py-4">
                    <div>
                        <p class="ui-kicker">Alle Fänge</p>
                        <p class="ui-muted mt-0.5 text-sm">Verbleib antippen, um ihn zu ändern.</p>
                    </div>
                    <label class="flex cursor-pointer items-center gap-2 text-sm font-medium">
                        <input v-model="onlyUnknown" type="checkbox" class="form-checkbox" />
                        Nur ohne Angabe
                    </label>
                </header>
                <ul class="divide-y divide-(--app-border)">
                    <li v-for="c in listedCatches" :key="c.id"
                        class="flex flex-col gap-3 px-5 py-3.5 sm:flex-row sm:items-center">
                        <Link :href="`/catched/${c.id}`" class="group min-w-0 flex-1">
                            <span class="block truncate font-semibold group-hover:text-(--app-accent-ink)">{{ c.fish }}
                                <span class="font-log ml-1 text-sm font-normal text-(--app-muted)">{{ c.length }} cm<template v-if="c.weight"> · {{ c.weight }} g</template></span>
                            </span>
                            <span class="block truncate text-xs text-(--app-subtle)">{{ formatDate(c.date) }} · {{ c.water }}</span>
                        </Link>
                        <ReleaseToggle compact :model-value="c.released" :disabled="saving === c.id"
                            @update:model-value="value => setReleased(c, value)" />
                    </li>
                    <li v-if="!listedCatches.length" class="px-5 py-8 text-center text-sm text-(--app-muted)">
                        Alle Fänge haben eine Angabe – deine Fangmeldung ist vollständig.
                    </li>
                </ul>
            </section>
        </template>
    </PageWrapper>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue'
import VButton from '@/components/VButton.vue'
import ReleaseToggle from '@/components/ReleaseToggle.vue'
import {
    ArrowDownTrayIcon, PrinterIcon, ExclamationTriangleIcon, DocumentChartBarIcon, TableCellsIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
    years: Array,
    year: Number,
    water: String,
    waters: Array,
    totals: Object,
    summary: Array,
    catches: Array,
})

const query = computed(() => {
    const params = new URLSearchParams({ year: props.year })
    if (props.water) params.set('water', props.water)
    return params.toString()
})
const exportUrl = computed(() => `/report/export?${query.value}`)
const printUrl = computed(() => `/report/print?${query.value}`)
const pdfUrl = computed(() => `/report/pdf?${query.value}`)

const formatKg = (grams) => grams ? `${(grams / 1000).toLocaleString('de-AT', { maximumFractionDigits: 2 })} kg` : '–'
const formatDate = (date) => new Date(date).toLocaleString('de-AT', {
    day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit',
})

const stats = computed(() => [
    { label: 'Fänge', value: props.totals.count },
    { label: 'Entnommen', value: props.totals.kept, tone: 'text-(--app-accent-ink)' },
    { label: 'Zurückgesetzt', value: props.totals.released, tone: 'text-(--app-success)' },
    { label: 'Gewicht entn.', value: formatKg(props.totals.keptWeight) },
])

function applyFilter({ year, water }) {
    router.get('/report', { year, ...(water ? { water } : {}) }, { preserveScroll: true, preserveState: true, replace: true })
}

const onlyUnknown = ref(false)
const listedCatches = computed(() => onlyUnknown.value ? props.catches.filter(c => c.released === null) : props.catches)

const saving = ref(null)
function setReleased(c, value) {
    saving.value = c.id
    router.patch(route('app.report.release', c.id), { released: value }, {
        preserveScroll: true,
        preserveState: true,
        onFinish: () => { saving.value = null },
    })
}
</script>
