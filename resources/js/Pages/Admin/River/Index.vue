<template>
    <WaterAdminIndex type="river" :items="rivers" :states="states" />
</template>

<script setup>
import WaterAdminIndex from '../partials/WaterAdminIndex.vue'

defineProps({
    rivers: { type: Array, default: () => [] },
    states: { type: Array, default: () => [] },
})
</script>
