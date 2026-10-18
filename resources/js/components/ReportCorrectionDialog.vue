<template>
    <Modal :show="show" max-width="lg" @close="close">
        <form class="p-6 md:p-7" @submit.prevent="submit">
            <span class="flex size-12 items-center justify-center rounded-2xl bg-(--app-accent-soft) text-(--app-accent-ink)">
                <FlagIcon class="size-6" />
            </span>
            <h2 class="font-display mt-4 text-3xl">Fehler melden</h2>
            <p class="ui-muted mt-1.5 text-sm">
                Stimmt bei „{{ name }}“ etwas nicht oder fehlt etwas? Sag uns Bescheid – unser Team prüft den Hinweis
                und passt das Wiki an. Du bekommst eine Rückmeldung per E-Mail.
            </p>

            <div class="mt-6 space-y-4">
                <fieldset>
                    <legend class="mb-1.5 block text-sm font-medium text-(--app-ink)">
                        Was betrifft es? <span class="text-(--app-float)">*</span>
                    </legend>
                    <div class="flex flex-wrap gap-1.5">
                        <button v-for="option in categories" :key="option.value" type="button"
                            :aria-pressed="form.category === option.value"
                            class="rounded-full border px-3 py-1.5 text-sm font-medium transition"
                            :class="form.category === option.value
                                ? 'border-(--app-accent) bg-(--app-accent-soft) text-(--app-accent-ink)'
                                : 'border-(--app-border) text-(--app-muted) hover:border-(--app-border-strong) hover:text-(--app-ink)'"
                            @click="form.category = option.value">
                            {{ option.label }}
                        </button>
                    </div>
                    <div v-if="form.errors.category" class="mt-1.5 text-xs font-medium text-(--app-danger)">{{ form.errors.category }}</div>
                </fieldset>

                <div>
                    <VTextarea :id="`correction-${type}-message`" v-model="form.message" label="Was stimmt nicht – und was wäre richtig?"
                        :rows="4" :placeholder="placeholder" mandatory />
                    <div v-if="form.errors.message" class="mt-1.5 text-xs font-medium text-(--app-danger)">{{ form.errors.message }}</div>
                </div>

                <VInput :id="`correction-${type}-source`" v-model="form.source" label="Quelle (optional)"
                    placeholder="z. B. Website des Vereins oder „selbst vor Ort gesehen“" :error="form.errors.source" />
            </div>

            <div class="mt-7 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <VButton variant="secondary" @click="close">Abbrechen</VButton>
                <VButton type="submit" :loading="form.processing" :disabled="form.processing || !form.category || !form.message.trim()">
                    <PaperAirplaneIcon class="size-4" /> Meldung senden
                </VButton>
            </div>
        </form>
    </Modal>
</template>

<script setup>
import { computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import Modal from '@/JetstreamComponents/Modal.vue'
import VButton from '@/components/VButton.vue'
import VInput from '@/components/VInput.vue'
import VTextarea from '@/components/VTextarea.vue'
import { FlagIcon, PaperAirplaneIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    show: Boolean,
    // fish | lake | river
    type: { type: String, required: true },
    id: { type: Number, required: true },
    name: { type: String, required: true },
})

const emit = defineEmits(['close'])

const ALL = [
    { value: 'fish', label: 'Fischbestand' },
    { value: 'tickets', label: 'Kartenverkauf / Preise' },
    { value: 'rights', label: 'Fischereirecht / Verein' },
    { value: 'description', label: 'Beschreibung / Tipps' },
    { value: 'other', label: 'Sonstiges' },
]
// Bei Fischarten gibt es keinen Bestand, Kartenverkauf oder Fischereirecht
const categories = computed(() => props.type === 'fish' ? ALL.filter(c => ['description', 'other'].includes(c.value)) : ALL)

const PLACEHOLDERS = {
    fish: 'z. B. Im See gibt es auch Zander, Seeforellen wurden seit Jahren keine mehr gefangen.',
    tickets: 'z. B. Die Tageskarte kostet seit 2026 18 € und gibt es auch online.',
    rights: 'z. B. Das Fischereirecht hat jetzt der Fischereiverein Musterdorf.',
    description: 'z. B. Die Schonzeit stimmt nicht mehr.',
    other: 'Beschreib kurz, was dir aufgefallen ist.',
}
const placeholder = computed(() => PLACEHOLDERS[form.category] ?? PLACEHOLDERS.other)

const form = useForm({ category: null, message: '', source: '' })

watch(() => props.show, (open) => {
    if (open) {
        form.reset()
        form.clearErrors()
        if (props.type === 'fish') form.category = 'description'
    }
})

function close() {
    emit('close')
}

function submit() {
    form.post(route('app.corrections.store', { type: props.type, id: props.id }), {
        preserveScroll: true,
        onSuccess: () => close(),
    })
}
</script>
