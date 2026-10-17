<template>
    <div>
      <input
        ref="fileRef"
        :accept="accept"
        :multiple="multiple"
        class="hidden"
        type="file"
        @change="change"
      />
  
      <button
        class="flex w-full flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-(--app-border-strong) bg-(--app-surface-2) px-4 py-6 text-sm font-semibold text-(--app-muted) transition hover:border-primary-500 hover:text-(--app-accent-ink)"
        type="button"
        @click="browse"
      >
        <PaperClipIcon class="h-5" />
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
  