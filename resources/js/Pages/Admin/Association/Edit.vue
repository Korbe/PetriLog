<template>
    <PageWrapper title="Verein bearbeiten" backTo="/admin/association">
        <div class="w-full bg-white flex flex-col justify-center dark:bg-gray-800 shadow-xs rounded-lg p-5">
            <div class="p-6">
                <form @submit.prevent="submit">

                    <VInput label="Name" v-model="form.name" :error="form.errors.name" />
                    <div class="my-4">
                        <label class="block text-sm font-medium mb-1">
                            Bundesland
                        </label>

                        <!-- States -->
                        <Multiselect v-model="form.state" :options="stateOptions" label="name" track-by="id"
                            :multiple="false" placeholder="Bundesland auswählen" :close-on-select="true"
                            :clear-on-select="false" :preserve-search="true" />

                        <p v-if="form.errors.state_ids" class="text-red-500 text-sm mt-1">
                            {{ form.errors.state_ids }}
                        </p>
                    </div>
                    <VInput label="Link" v-model="form.link" :error="form.errors.link" />
                    <VTextarea class="mt-4" label="Beschreibung" v-model="form.desc" :error="form.errors.desc" />

                    <div class="flex items-center justify-between">
                        <VButton type="submit" :disabled="form.processing">
                            Speichern
                        </VButton>

                        <VButton type="button" variant="danger" @click="destroy">
                            Löschen
                        </VButton>
                    </div>

                </form>
            </div>
        </div>
    </PageWrapper>
</template>

<script setup>
import VButton from '@/components/VButton.vue';
import VInput from '@/components/VInput.vue';
import VTextarea from '@/components/VTextarea.vue';
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
import { useForm } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';
import Multiselect from 'vue-multiselect'

const props = defineProps({
    association: Object,
    states: Array,
});

// Form initialisieren mit bestehenden Daten
const form = useForm({
    name: props.association.name,
    desc: props.association.desc,
    link: props.association.link,
    state: props.association.state || null,
    state_id: props.association.state ? props.association.state.id : null,
});

const stateOptions = props.states || []

function submit() {
    if (!form.state) {
        alert('Bitte wähle ein Bundesland aus!');
        return;
    }

    form.state_id = form.state.id;
    form.put(route('admin.association.update', props.association.id));
}

function destroy() {
    if (confirm(`Möchtest du den Verein "${props.association.name}" wirklich löschen?`)) {
        Inertia.delete(route('admin.association.destroy', props.association.id));
    }
}
</script>