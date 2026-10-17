<template>
  <div v-if="items.length" class="flex flex-wrap justify-center gap-2">
    <div v-for="(item, index) in items" :key="index" class="w-28 flex flex-col items-center gap-1 rounded-2xl border border-(--app-border) bg-(--app-surface) p-1.5 shadow-(--app-shadow)">
      <img :src="item.url" class="w-full aspect-square object-cover rounded-xl" alt="Vorschau" />

      <div v-if="item.name" class="text-center text-xs font-medium truncate w-full px-1">
        {{ item.name }}
      </div>

      <div v-if="item.size" class="text-center text-[10px] text-(--app-subtle) w-full">
        {{ formatSize(item.size) }}
      </div>

      <button type="button"
        class="w-full rounded-full px-3 py-1.5 text-xs font-semibold text-(--app-danger) transition hover:bg-(--app-danger-soft)"
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