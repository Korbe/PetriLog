<template>
  <div class="flex flex-wrap justify-center gap-2">
    <div v-for="(item, index) in props.modelValue" :key="index"
      class="w-28 flex flex-col items-center border p-1 rounded-md">
      <img :src="getUrl(item, index)" class="w-20 h-20 object-cover rounded-md border mb-2" alt="Preview" />
      <div class="text-center font-medium truncate w-full" :title="getName(item)">
        {{ getName(item) }}
      </div>
      <div class="text-center text-xs text-gray-500 mb-2 w-full">
        {{ getSize(item) }}
      </div>
      <button
        class="px-3 py-1 bg-gray-800 hover:bg-gray-700 rounded-md font-semibold text-xs text-white uppercase w-full"
        type="button" @click="$emit('remove', item)">
        Entfernen
      </button>
    </div>
  </div>
</template>

<script setup>
import { onBeforeUnmount, ref, watch } from 'vue';

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['remove']);

// Vorschau-URLs (nur für File Objekte)
const previews = ref([]);

watch(() => props.modelValue, () => {
  previews.value.forEach(url => URL.revokeObjectURL(url));
  previews.value = [];
}, { deep: true });

const getUrl = (item, index) => {
  if (item instanceof File) {
    if (!previews.value[index]) {
      previews.value[index] = URL.createObjectURL(item);
    }
    return previews.value[index];
  }
  return item.original_url;
};

const getName = (item) => item.name || item.file_name || 'Bild';

const getSize = (item) => {
  const size = item.size || 0;
  const i = Math.floor(Math.log(size) / Math.log(1024));
  return (size / Math.pow(1024, i)).toFixed(2) + ' ' + ['B', 'kB', 'MB', 'GB', 'TB'][i];
};

onBeforeUnmount(() => {
  previews.value.forEach((url) => URL.revokeObjectURL(url));
});
</script>