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

                    <VEditor class="my-4" label="Beschreibung" v-model="form.desc" :error="form.errors.desc" />
                    <VEditor class="my-4" label="Tipps" v-model="form.hint" :error="form.errors.hint" />

                    <VEditor class="my-4" label="Fischereirechte" v-model="form.fishing_rights" :error="form.errors.fishing_rights" />
                    <VEditor class="my-4" label="Ticketverkäufe" v-model="form.ticket_sales" :error="form.errors.ticket_sales" />

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
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue'

import { useForm } from '@inertiajs/vue3'
import { Inertia } from '@inertiajs/inertia'
import { ref } from 'vue'
import VEditor from '@/components/VEditor.vue'

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
    fishing_rights: props.river.fishing_rights,
    ticket_sales: props.river.ticket_sales,
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