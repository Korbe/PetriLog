<template>
    <div class="space-y-5">
        <VDateTimePicker v-model="props.modelValue.date" label="Datum" mandatory />


        <label class="block text-md md:text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">
            Fischart<span class="text-red-500"> *</span>
        </label>

        <Multiselect v-model="selectedFish" :options="fish" label="name" track-by="id" placeholder="Fisch auswählen" />

        <div v-if="errors?.fish_id" class="text-xs mt-1 text-red-500">
            {{ errors.fish_id }}
        </div>

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
import { ref, computed } from 'vue'
import VDateTimePicker from '@/components/VDateTimePicker.vue'
import VInput from '@/components/VInput.vue'
import Multiselect from 'vue-multiselect'
import { waters } from '../config.js'

const props = defineProps({
    modelValue: Object,
    errors: Object,
    fish: Array,
})

const emit = defineEmits(['update:modelValue'])

const showCustomWatersField = ref(false)

/**
 * Verbindung Multiselect ↔ fish_id
 */
const selectedFish = computed({
    get() {
        return props.fish.find(f => f.id === props.modelValue.fish_id) ?? null
    },
    set(value) {
        emit('update:modelValue', {
            ...props.modelValue,
            fish_id: value?.id ?? null,
        })
    },
})
</script>

<style scoped>
.multiselect__tags {
    border: 5px solid red;
}
</style>