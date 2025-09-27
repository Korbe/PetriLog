<template>
    <div class="space-y-5">
        <VDateTimePicker v-model="props.modelValue.date" label="Datum" mandatory />


        <label class="block text-md md:text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">
            Fish Art<span class="text-red-500"> *</span>
        </label>
        <multiselect v-model="props.modelValue.name" :options="fishSpecies" placeholder=""></multiselect>
        <div v-if="errors?.name" class="text-xs mt-1 text-red-500">{{ errors?.name }}</div>

        <span class="block text-sm font-medium mb-5" v-if="!showCustomFishField"
            @click="showCustomFishField = true">
            Dein Fisch ist nicht dabei? Klick hier
        </span>

        <VInput v-if="showCustomFishField" label="Gib deine Fisch Art ein" v-model="props.modelValue.name"
            :error="errors?.name" />



            
        <label class="block text-md md:text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">
            Gewässer<span class="text-red-500"> *</span>
        </label>
        <multiselect v-model="props.modelValue.waters" :options="waters" placeholder=""></multiselect>
        <div v-if="errors?.waters" class="text-xs mt-1 text-red-500">{{ errors?.waters }}</div>

        <span class="block text-sm font-medium mb-5" v-if="!showCustomWatersField"
            @click="showCustomWatersField = true">
            Dein Gewässer ist nicht dabei? Klick hier
        </span>

        <VInput v-if="showCustomWatersField" label="Gib dein Gewässer ein" v-model="props.modelValue.waters"
            :error="errors?.waters" />

        <VInput label="Länge (Centimeter)" type="number" mandatory v-model="props.modelValue.length"
            :error="errors?.length" />
        <VInput label="Gewicht (Gramm)" type="number" v-model="props.modelValue.weight" :error="errors?.weight" />
    </div>
</template>
<script setup>
import { ref } from 'vue';
import VDateTimePicker from '@/components/VDateTimePicker.vue';
import VInput from '@/components/VInput.vue';
import { fishSpecies, waters } from '../config.js';
import Multiselect from 'vue-multiselect'

const props = defineProps({
    modelValue: Object,
    errors: Object
})

const emit = defineEmits(['update:modelValue'])

const showCustomWatersField = ref(false);
const showCustomFishField = ref(false);


</script>

<style scoped>
.multiselect__tags {
    border: 5px solid red;
}
</style>