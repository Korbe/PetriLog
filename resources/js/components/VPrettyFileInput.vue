<template>
  <div class="flex justify-center m-5">
    <div @click="browse" class="w-40 h-40 p-10 flex justify-center items-center flex-col rounded-lg border-1">
      <div>
        <PhotoIcon class="h-28 text-gray-400 mr-2" />
        <span>Bilder auswählen</span>
      </div>
    </div>
  </div>

  <input ref="fileRef" :accept="accept" :multiple="multiple" class="hidden" type="file" @change="change" />
</template>

<script setup>
import { ref } from 'vue';
import { PhotoIcon } from '@heroicons/vue/24/solid';

const props = defineProps({
  modelValue: {
    type: [Array, File, Object, null],
    default: null
  },
  accept: String,
  multiple: {
    type: Boolean,
    default: false
  },
  max: {
    type: Number,
    default: Infinity
  }
});

const emit = defineEmits(['update:modelValue']);
const fileRef = ref(null);

const browse = () => {
  fileRef.value.click();
};

const change = (event) => {
  let selectedFiles = Array.from(event.target.files);
  if (props.multiple) {
    const current = Array.isArray(props.modelValue) ? props.modelValue : [];
    const remaining = Math.max(props.max - current.length, 0);
    const toAdd = selectedFiles.slice(0, remaining);
    emit('update:modelValue', [...current, ...toAdd]);
  } else {
    emit('update:modelValue', selectedFiles[0] || null);
  }
  fileRef.value.value = '';
};
</script>