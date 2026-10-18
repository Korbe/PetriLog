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
    BoltIcon,
    ArrowLeftIcon,
    ArrowRightIcon,
    CloudIcon,
} from '@heroicons/vue/24/outline';

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
    states: Array,
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
    released: null,
    weather_notify: rememberedWeatherNotify(),
    photos: [],
    media: [],
});

/* E-Mail, sobald die Wetterdaten da sind – letzte Wahl merken */
function rememberedWeatherNotify() {
    try { return localStorage.getItem('petrilog-weather-notify') === '1'; } catch { return false; }
}
watch(() => form.value.weather_notify, (value) => {
    try { localStorage.setItem('petrilog-weather-notify', value ? '1' : '0'); } catch { /* egal */ }
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

const wizardOptions = [
    {
        type: 'express',
        title: 'Express-Eintrag',
        text: 'Schnell direkt am Wasser – nur das Nötigste.',
        points: ['Datum, Länge & Gewicht', 'Fischart & Gewässer', 'Position & Fotos'],
        cta: 'Express starten',
        icon: BoltIcon,
        tone: 'bg-[color-mix(in_srgb,var(--app-float)_14%,transparent)] text-(--app-float)',
    },
    {
        type: 'normal',
        title: 'Normaler Eintrag',
        text: 'Alle Details für dein vollständiges Fangbuch.',
        points: ['Alles aus dem Express-Eintrag', 'Tiefe, Wassertemperatur & Köder', 'Bemerkungen'],
        cta: 'Normal starten',
        icon: AdjustmentsHorizontalIcon,
        tone: 'bg-(--app-accent-soft) text-(--app-accent-ink)',
    },
];

const stepProps = computed(() => {
  const step = activeSteps.value[currentStep.value].component

  if (step === CatchedBasicData || step === CatchedExpress) {
    return {
      fish: props.fish,
      lakes: props.lakes,
      rivers: props.rivers,
      states: props.states,
    }
  }

  return {}
})
</script>

<template>
    <PageWrapper title="Fang eintragen" :eyebrow="wizardType ? (wizardType === 'express' ? 'Express-Eintrag' : 'Normaler Eintrag') : 'Petri Heil!'" :backTo="props.backToUrl">

        <!-- =========================
             Wizard Auswahl
        ========================== -->
        <div v-if="wizardType === null" class="mx-auto grid max-w-4xl gap-5 md:grid-cols-2 md:gap-6">

            <button v-for="option in wizardOptions" :key="option.type" type="button" @click="selectWizard(option.type)"
                class="ui-card ui-card-hover group flex flex-col p-6 text-left md:p-8">
                <span class="flex size-12 items-center justify-center rounded-2xl" :class="option.tone">
                    <component :is="option.icon" class="size-6" />
                </span>
                <span class="font-display mt-5 text-4xl leading-tight">{{ option.title }}</span>
                <span class="ui-muted mt-2">{{ option.text }}</span>
                <ul class="mt-5 space-y-1.5 text-sm text-(--app-muted)">
                    <li v-for="point in option.points" :key="point" class="flex items-center gap-2">
                        <CheckIcon class="size-4 shrink-0 text-(--app-success)" /> {{ point }}
                    </li>
                </ul>
                <span class="mt-7 inline-flex items-center gap-2 self-start rounded-full bg-primary-500 px-5 py-2.5 text-sm font-semibold text-white transition group-hover:bg-primary-600">
                    {{ option.cta }} <ArrowRightIcon class="size-4 transition group-hover:translate-x-0.5" />
                </span>
            </button>

        </div>


        <!-- =========================
             Wizard
        ========================== -->
        <div v-else class="mx-auto max-w-3xl">

            <!-- Progress -->
            <div class="mb-5">
                <div class="flex items-center justify-between gap-3 text-sm">
                    <span class="font-semibold">{{ activeSteps[currentStep].name }}</span>
                    <span class="font-log text-xs text-(--app-subtle)">Schritt {{ currentStep + 1 }} von {{ activeSteps.length }}</span>
                </div>
                <div class="mt-3 flex gap-1.5" aria-hidden="true">
                    <span v-for="(step, index) in activeSteps" :key="step.id" class="h-1.5 flex-1 rounded-full transition-colors"
                        :class="index <= currentStep ? 'bg-primary-500' : 'bg-(--app-border-strong)'"></span>
                </div>
                <ol class="mt-3 hidden gap-1.5 md:flex">
                    <li v-for="(step, index) in activeSteps" :key="step.id" class="flex flex-1 items-center gap-1.5 text-xs"
                        :class="index === currentStep ? 'font-semibold text-(--app-ink)' : 'text-(--app-subtle)'">
                        <CheckIcon v-if="index < currentStep" class="size-3.5 text-(--app-success)" />
                        <span v-else class="font-log">{{ step.id }}</span>
                        {{ step.name }}
                    </li>
                </ol>
            </div>

            <div class="ui-card p-5 md:p-8">
                <!-- Error -->
                <div v-if="alertMessage" class="mb-5 rounded-2xl bg-(--app-danger-soft) px-4 py-3 text-sm font-medium text-(--app-danger)" role="alert">
                    {{ alertMessage }}
                </div>

                <!-- Step Content -->
                <component v-if="!loading" :is="activeSteps[currentStep].component" v-model="form" :errors="errors"
                    v-bind="stepProps"/>

                <FullLoadingScreen v-if="loading" />

                <!-- Wetter-Benachrichtigung (letzter Schritt) -->
                <label v-if="!loading && currentStep === activeSteps.length - 1"
                    class="mt-8 flex cursor-pointer items-start gap-3 rounded-2xl border border-(--app-border) bg-(--app-surface-2) p-4 transition hover:border-(--app-border-strong)">
                    <input type="checkbox" class="form-checkbox mt-0.5" v-model="form.weather_notify" />
                    <span class="min-w-0">
                        <span class="flex items-center gap-1.5 text-sm font-semibold text-(--app-ink)">
                            <CloudIcon class="size-4 text-(--app-accent)" /> Benachrichtigen, wenn die Wetterdaten da sind
                        </span>
                        <span class="mt-0.5 block text-xs text-(--app-muted)">
                            Temperatur, Luftdruck und Wind zum Fangzeitpunkt kommen etwa 1–2 Stunden nach dem Fang.
                            Wir schicken dir dann eine E-Mail.
                        </span>
                    </span>
                </label>

                <!-- Navigation -->
                <div class="mt-8 flex items-center justify-between gap-3 border-t border-(--app-border) pt-5">
                    <VButton v-if="currentStep > 0" variant="secondary" @click="prevStep">
                        <ArrowLeftIcon class="size-4" /> Zurück
                    </VButton>
                    <VButton v-else variant="ghost" @click="wizardType = null">
                        <ArrowLeftIcon class="size-4" /> Art wechseln
                    </VButton>

                    <VButton @click="nextStep" :size="currentStep === activeSteps.length - 1 ? 'lg' : 'md'">
                        <template v-if="currentStep === activeSteps.length - 1"><CheckIcon class="size-5" /> Fang speichern</template>
                        <template v-else>Weiter <ArrowRightIcon class="size-4" /></template>
                    </VButton>
                </div>
            </div>
        </div>
    </PageWrapper>
</template>
