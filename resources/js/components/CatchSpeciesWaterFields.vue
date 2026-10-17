<template>
    <div class="space-y-5">
        <!-- Fischart -->
        <div>
            <div class="mb-1.5 flex items-baseline justify-between gap-3">
                <label class="block text-sm font-medium text-(--app-ink)">
                    Fischart <span class="text-(--app-float)">*</span>
                </label>
                <button type="button" class="ui-link text-xs font-semibold" @click="openSuggest('fish')">
                    Nicht dabei? Vorschlagen
                </button>
            </div>
            <VCombobox v-model="selectedFish" :options="fish" label="name" track-by="id" placeholder="Fisch auswählen"
                search-placeholder="Fischart suchen …" aria-label="Fischart" />
            <div v-if="errors?.fish_id" class="text-xs mt-1.5 font-medium text-(--app-danger)">{{ errors.fish_id }}</div>
        </div>

        <!-- Gewässer: See oder Fluss -->
        <fieldset class="ui-panel space-y-4 p-4">
            <legend class="sr-only">Gewässer</legend>
            <p class="text-sm font-medium text-(--app-ink)">Gewässer <span class="text-(--app-float)">*</span>
                <span class="font-normal text-(--app-muted)">– wähle einen See <strong>oder</strong> einen Fluss</span></p>
            <div>
                <div class="mb-1.5 flex items-baseline justify-between gap-3">
                    <label class="block text-xs font-medium text-(--app-muted)">See</label>
                    <button type="button" class="ui-link text-xs font-semibold" @click="openSuggest('lake')">See vorschlagen</button>
                </div>
                <VCombobox v-model="selectedLake" :options="lakes" label="name" track-by="id" placeholder="See auswählen"
                    search-placeholder="See suchen …" aria-label="See" />
                <div v-if="errors?.lake_id" class="text-xs mt-1.5 font-medium text-(--app-danger)">{{ errors.lake_id }}</div>
            </div>
            <div>
                <div class="mb-1.5 flex items-baseline justify-between gap-3">
                    <label class="block text-xs font-medium text-(--app-muted)">Fluss</label>
                    <button type="button" class="ui-link text-xs font-semibold" @click="openSuggest('river')">Fluss vorschlagen</button>
                </div>
                <VCombobox v-model="selectedRiver" :options="rivers" label="name" track-by="id" placeholder="Fluss auswählen"
                    search-placeholder="Fluss suchen …" aria-label="Fluss" />
                <div v-if="errors?.river_id" class="text-xs mt-1.5 font-medium text-(--app-danger)">{{ errors.river_id }}</div>
            </div>
        </fieldset>

        <transition name="fade">
            <p v-if="notice" class="flex items-start gap-2 rounded-2xl bg-(--app-success-soft) px-4 py-3 text-sm font-medium text-(--app-success)" role="status">
                <CheckCircleIcon class="mt-0.5 size-4 shrink-0" /> {{ notice }}
            </p>
        </transition>

        <SuggestEntryDialog :show="suggestType !== null" :type="suggestType ?? 'fish'" :states="states"
            :default-state-id="userStateId" @close="suggestType = null" @created="onCreated" />
    </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import VCombobox from '@/components/VCombobox.vue'
import SuggestEntryDialog from '@/components/SuggestEntryDialog.vue'
import { CheckCircleIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    fishId: [Number, String],
    lakeId: [Number, String],
    riverId: [Number, String],
    fish: { type: Array, default: () => [] },
    lakes: { type: Array, default: () => [] },
    rivers: { type: Array, default: () => [] },
    states: { type: Array, default: () => [] },
    errors: Object,
})

const emit = defineEmits(['update:fishId', 'update:lakeId', 'update:riverId'])

const page = usePage()
const userStateId = computed(() => page.props.auth?.user?.state?.id ?? null)

const selectedFish = computed({
    get: () => props.fish.find(f => f.id === props.fishId) ?? null,
    set: (value) => emit('update:fishId', value?.id ?? null),
})

// Es darf nur ein See ODER ein Fluss gewählt sein
const selectedLake = computed({
    get: () => props.lakes.find(l => l.id === props.lakeId) ?? null,
    set: (value) => {
        emit('update:lakeId', value?.id ?? null)
        if (value) emit('update:riverId', null)
    },
})

const selectedRiver = computed({
    get: () => props.rivers.find(r => r.id === props.riverId) ?? null,
    set: (value) => {
        emit('update:riverId', value?.id ?? null)
        if (value) emit('update:lakeId', null)
    },
})

/* Vorschläge */
const suggestType = ref(null)
const notice = ref('')

const openSuggest = (type) => { suggestType.value = type }

function onCreated(entry) {
    const type = suggestType.value
    suggestType.value = null

    // Auswahllisten neu laden – der neue Eintrag ist für den Einreichenden sofort sichtbar
    router.reload({
        only: ['fish', 'lakes', 'rivers'],
        onSuccess: () => {
            if (type === 'fish') emit('update:fishId', entry.id)
            if (type === 'lake') { emit('update:lakeId', entry.id); emit('update:riverId', null) }
            if (type === 'river') { emit('update:riverId', entry.id); emit('update:lakeId', null) }
            notice.value = entry.message
            setTimeout(() => { notice.value = '' }, 7000)
        },
    })
}
</script>
