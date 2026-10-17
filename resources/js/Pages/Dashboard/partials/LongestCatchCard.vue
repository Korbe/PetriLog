<template>
  <RecordCatchCard kicker="Längster Fang" :catched="catched" :route="route" :value="catched?.length" unit="cm" />
</template>

<script setup>
import RecordCatchCard from './RecordCatchCard.vue'

defineProps({
  catched: Object,
  route: String,
})
</script>
