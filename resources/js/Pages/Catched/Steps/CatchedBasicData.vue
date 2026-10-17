<template>
    <CatchSpeciesWaterFields v-model:fish-id="props.modelValue.fish_id" v-model:lake-id="props.modelValue.lake_id"
        v-model:river-id="props.modelValue.river_id" :fish="fish" :lakes="lakes" :rivers="rivers" :states="states"
        :errors="errors" />
</template>

<script setup>
import CatchSpeciesWaterFields from '@/components/CatchSpeciesWaterFields.vue'

// Wie die übrigen Schritte schreibt die Komponente direkt in das Formularobjekt des Assistenten
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
