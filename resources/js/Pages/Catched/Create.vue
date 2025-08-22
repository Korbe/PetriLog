<script setup>
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
import CatchedBasicData from './Steps/CatchedBasicData.vue';
import CatchedAdvancedData from './Steps/CatchedAdvancedData.vue';
import CatchedRemark from './Steps/CatchedRemark.vue';
import CatchedImages from './Steps/CatchedImages.vue';
import CatchedPosition from './Steps/CatchedPosition.vue';
import { onMounted, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { CheckIcon } from '@heroicons/vue/24/solid';

const props = defineProps({
    errors: Object,
})

const currentStep = ref(0);
const alertMessage = ref('');


const steps = [
    { id: '01', name: 'Einfach', component: CatchedBasicData },
    { id: '02', name: 'Erweitert', component: CatchedAdvancedData },
    { id: '03', name: 'Bemerkungen', component: CatchedRemark },
    { id: '04', name: 'Fotos', component: CatchedImages },
    { id: '05', name: 'Position', component: CatchedPosition },
]

const form = ref({
    name: null,
    length: null,
    weight: null,
    depth: null,
    temperature: null,
    waters: null,
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

function nextStep() {
    if (currentStep.value < steps.length - 1) {
        currentStep.value++
    } else {
        const formProxy = useForm(form.value)
        formProxy.post(route('catched.store'))
    }
}

function prevStep() {
    if (currentStep.value > 0) {
        currentStep.value--
    }
}

watch(
    () => props.errors,
    (newErrors) => {
        if (newErrors && Object.keys(newErrors).length > 0) {
            currentStep.value = 0;
            alertMessage.value = 'Es sind Fehler im Formular aufgetreten. Bitte überprüfe deine Eingaben.'

            // Optional: nach 5 Sekunden ausblenden
            setTimeout(() => {
                alertMessage.value = ''
            }, 5000)
        }
    }
)

</script>

<template>
    <PageWrapper title="Fang eintragen" :backTo="route('catched.index')">

        <div class="bg-white p-5 rounded-lg">
            <!-- Progress Steps -->
            <nav class="hidden lg:block mb-5" aria-label="Progress">
                <ol role="list"
                    class="divide-y divide-gray-300 rounded-md border border-gray-300 md:flex md:divide-y-0">
                    <li v-for="(step, index) in steps" :key="step.id" class="relative md:flex md:flex-1">
                        <span class="flex items-center px-6 py-4 text-sm font-medium" :class="{
                            'text-gray-500': index > currentStep,
                            'text-primary-500': index === currentStep,
                            'text-gray-900': index < currentStep
                        }">
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-full" :class="{
                                'bg-primary-500 text-white': index < currentStep,
                                'border-2 border-primary-500': index === currentStep,
                                'border-2 border-gray-300 text-gray-500': index > currentStep
                            }">
                                <template v-if="index < currentStep">
                                    <CheckIcon class="size-6 text-white" />
                                </template>
                                <template v-else>{{ step.id }}</template>
                            </span>
                            <span class="ml-4">{{ step.name }}</span>
                        </span>
                    </li>
                </ol>
            </nav>

            <div v-if="alertMessage" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-5">
                {{ alertMessage }}
            </div>


            <!-- Form Wizard Step Content -->
            <div class="max-w-xl mx-auto">
                <component :is="steps[currentStep].component" v-model="form" :errors="errors" />

                <!-- Navigation Buttons -->
                <div class="mt-6 flex justify-between">
                    <button class="cursor-pointer px-4 py-2 bg-gray-200 rounded" :disabled="currentStep === 0"
                        @click="prevStep">
                        Zurück
                    </button>
                    <button class="cursor-pointer px-4 py-2 bg-primary-500 text-white rounded" @click="nextStep">
                        {{ currentStep === steps.length - 1 ? 'Fertig' : 'Weiter' }}
                    </button>
                </div>
            </div>

        </div>

    </PageWrapper>
</template>
