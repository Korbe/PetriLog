<template>
    <PageWrapper title="Verein hinzufügen" backTo="/admin/association">
        <div class="ui-card mx-auto max-w-4xl p-5 md:p-8">

            <div>
                <form @submit.prevent="submit">

                    <VInput label="Name" v-model="form.name" :error="form.errors.name" />
                    <div class="my-4">
                        <label class="block text-sm font-medium mb-1.5 text-(--app-ink)">
                            Bundesland
                        </label>

                        <VCombobox v-model="form.state_id" :options="stateOptions" label="name" track-by="id"
                            placeholder="Bundesland auswählen" search-placeholder="Bundesland suchen …" aria-label="Bundesland" />

                        <p v-if="form.errors.state_ids" class="text-xs mt-1.5 font-medium text-(--app-danger)">
                            {{ form.errors.state_ids }}
                        </p>
                    </div>
                    <VInput label="Link" v-model="form.link" :error="form.errors.link" />
                    <VEditor label="Beschreibung" v-model="form.desc" :error="form.errors.desc" />

                    <VButton type="submit" :disabled="form.processing">
                        Speichern
                    </VButton>
                </form>
            </div>
        </div>
    </PageWrapper>
</template>
<script setup>
import VButton from '@/components/VButton.vue';
import VInput from '@/components/VInput.vue';
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
import { useForm } from '@inertiajs/vue3'
import VEditor from '@/components/VEditor.vue';
import VCombobox from '@/components/VCombobox.vue';

const props = defineProps({
    states: Array,
});

const form = useForm({
    name: '',
    desc: '',
    link: '',
    state_id: null,
});

const stateOptions = props.states;

function submit() {
    form.state_id = form.state_id['id'];
    form.post(route('admin.association.store'));
}
</script>