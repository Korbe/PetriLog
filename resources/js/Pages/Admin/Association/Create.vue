<template>
    <PageWrapper title="Verein hinzufügen" backTo="/admin/association">
        <div class="w-full bg-white flex flex-col justify-center dark:bg-gray-800 shadow-xs rounded-lg p-5">

            <div class="p-6">
                <form @submit.prevent="submit">

                    <VInput label="Name" v-model="form.name" :error="form.errors.name" />
                    <div class="my-4">
                        <label class="block text-sm font-medium mb-1">
                            Bundesland
                        </label>

                        <VMultiselect v-model="form.state_id" :options="stateOptions" label="name" track-by="id"
                            :multiple="false" placeholder="Bundesland auswählen" :close-on-select="true"
                            :clear-on-select="false" :preserve-search="true" />

                        <p v-if="form.errors.state_ids" class="text-red-500 text-sm mt-1">
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
import VMultiselect from '@/components/VMultiselect.vue';

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