<template>
    <VTextarea v-model="props.modelValue.remark" label="Bemerkungen"></VTextarea>
</template>
<script setup>
import VTextarea from '@/components/VTextarea.vue';

const props = defineProps({
    modelValue: Object,
    errors: Object
})

const emit = defineEmits(['update:modelValue'])

</script>