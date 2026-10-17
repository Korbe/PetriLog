<template>
    <PageWrapper title="Bundesländer" eyebrow="Administration · Wiki" backTo="/admin">
        <p class="ui-muted mb-5 max-w-2xl text-sm">
            Die neun Bundesländer sind fix. Hier pflegst du Wappen und Beschreibung – Seen, Flüsse und Vereine ordnest du
            direkt beim jeweiligen Eintrag zu.
        </p>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <article v-for="state in states" :key="state.id" class="ui-card flex flex-col p-5">
                <div class="flex items-start gap-4">
                    <div class="flex h-16 w-14 shrink-0 items-center justify-center rounded-xl bg-(--app-surface-2)">
                        <img v-if="state.image && !brokenImages.has(state.id)" :src="state.image" :alt="`Wappen ${state.name}`"
                            class="max-h-14 max-w-12 object-contain" loading="lazy" @error="brokenImages.add(state.id)" />
                        <PhotoIcon v-else class="size-6 text-(--app-subtle)" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <h2 class="font-display text-2xl leading-tight">{{ state.name }}</h2>
                        <div class="mt-1.5 flex flex-wrap gap-1.5">
                            <span v-if="!state.image" class="ui-badge ui-badge-float">Wappen fehlt</span>
                            <span v-if="!state.has_desc" class="ui-badge ui-badge-float">Beschreibung fehlt</span>
                            <span v-if="state.image && state.has_desc" class="ui-badge ui-badge-success">Vollständig</span>
                        </div>
                    </div>
                </div>

                <dl class="mt-5 grid grid-cols-4 gap-2 text-center">
                    <div v-for="stat in stats(state)" :key="stat.label" class="ui-panel px-1 py-2.5">
                        <dt class="text-[11px] text-(--app-subtle)">{{ stat.label }}</dt>
                        <dd class="font-display text-2xl leading-none">{{ stat.value }}</dd>
                    </div>
                </dl>

                <div class="mt-5 flex items-center justify-between gap-2 border-t border-(--app-border) pt-4">
                    <span class="text-xs text-(--app-subtle)">geändert {{ formatDate(state.updated_at) }}</span>
                    <div class="flex gap-2">
                        <VButton size="sm" variant="ghost" :href="`/states/${state.id}`">
                            <EyeIcon class="size-4" /> Wiki
                        </VButton>
                        <VButton size="sm" variant="secondary" :href="route('admin.state.edit', state.id)">
                            <PencilSquareIcon class="size-4" /> Bearbeiten
                        </VButton>
                    </div>
                </div>
            </article>
        </div>
    </PageWrapper>
</template>

<script setup>
import VButton from '@/components/VButton.vue'
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue'
import { EyeIcon, PencilSquareIcon, PhotoIcon } from '@heroicons/vue/24/outline'
import { formatDate } from '@/utils/format'
import { reactive } from 'vue'

defineProps({
    states: { type: Array, default: () => [] },
})

const brokenImages = reactive(new Set())

const stats = (state) => [
    { label: 'Seen', value: state.lakes_count },
    { label: 'Flüsse', value: state.rivers_count },
    { label: 'Vereine', value: state.associations_count },
    { label: 'User', value: state.users_count },
]
</script>
