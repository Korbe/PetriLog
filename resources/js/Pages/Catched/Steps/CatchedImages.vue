<template>
  <p class="text-center block text-md font-medium text-gray-700 dark:text-gray-400 mb-5">Lade maximal drei Bilder hoch</p>
    <VPrettyFileInput v-if="canUploadMore" type="file" v-model="props.modelValue.photos" :multiple="true" :max="3" accept="image/*"
        class="block w-full focus:ring-brand-primary focus:border-brand-primary" />

    <div v-if="errors?.['photos.0']" class="text-xs mt-1 text-red-500">{{ errors['photos.0'] }}</div>
    <div v-if="errors?.['photos.1']" class="text-xs mt-1 text-red-500">{{ errors['photos.1'] }}</div>
    <div v-if="errors?.['photos.2']" class="text-xs mt-1 text-red-500">{{ errors['photos.2'] }}</div>


    <ImagePreview :modelValue="props.modelValue.photos" @remove="removeImage" />
</template>
<script setup>
import VFileInput from '@/components/VFileInput.vue';
import ImagePreview from '../ImagePreview.vue';
import { computed } from 'vue';
import VPrettyFileInput from '@/components/VPrettyFileInput.vue';

const props = defineProps({
    modelValue: Object,
    errors: Object
})

const emit = defineEmits(['update:modelValue'])

const canUploadMore = computed(() => {
  return (props.modelValue.photos?.length ?? 0) < 3;
});

const removeImage = (item) => {
  if (item instanceof File) {
    props.modelValue.photos = props.modelValue.photos.filter(photo => photo !== item);
  }
};
</script>