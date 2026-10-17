<template>
    <RecordCatchCard kicker="Schwerster Fang" :catched="catched" :route="route" :value="weightValue" :unit="weightUnit" />
</template>

<script setup>
import { computed } from 'vue'
import RecordCatchCard from './RecordCatchCard.vue'

const props = defineProps({
    catched: Object,
    route: String,
})

// weight is stored in gramm – show kg from 1000 g on
const weightValue = computed(() => {
    const g = Number(props.catched?.weight ?? 0)
    return g >= 1000 ? (g / 1000).toLocaleString('de-AT', { maximumFractionDigits: 2 }) : g
})
const weightUnit = computed(() => (Number(props.catched?.weight ?? 0) >= 1000 ? 'kg' : 'g'))
</script>
