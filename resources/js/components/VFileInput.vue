<template>
    <div class="border border-gray-300 rounded-md shadow-sm p-2 space-y-2">
      <input
        ref="fileRef"
        :accept="accept"
        :multiple="multiple"
        class="hidden"
        type="file"
        @change="change"
      />
  
      <button
        class="flex px-4 p-2 bg-gray-800 hover:bg-gray-700 rounded-md font-semibold text-xs text-white uppercase"
        type="button"
        @click="browse"
      >
        <PaperClipIcon class="h-4 text-gray-400 mr-2" />
        <span>Bilder auswählen</span>
      </button>
    </div>
  </template>
  
  <script setup>
  import { ref } from 'vue';
  import { PaperClipIcon } from "@heroicons/vue/24/outline";
  
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
  