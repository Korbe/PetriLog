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
                        <VMultiselect v-model="form.state" :options="stateOptions" label="name" track-by="id"
                            :multiple="false" placeholder="Bundesland auswählen" :close-on-select="true"
                            :clear-on-select="false" :preserve-search="true" />

                        <p v-if="form.errors.state_ids" class="text-red-500 text-sm mt-1">
                            {{ form.errors.state_ids }}
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
import Quill from 'quill'
import 'quill/dist/quill.snow.css'
import { onMounted, ref } from 'vue';
import VEditor from '@/components/VEditor.vue';
import VMultiselect from '@/components/VMultiselect.vue';

const props = defineProps({
    association: Object,
    states: Array,
});

const editor = ref(null);
const stateOptions = props.states || [];
let quill = null;

// Form initialisieren mit bestehenden Daten
const form = useForm({
    name: props.association.name,
    desc: props.association.desc,
    link: props.association.link,
    state: props.association.state || null,
    state_id: props.association.state ? props.association.state.id : null,
});

onMounted(() => {
    quill = new Quill(editor.value, {
        theme: 'snow',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['link'],
                ['clean']
            ]
        }
    })

    // Initialwert setzen (Edit-Form!)
    if (form.desc) {
        quill.root.innerHTML = form.desc
    }

    // Änderungen ins Inertia-Formular schreiben
    quill.on('text-change', () => {
        form.desc = quill.root.innerHTML
    })
})

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