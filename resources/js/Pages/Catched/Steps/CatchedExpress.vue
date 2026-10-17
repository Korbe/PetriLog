<template>
    <div class="space-y-5">
        <VDateTimePicker v-model="props.modelValue.date" label="Datum" mandatory />
        <div class="grid gap-4 sm:grid-cols-2">
            <VInput label="Länge (cm)" type="number" mandatory v-model="props.modelValue.length" :error="errors?.length" />
            <VInput label="Gewicht (g)" type="number" v-model="props.modelValue.weight" :error="errors?.weight" />
        </div>
        <ReleaseToggle v-model="props.modelValue.released" label="Verbleib" block />

        <CatchSpeciesWaterFields v-model:fish-id="props.modelValue.fish_id" v-model:lake-id="props.modelValue.lake_id"
            v-model:river-id="props.modelValue.river_id" :fish="fish" :lakes="lakes" :rivers="rivers" :states="states"
            :errors="errors" />
    </div>
</template>

<script setup>
import VDateTimePicker from '@/components/VDateTimePicker.vue'
import VInput from '@/components/VInput.vue'
import ReleaseToggle from '@/components/ReleaseToggle.vue'
import CatchSpeciesWaterFields from '@/components/CatchSpeciesWaterFields.vue'

const props = defineProps({
    modelValue: Object,
    errors: Object,
    fish: Array,
    lakes: Array,
    rivers: Array,
    states: Array,
})

const emit = defineEmits(['update:modelValue'])
</script>
