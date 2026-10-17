<template>
    <Modal :show="show" max-width="lg" @close="close">
        <form class="p-6 md:p-7" @submit.prevent="submit">
            <span class="flex size-12 items-center justify-center rounded-2xl bg-(--app-accent-soft) text-(--app-accent-ink)">
                <component :is="config.icon" class="size-6" />
            </span>
            <h2 class="font-display mt-4 text-3xl">{{ config.title }}</h2>
            <p class="ui-muted mt-1.5 text-sm">
                Du kannst den Eintrag sofort für deinen Fang verwenden. Nach einer kurzen Prüfung durch unser Team
                wird er für alle im Wiki sichtbar.
            </p>

            <div class="mt-6 space-y-4">
                <VInput :id="`suggest-${type}-name`" v-model="form.name" :label="config.nameLabel" mandatory
                    :placeholder="config.namePlaceholder" :error="errors.name" />

                <fieldset v-if="isWater">
                    <legend class="block text-sm font-medium mb-1.5 text-(--app-ink)">
                        Bundesland <span class="text-(--app-float)">*</span>
                        <span class="font-normal text-(--app-muted)">– mehrere möglich</span>
                    </legend>
                    <div class="flex flex-wrap gap-1.5">
                        <button v-for="state in states" :key="state.id" type="button" :aria-pressed="form.states.includes(state.id)"
                            class="rounded-full border px-3 py-1.5 text-sm font-medium transition"
                            :class="form.states.includes(state.id)
                                ? 'border-(--app-accent) bg-(--app-accent-soft) text-(--app-accent-ink)'
                                : 'border-(--app-border) text-(--app-muted) hover:border-(--app-border-strong) hover:text-(--app-ink)'"
                            @click="toggleState(state.id)">
                            {{ state.name }}
                        </button>
                    </div>
                    <div v-if="errors.states" class="text-xs mt-1.5 font-medium text-(--app-danger)">{{ errors.states }}</div>
                </fieldset>

                <div>
                    <VTextarea :id="`suggest-${type}-desc`" v-model="form.desc" label="Beschreibung" :rows="3"
                        :placeholder="config.descPlaceholder" />
                    <div v-if="errors.desc" class="text-xs mt-1.5 font-medium text-(--app-danger)">{{ errors.desc }}</div>
                </div>

                <div v-if="isWater" class="grid gap-4 sm:grid-cols-2">
                    <VInput :id="`suggest-${type}-rights`" v-model="form.fishing_rights" label="Fischereirecht / Verein"
                        placeholder="z. B. Fischereiverein Musterdorf" :error="errors.fishing_rights" />
                    <VInput :id="`suggest-${type}-tickets`" v-model="form.ticket_sales" label="Lizenz erhältlich bei"
                        placeholder="z. B. Trafik am Hauptplatz" :error="errors.ticket_sales" />
                </div>
            </div>

            <div class="mt-7 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <VButton variant="secondary" @click="close">Abbrechen</VButton>
                <VButton type="submit" :loading="processing" :disabled="processing">
                    <PaperAirplaneIcon class="size-4" /> Einreichen & verwenden
                </VButton>
            </div>
        </form>
    </Modal>
</template>

<script setup>
import { computed, h, reactive, ref, watch } from 'vue'
import axios from 'axios'
import Modal from '@/JetstreamComponents/Modal.vue'
import VButton from '@/components/VButton.vue'
import VInput from '@/components/VInput.vue'
import VTextarea from '@/components/VTextarea.vue'
import { PaperAirplaneIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    show: Boolean,
    // fish | lake | river
    type: { type: String, default: 'fish' },
    states: { type: Array, default: () => [] },
    // Vorbelegung der Bundesländer, z. B. aus dem Profil
    defaultStateId: { type: Number, default: null },
})

const emit = defineEmits(['close', 'created'])

const FishIcon = (p) => h('svg', { ...p, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': 1.5 }, [
    h('path', { d: 'M2.5 12c2.6-3.6 6.1-5.5 10-5.5 3.1 0 5.6 1.4 7.5 4L22 8.5v7l-2 -2c-1.9 2.6-4.4 4-7.5 4-3.9 0-7.4-1.9-10-5.5Z', 'stroke-linejoin': 'round' }),
    h('circle', { cx: 7.5, cy: 11, r: 0.9, fill: 'currentColor', stroke: 'none' }),
])
const WaterIcon = (p) => h('svg', { ...p, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': 1.5, 'stroke-linecap': 'round' }, [
    h('path', { d: 'M3 9c3-2 6 2 9 0s6-2 9 0' }),
    h('path', { d: 'M3 14c3-2 6 2 9 0s6-2 9 0' }),
    h('path', { d: 'M3 19c3-2 6 2 9 0s6-2 9 0' }),
])

const CONFIG = {
    fish: {
        title: 'Fischart vorschlagen', nameLabel: 'Name der Fischart', namePlaceholder: 'z. B. Marmorierte Forelle',
        descPlaceholder: 'Merkmale, Lebensraum, Schonzeit … (optional)', icon: FishIcon,
    },
    lake: {
        title: 'See vorschlagen', nameLabel: 'Name des Sees', namePlaceholder: 'z. B. Schwarzsee',
        descPlaceholder: 'Lage, Größe, Besonderheiten … (optional)', icon: WaterIcon,
    },
    river: {
        title: 'Fluss vorschlagen', nameLabel: 'Name des Flusses oder Bachs', namePlaceholder: 'z. B. Gail',
        descPlaceholder: 'Abschnitt, Strecke, Besonderheiten … (optional)', icon: WaterIcon,
    },
}

const config = computed(() => CONFIG[props.type] ?? CONFIG.fish)
const isWater = computed(() => props.type !== 'fish')

const emptyForm = () => ({
    name: '', desc: '', fishing_rights: '', ticket_sales: '',
    states: props.defaultStateId ? [props.defaultStateId] : [],
})
const form = reactive(emptyForm())
const errors = ref({})
const processing = ref(false)

watch(() => props.show, (open) => {
    if (open) {
        Object.assign(form, emptyForm())
        errors.value = {}
    }
})

const toggleState = (id) => {
    form.states = form.states.includes(id) ? form.states.filter(s => s !== id) : [...form.states, id]
}

const close = () => emit('close')

async function submit() {
    processing.value = true
    errors.value = {}

    const payload = isWater.value
        ? { ...form }
        : { name: form.name, desc: form.desc }

    try {
        const { data } = await axios.post(route('app.submissions.store', props.type), payload)
        emit('created', data)
    } catch (error) {
        const fieldErrors = error.response?.data?.errors
        errors.value = fieldErrors
            ? Object.fromEntries(Object.entries(fieldErrors).map(([key, messages]) => [key.split('.')[0], messages[0]]))
            : { name: error.response?.status === 429
                ? 'Zu viele Vorschläge in kurzer Zeit – bitte versuch es später noch einmal.'
                : 'Das hat leider nicht geklappt. Bitte versuch es noch einmal.' }
    } finally {
        processing.value = false
    }
}
</script>
