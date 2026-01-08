<template>
    <PageWrapper title="Fluss bearbeiten" backTo="/admin/river">
        <div class="w-full bg-white flex flex-col justify-center dark:bg-gray-800 shadow-xs rounded-lg p-5">
            <div class="p-6">
                <form @submit.prevent="submit">

                    <VInput label="Name" v-model="form.name" :error="form.errors.name" />

                    <div class="my-4">
                        <label class="block text-sm font-medium mb-1">
                            Bundesländer
                        </label>

                        <!-- States -->
                        <Multiselect v-model="selectedStates" :options="stateOptions" label="name" track-by="id"
                            :multiple="true" placeholder="Bundesländer auswählen" :close-on-select="false"
                            :clear-on-select="false" :preserve-search="true" />

                        <p v-if="form.errors.state_ids" class="text-red-500 text-sm mt-1">
                            {{ form.errors.state_ids }}
                        </p>
                    </div>
                    <div class="my-4">
                        <label class="block text-sm font-medium mb-1">
                            Fische
                        </label>

                        <!-- Fish -->
                        <Multiselect v-model="selectedFish" :options="fishOptions" label="name" track-by="id"
                            :multiple="true" placeholder="Fische auswählen" :close-on-select="false"
                            :clear-on-select="false" :preserve-search="true" />

                        <p v-if="form.errors.fish_ids" class="text-red-500 text-sm mt-1">
                            {{ form.errors.state_ids }}
                        </p>
                    </div>

                    <VTextarea class="mt-4" label="Beschreibung" v-model="form.desc" :error="form.errors.desc" />
                    <VTextarea class="my-4" label="Tipps" v-model="form.hint" :error="form.errors.hint" />

                    <div class="flex items-center justify-between">
                        <VButton type="submit" :disabled="form.processing">Speichern</VButton>
                        <VButton type="button" variant="danger" @click="destroy">Löschen</VButton>
                    </div>

                </form>
            </div>
        </div>
    </PageWrapper>
</template>

<script setup>
import Multiselect from 'vue-multiselect'
import VButton from '@/components/VButton.vue'
import VInput from '@/components/VInput.vue'
import VTextarea from '@/components/VTextarea.vue'
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue'

import { useForm } from '@inertiajs/vue3'
import { Inertia } from '@inertiajs/inertia'
import { ref } from 'vue'

const props = defineProps({
    river: Object,
    states: Array,
    fish: Array,
})

// Das eigentliche Form-Objekt mit IDs für das Backend
const form = useForm({
    name: props.river.name,
    desc: props.river.desc,
    hint: props.river.hint,
    states: props.river.states.map(s => s.id),
    fish: props.river.fish.map(s => s.id),
})

const selectedStates = ref(props.river.states ? [...props.river.states] : [])
const selectedFish = ref(props.river.fish ? [...props.river.fish] : [])

// Optionen
const stateOptions = props.states || []
const fishOptions = props.fish || []

function submit() {
    form.states = selectedStates.value.map(s => s.id)
    form.fish = selectedFish.value.map(f => f.id)

    form.put(route('admin.river.update', props.river.id))
}
function destroy() {
    if (confirm(`Möchtest du den Fluss "${props.river.name}" wirklich löschen?`)) {
        Inertia.delete(route('admin.river.destroy', props.river.id))
    }
}
</script>
<style scoped>
    /* =========================
   Vue Multiselect - Global Override
   Farbe: #118bf0
   Dark Mode kompatibel
   ========================= */

/* Grundstil */
.multiselect {
  border: 1px solid #118bf0;
  border-radius: 0.375rem; /* optional: abgerundete Ecken */
  color: #111; /* Standard Textfarbe */
  background-color: #fff;
  transition: border-color 0.2s, box-shadow 0.2s;
}

/* Fokuszustand */
.multiselect--active {
  border-color: #118bf0;
  box-shadow: 0 0 0 2px rgba(17, 139, 240, 0.2);
}

/* Input & Single Value */
.multiselect__input,
.multiselect__single {
  color: inherit;
}

/* Placeholder */
.multiselect__placeholder {
  color: #118bf0;
  opacity: 0.7;
}

/* Dropdown Pfeil */
.multiselect__select:before {
  border-color: #118bf0 transparent transparent;
}

/* Optionen */
.multiselect__option--highlight {
  background-color: #118bf0;
  color: #fff;
}

.multiselect__option--selected {
  background-color: rgba(17, 139, 240, 0.15);
  color: #118bf0;
  font-weight: 600;
}

/* Tags (Multiple Select) */
.multiselect__tag {
  background-color: #118bf0;
  color: #fff;
}

.multiselect__tag-icon:after {
  color: #fff;
}

.multiselect__tag-icon:hover {
  background-color: rgba(0,0,0,0.1);
}

/* =========================
   Dark Mode
   ========================= */
.dark .multiselect {
  background-color: #1f2937; /* dunkles Grau */
  border-color: #118bf0;
  color: #e5e7eb; /* helles Grau */
}

.dark .multiselect__input,
.dark .multiselect__single {
  color: #e5e7eb;
}

.dark .multiselect__placeholder {
  color: #118bf0;
  opacity: 0.6;
}

.dark .multiselect__content-wrapper {
  background-color: #1f2937;
  color: #e5e7eb;
}

.dark .multiselect__option--highlight {
  background-color: #118bf0;
  color: #fff;
}

.dark .multiselect__option--selected {
  background-color: rgba(17, 139, 240, 0.25);
  color: #118bf0;
}

.dark .multiselect__tag {
  background-color: #118bf0;
  color: #fff;
}

</style>