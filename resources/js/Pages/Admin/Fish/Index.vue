<template>
    <PageWrapper title="Fischarten" eyebrow="Administration · Wiki" backTo="/admin">
        <template v-slot:actions>
            <VButton href="/admin/fish/create"><PlusIcon class="size-4" /> Fischart anlegen</VButton>
        </template>

        <div class="space-y-5">
            <!-- Filter -->
            <div class="no-scrollbar -mx-1 flex gap-2 overflow-x-auto px-1 pb-1" role="tablist" aria-label="Fischarten filtern">
                <button v-for="segment in segments" :key="segment.key" type="button" role="tab" :aria-selected="filter === segment.key"
                    class="flex shrink-0 items-center gap-2 rounded-full border px-4 py-2 text-sm font-semibold transition"
                    :class="filter === segment.key
                        ? 'border-(--app-ink) bg-(--app-ink) text-(--app-surface)'
                        : 'border-(--app-border) bg-(--app-surface) text-(--app-muted) hover:border-(--app-border-strong) hover:text-(--app-ink)'"
                    @click="filter = segment.key">
                    {{ segment.label }}
                    <span class="font-log text-xs opacity-70">{{ segment.count }}</span>
                </button>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <div class="ui-search flex-1">
                    <MagnifyingGlassIcon />
                    <input v-model="search" type="search" placeholder="Fischart suchen …" class="form-input w-full rounded-full!"
                        aria-label="Fischart suchen" />
                </div>
                <select v-model="sort" class="form-select rounded-full! sm:w-56" aria-label="Sortierung">
                    <option value="name">Name A–Z</option>
                    <option value="catches">Meiste Fänge</option>
                    <option value="waters">Meiste Gewässer</option>
                    <option value="record">Größter Fang</option>
                </select>
            </div>

            <div class="ui-card overflow-x-auto">
                <table class="ui-table min-w-full">
                    <thead>
                        <tr>
                            <th scope="col">Fischart</th>
                            <th scope="col">Steckbrief</th>
                            <th scope="col">Vorkommen</th>
                            <th scope="col" class="text-right">Fänge</th>
                            <th scope="col" class="text-right">Rekord</th>
                            <th scope="col">Letzter Fang</th>
                            <th scope="col"><span class="sr-only">Aktionen</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="fish in visible" :key="fish.id">
                            <td>
                                <Link :href="route('admin.fish.edit', fish.id)" class="group flex items-center gap-3">
                                    <span class="flex size-12 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-(--app-surface-2)">
                                        <img v-if="fish.image && !brokenImages.has(fish.id)" :src="fish.image" alt="" class="size-full object-cover"
                                            loading="lazy" @error="brokenImages.add(fish.id)" />
                                        <PhotoIcon v-else class="size-5 text-(--app-subtle)" />
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block truncate font-semibold text-(--app-ink) group-hover:text-(--app-accent-ink)">{{ fish.name }}</span>
                                        <span v-if="fish.is_default" class="ui-badge mt-0.5">Platzhalter</span>
                                    </span>
                                </Link>
                            </td>
                            <td class="whitespace-nowrap">
                                <div class="flex flex-wrap gap-1.5">
                                    <span class="ui-badge" :class="fish.image ? 'ui-badge-success' : 'ui-badge-float'">{{ fish.image ? 'Bild' : 'Bild fehlt' }}</span>
                                    <span class="ui-badge" :class="fish.has_desc ? 'ui-badge-success' : 'ui-badge-float'">{{ fish.has_desc ? 'Text' : 'Text fehlt' }}</span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap text-sm">
                                <span v-if="fish.lakes_count || fish.rivers_count">
                                    {{ fish.lakes_count }} {{ fish.lakes_count === 1 ? 'See' : 'Seen' }} ·
                                    {{ fish.rivers_count }} {{ fish.rivers_count === 1 ? 'Fluss' : 'Flüsse' }}
                                </span>
                                <span v-else class="text-(--app-subtle)">keinem Gewässer zugeordnet</span>
                            </td>
                            <td class="font-log whitespace-nowrap text-right">{{ formatNumber(fish.catches_count) }}</td>
                            <td class="font-log whitespace-nowrap text-right">
                                <span v-if="fish.record_length">{{ fish.record_length }} cm</span>
                                <span v-else class="text-(--app-subtle)">–</span>
                            </td>
                            <td class="whitespace-nowrap text-sm text-(--app-muted)">{{ relativeTime(fish.last_catch_at) }}</td>
                            <td class="whitespace-nowrap text-right">
                                <div class="flex justify-end gap-1">
                                    <Link :href="`/fish/${fish.id}`" class="ui-icon-btn" :aria-label="`${fish.name} im Wiki ansehen`" title="Im Wiki ansehen">
                                        <EyeIcon class="size-4" />
                                    </Link>
                                    <Link :href="route('admin.fish.edit', fish.id)" class="ui-icon-btn" :aria-label="`${fish.name} bearbeiten`" title="Bearbeiten">
                                        <PencilSquareIcon class="size-4" />
                                    </Link>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!visible.length">
                            <td colspan="7" class="py-10 text-center text-sm text-(--app-muted)">Keine Fischart gefunden.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </PageWrapper>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import VButton from '@/components/VButton.vue'
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue'
import { EyeIcon, MagnifyingGlassIcon, PencilSquareIcon, PhotoIcon, PlusIcon } from '@heroicons/vue/24/outline'
import { formatNumber, normalize, relativeTime } from '@/utils/format'

const props = defineProps({
    fishs: { type: Array, default: () => [] },
})

const FILTERS = {
    all: () => true,
    noImage: (f) => !f.image,
    noDesc: (f) => !f.has_desc,
    noWaters: (f) => !f.lakes_count && !f.rivers_count,
    unused: (f) => !f.catches_count,
}

const filter = ref('all')
const segments = computed(() => [
    { key: 'all', label: 'Alle' },
    { key: 'noImage', label: 'Ohne Bild' },
    { key: 'noDesc', label: 'Ohne Steckbrief' },
    { key: 'noWaters', label: 'Ohne Gewässer' },
    { key: 'unused', label: 'Noch nie gefangen' },
].map(s => ({ ...s, count: props.fishs.filter(FILTERS[s.key]).length })))

// Bilder, deren Datei fehlt, zeigen den Platzhalter statt eines kaputten Bildes
const brokenImages = reactive(new Set())

const sort = ref('name')
const SORTERS = {
    name: (a, b) => a.name.localeCompare(b.name, 'de'),
    catches: (a, b) => b.catches_count - a.catches_count,
    waters: (a, b) => (b.lakes_count + b.rivers_count) - (a.lakes_count + a.rivers_count),
    record: (a, b) => (b.record_length ?? 0) - (a.record_length ?? 0),
}

const search = ref('')
const visible = computed(() => {
    const term = normalize(search.value.trim())
    return props.fishs
        .filter(FILTERS[filter.value])
        .filter(f => !term || normalize(f.name).includes(term))
        // Platzhalter („Anderes / nicht gelistet“) bei Namenssortierung immer oben
        .sort((a, b) => sort.value === 'name' ? (b.is_default - a.is_default) || SORTERS.name(a, b) : SORTERS[sort.value](a, b))
})
</script>
