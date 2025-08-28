<template>
    <PageWrapper title="Fehler melden" backTo="/dashboard">
        <div class="md:w-1/2 m-auto space-y-4 bg-white dark:bg-gray-800 rounded-lg p-5">

            <VInput label="Titel" v-model="form.title" placeholder="Kurze Fehlerbeschreibung" :error="errors.title" />

            <VTextarea label="Beschreibung" v-model="form.description" placeholder="Detaillierte Fehlerbeschreibung"
                :rows=5 />

            <VSelect label="Kategorie" v-model="form.category" :options="[
                { label: 'UI', value: 'ui' },
                { label: 'Performance', value: 'performance' },
                { label: 'Datenproblem', value: 'data_issue' },
                { label: 'Sonstiges', value: 'other' }
            ]" />

            <VTextarea label="Schritte zur Reproduktion (optional)" v-model="form.steps" :rows=4
                placeholder="Schritt-für-Schritt-Anleitung" />

            <VInput label="Betroffene Seite / URL (optional)" v-model="form.url" placeholder="/dashboard" />

            <VButton @click="submitReport" class="w-full">Fehler melden</VButton>
        </div>
    </PageWrapper>
</template>

<script setup>
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
import VInput from '@/components/VInput.vue';
import VTextarea from '@/components/VTextarea.vue';
import VSelect from '@/components/VSelect.vue';
import VButton from '@/components/VButton.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    errors: Object,
})

const form = useForm({
    title: '',
    description: '',
    category: '',
    steps: '',
    url: ''
});

const submitReport = () => {
    form.post("/bug-report", {
        onSuccess: () => {
            console.log("onSuccess");
            form.reset(); // setzt automatisch alle Felder auf den initialen Wert zurück
        },
        onError: (errors) => {
            console.log("Fehler:", errors);
        }
    });
}
</script>