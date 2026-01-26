<template>
    <VEditor class="my-4" label="Bemerkungen" v-model="props.modelValue.remark"/>
</template>
<script setup>
import VEditor from '@/components/VEditor.vue';

const props = defineProps({
    modelValue: Object,
})

const emit = defineEmits(['update:modelValue'])

</script>