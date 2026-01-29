<template>
    <div class="space-y-5">
        <VDateTimePicker v-model="props.modelValue.date" label="Datum" mandatory />
        <VInput label="Länge (cm)" type="number" mandatory v-model="props.modelValue.length" :error="errors?.length" />
        <VInput label="Gewicht (g)" type="number" v-model="props.modelValue.weight" :error="errors?.weight" />
    </div>
</template>

<script setup>
import VDateTimePicker from '@/components/VDateTimePicker.vue';
import VInput from '@/components/VInput.vue'

const props = defineProps({
    modelValue: Object,
    errors: Object,
})
const emit = defineEmits(['update:modelValue'])
</script>