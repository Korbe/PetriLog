<template>
    <PageWrapper title="Verein bearbeiten" backTo="/admin/association">
        <div class="ui-card mx-auto max-w-4xl p-5 md:p-8">
            <div>
                <form @submit.prevent="submit">

                    <VInput label="Name" v-model="form.name" :error="form.errors.name" />
                    <div class="my-4">
                        <label class="block text-sm font-medium mb-1.5 text-(--app-ink)">
                            Bundesland
                        </label>

                        <!-- States -->
                        <VCombobox v-model="form.state" :options="stateOptions" label="name" track-by="id"
                            placeholder="Bundesland auswählen" search-placeholder="Bundesland suchen …" aria-label="Bundesland" />

                        <p v-if="form.errors.state_id || form.errors.state_ids" class="text-xs mt-1.5 font-medium text-(--app-danger)">
                            {{ form.errors.state_id || form.errors.state_ids }}
                        </p>
                    </div>
                    <VInput label="Link" v-model="form.link" :error="form.errors.link" />

                    <VEditor label="Beschreibung" v-model="form.desc" :error="form.errors.desc" />

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
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
import { useForm } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';
import VEditor from '@/components/VEditor.vue';
import VCombobox from '@/components/VCombobox.vue';
import { confirmDialog } from '@/composables/useDialog';

const props = defineProps({
    association: Object,
    states: Array,
});

const stateOptions = props.states || [];

// Form initialisieren mit bestehenden Daten
const form = useForm({
    name: props.association.name,
    desc: props.association.desc,
    link: props.association.link,
    state: props.association.state || null,
    state_id: props.association.state ? props.association.state.id : null,
});

function submit() {
    form.clearErrors('state_id');
    if (!form.state) {
        form.setError('state_id', 'Bitte wähle ein Bundesland aus.');
        return;
    }

    form.state_id = form.state.id;
    form.put(route('admin.association.update', props.association.id));
}

async function destroy() {
    const confirmed = await confirmDialog({
        title: 'Verein löschen?',
        message: `„${props.association.name}“ wird dauerhaft aus dem Wiki entfernt.`,
        confirmText: 'Verein löschen',
        tone: 'danger',
    });
    if (confirmed) {
        Inertia.delete(route('admin.association.destroy', props.association.id));
    }
}
</script>