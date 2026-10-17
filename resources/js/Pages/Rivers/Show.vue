<template>
    <WaterDetail :water="river" kind="river" :state-id="state_id" />
</template>
<script setup>
import WaterDetail from '@/components/WaterDetail.vue';
import { onMounted } from 'vue';

defineProps({
    state_id: [String, Number],
    river: Object
});

onMounted(() => {
    sessionStorage.setItem('lastOrigin', window.location.href);
});
</script>
