<template>
    <PageWrapper title="See hinzufügen" backTo="/admin/lake">
        <div class="w-full bg-white flex flex-col justify-center dark:bg-gray-800 shadow-xs rounded-lg p-5">

            <div class="p-6">
                <form @submit.prevent="submit">

                    <VInput label="Name" v-model="form.name" :error="form.errors.name" />
                    <div class="my-4">
                        <label class="block text-sm font-medium mb-1">
                            Bundesländer
                        </label>

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
                    <VTextarea class="my-4" label="Beschreibung" v-model="form.desc" :error="form.errors.desc" />
                    <VTextarea class="my-4" label="Tipps" v-model="form.hint" :error="form.errors.desc" />

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
import VTextarea from '@/components/VTextarea.vue';
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
import Multiselect from 'vue-multiselect'

import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue';

const props = defineProps({
    states: Array,  // State-Objekte aus DB
    fish: Array,
});

const form = useForm({
    name: '',
    desc: '',
    hint: '',
    states: [],
    fish: [],
});

const selectedStates = ref([]);
const selectedFish = ref([]);

const stateOptions = props.states;
const fishOptions = props.fish;

function submit() {
    form.states = selectedStates.value.map(s => s.id),
    form.fish = selectedFish.value.map(f => f.id),

    form.post(route('admin.lake.store'));
}
</script>