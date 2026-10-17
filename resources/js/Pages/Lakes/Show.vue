<template>
    <WaterDetail :water="lake" kind="lake" :state-id="state_id" />
</template>
<script setup>
import WaterDetail from '@/components/WaterDetail.vue';
import { onMounted } from 'vue';

defineProps({
    state_id: [String, Number],
    lake: Object
});

onMounted(() => {
    sessionStorage.setItem('lastOrigin', window.location.href);
});
</script>
