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

                        <VMultiselect v-model="selectedStates" :options="stateOptions" label="name" track-by="id"
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
                        <VMultiselect v-model="selectedFish" :options="fishOptions" label="name" track-by="id"
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
import { ref } from 'vue';
import VEditor from '@/components/VEditor.vue';
import VMultiselect from '@/components/VMultiselect.vue';

const props = defineProps({
    states: Array,  // State-Objekte aus DB
    fish: Array,
});

const form = useForm({
    name: '',
    desc: '',
    hint: '',
    fishing_rights: '',
    ticket_sales: '',
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