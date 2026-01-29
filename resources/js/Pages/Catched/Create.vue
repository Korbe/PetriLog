<script setup>
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';

import CatchedBasicData from './Steps/CatchedBasicData.vue';
import CatchedAdvancedData from './Steps/CatchedAdvancedData.vue';
import CatchedRemark from './Steps/CatchedRemark.vue';
import CatchedImages from './Steps/CatchedImages.vue';
import CatchedPosition from './Steps/CatchedPosition.vue';
import CatchedMeasurement from './Steps/CatchedMeasurement.vue';
import CatchedExpress from './Steps/CatchedExpress.vue';

import FullLoadingScreen from '@/components/FullLoadingScreen.vue';
import VButton from '@/components/VButton.vue';

import { computed, ref, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import {
    AdjustmentsHorizontalIcon,
    CheckIcon,
    ForwardIcon
} from '@heroicons/vue/24/solid';

/* ---------------------------------
   Props
--------------------------------- */
const props = defineProps({
    errors: Object,
    backToUrl: String,
    storeUrl: String,
    fish: Array,
    lakes: Array,
    rivers: Array,
});

/* ---------------------------------
   State
--------------------------------- */
const wizardType = ref(null); // null | 'express' | 'normal'
const currentStep = ref(0);
const loading = ref(false);
const alertMessage = ref('');

/* ---------------------------------
   User
--------------------------------- */
const page = usePage();
const user = computed(() => page.props.auth.user);

/* ---------------------------------
   Steps
--------------------------------- */
const normalSteps = [
    { id: '01', name: 'Maße', component: CatchedMeasurement },
    { id: '02', name: 'Basisdaten', component: CatchedBasicData },
    { id: '03', name: 'Position', component: CatchedPosition },
    { id: '04', name: 'Fotos', component: CatchedImages },
    { id: '05', name: 'Erweitert', component: CatchedAdvancedData },
    { id: '06', name: 'Bemerkungen', component: CatchedRemark },
];

const expressSteps = [
    { id: '01', name: 'Maße', component: CatchedExpress },
    { id: '02', name: 'Position', component: CatchedPosition },
    { id: '03', name: 'Fotos', component: CatchedImages },
];

const activeSteps = computed(() => {
    return wizardType.value === 'express'
        ? expressSteps
        : normalSteps;
});

/* ---------------------------------
   Form
--------------------------------- */
const form = ref({
    fish_id: null,
    lake_id: null,
    river_id: null,
    length: null,
    weight: null,
    depth: null,
    temperature: null,
    date: new Date(),
    latitude: null,
    longitude: null,
    address: null,
    remark: null,
    air_pressure: null,
    bait: null,
    photos: [],
    media: [],
});

/* ---------------------------------
   Wizard Logic
--------------------------------- */
function selectWizard(type) {
    wizardType.value = type;
    currentStep.value = 0;
}

function nextStep() {
    if (currentStep.value < activeSteps.value.length - 1) {
        currentStep.value++;
    } else {
        loading.value = true;

        const formProxy = useForm(form.value);
        formProxy.post(props.storeUrl, {
            onFinish: () => (loading.value = false),
        });
    }
}

function prevStep() {
    if (currentStep.value > 0) {
        currentStep.value--;
    }
}

/* ---------------------------------
   Error Handling
--------------------------------- */
watch(
    () => props.errors,
    (newErrors) => {
        if (newErrors && Object.keys(newErrors).length > 0) {
            currentStep.value = 0;
            alertMessage.value =
                'Es sind Fehler im Formular aufgetreten. Bitte überprüfe deine Eingaben.';
            setTimeout(() => (alertMessage.value = ''), 5000);
        }
    }
);
</script>

<template>
    <PageWrapper title="Fang eintragen" :backTo="props.backToUrl">

        <!-- =========================
             Wizard Auswahl
        ========================== -->
        <div v-if="wizardType === null" class="space-y-6">

            <!-- Express -->
            <div
                class="mx-auto w-full lg:w-1/3 bg-white dark:bg-gray-800 shadow-md hover:shadow-xl rounded-lg p-8 flex flex-col items-center text-center transition hover:scale-105">
                <ForwardIcon class="h-12 w-12 text-blue-500 mb-4" />
                <h2 class="text-2xl font-bold mb-2">Express Eintrag</h2>
                <p class="mb-5 text-gray-600 dark:text-gray-400">
                    Nur die nötigsten Daten
                </p>
                <VButton @click="selectWizard('express')">
                    Express starten
                </VButton>
            </div>

            <!-- Normal -->
            <div
                class="mx-auto w-full lg:w-1/3 bg-white dark:bg-gray-800 shadow-md hover:shadow-xl rounded-lg p-8 flex flex-col items-center text-center transition hover:scale-105">
                <AdjustmentsHorizontalIcon class="h-12 w-12 text-blue-500 mb-4" />
                <h2 class="text-2xl font-bold mb-2">Normaler Eintrag</h2>
                <p class="mb-5 text-gray-600 dark:text-gray-400">
                    Alle Details erfassen
                </p>
                <VButton @click="selectWizard('normal')">
                    Normal starten
                </VButton>
            </div>

        </div>

        <!-- =========================
             Wizard
        ========================== -->
        <div v-else class="bg-white dark:bg-gray-800 p-6 rounded-lg">

            <!-- Progress -->
            <nav class="hidden lg:block mb-6">
                <ol class="md:flex border rounded-md divide-y md:divide-y-0">
                    <li v-for="(step, index) in activeSteps" :key="step.id" class="flex-1">
                        <span class="flex items-center px-6 py-4 text-sm font-medium" :class="{
                            'text-gray-500': index > currentStep,
                            'text-primary-500': index === currentStep,
                            'text-gray-900': index < currentStep
                        }">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full mr-4" :class="{
                                'bg-primary-500 text-white': index < currentStep,
                                'border-2 border-primary-500': index === currentStep,
                                'border-2 border-gray-300 text-gray-400': index > currentStep
                            }">
                                <CheckIcon v-if="index < currentStep" class="h-5 w-5" />
                                <span v-else>{{ step.id }}</span>
                            </span>
                            {{ step.name }}
                        </span>
                    </li>
                </ol>
            </nav>

            <!-- Error -->
            <div v-if="alertMessage" class="mb-4 rounded bg-red-100 border border-red-400 px-4 py-3 text-red-700">
                {{ alertMessage }}
            </div>

            <!-- Step Content -->
            <div class="max-w-xl mx-auto">
                <component v-if="!loading" :is="activeSteps[currentStep].component" v-model="form" :errors="errors"
                    :fish="props.fish" :lakes="props.lakes" :rivers="props.rivers" />

                <FullLoadingScreen v-if="loading" />

                <!-- Navigation -->
                <div class="mt-6 flex justify-between">
                    <button class="px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded" :disabled="currentStep === 0"
                        @click="prevStep">
                        Zurück
                    </button>

                    <button class="px-4 py-2 bg-primary-500 text-white rounded" @click="nextStep">
                        {{ currentStep === activeSteps.length - 1 ? 'Fertig' : 'Weiter' }}
                    </button>
                </div>
            </div>
        </div>
    </PageWrapper>
</template>
