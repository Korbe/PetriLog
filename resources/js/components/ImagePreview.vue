<template>
  <div v-if="items.length" class="flex flex-wrap justify-center gap-2">
    <div v-for="(item, index) in items" :key="index" class="w-28 flex flex-col items-center border p-1 rounded-md">
      <img :src="item.url" class="w-20 h-20 object-cover rounded-md border mb-2" alt="Preview" />

      <div v-if="item.name" class="text-center font-medium truncate w-full">
        {{ item.name }}
      </div>

      <div v-if="item.size" class="text-center text-xs text-gray-500 mb-2 w-full">
        {{ formatSize(item.size) }}
      </div>

      <button type="button"
        class="px-3 py-1 bg-gray-800 hover:bg-gray-700 rounded-md font-semibold text-xs text-white uppercase w-full"
        @click="$emit('remove', item)">
        Entfernen
      </button>
    </div>
  </div>
</template>
<script setup>
import { computed, onBeforeUnmount, ref } from 'vue'

const props = defineProps({
  modelValue: {
    type: [File, Array, Object, String, null],
    default: null,
  },
  existing: {
    type: [Object, Array, String, null],
    default: null,
  },
})

defineEmits(['remove'])

const previews = ref([])

/**
 * 🔁 ALLES → einheitliche Preview-Items
 */
const items = computed(() => {
  const result = []

  const push = (item) => {
    if (!item) return

    // ✅ echtes File (Upload)
    if (item instanceof File) {
      const url = URL.createObjectURL(item)
      previews.value.push(url)

      result.push({
        file: item,
        url,
        name: item.name,
        size: item.size,
        readonly: false,
      })
      return
    }

    // ✅ Spatie Media Object
    if (item.original_url) {
      result.push({
        file: null,
        url: item.original_url,
        name: item.file_name,
        size: item.size,
        readonly: true,
        id: item.id,
      })
      return
    }

    // ✅ reine URL
    if (typeof item === 'string') {
      result.push({
        file: null,
        url: item,
        readonly: true,
      })
    }
  }

  const normalize = (value) => {
    if (Array.isArray(value)) value.forEach(push)
    else push(value)
  }

  normalize(props.modelValue)
  normalize(props.existing)

  return result
})

const formatSize = (bytes) => {
  if (!bytes) return null
  const i = Math.floor(Math.log(bytes) / Math.log(1024))
  return (bytes / Math.pow(1024, i)).toFixed(2) + [' B', ' kB', ' MB'][i]
}

onBeforeUnmount(() => {
  previews.value.forEach(URL.revokeObjectURL)
})
</script>