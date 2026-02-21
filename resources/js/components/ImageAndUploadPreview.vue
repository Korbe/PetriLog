<template>
  <div class="flex gap-2 flex-wrap">
    <draggable v-model="localList" item-key="key" class="flex gap-2 flex-wrap" @end="onDragEnd">
      <template #item="{ element }">
        <div class="w-28 flex flex-col items-center border p-1 rounded-md cursor-move bg-white dark:bg-gray-800">
          <img :src="element.url || element.original_url" class="w-20 h-20 object-cover rounded-md border mb-2" />
          <div v-if="element.name" class="text-center font-medium truncate w-full text-xs mb-1">
            {{ element.name }}
          </div>
          <div v-if="element.size" class="text-center text-[10px] text-gray-500 mb-2 w-full">
            {{ formatSize(element.size) }}
          </div>
          <button type="button"
            class="px-3 py-1 bg-gray-800 hover:bg-gray-700 rounded-md font-semibold text-xs text-white uppercase w-full"
            @click="remove(element)">
            Entfernen
          </button>
        </div>
      </template>
    </draggable>

    <!-- Plus-Karte -->
    <div v-if="localList.length < 3"
      class="w-28 h-28 flex flex-col items-center justify-center border-dashed border-2 rounded-md cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700"
      @click="triggerFileInput">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24"
        stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
      </svg>
      <div class="text-xs text-gray-500 mt-1">Upload</div>
    </div>

    <input ref="fileInput" type="file" multiple accept="image/*" class="hidden" @change="onFileChange" />
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import draggable from 'vuedraggable'
import { v4 as uuidv4 } from 'uuid'

const props = defineProps({
  modelValue: { type: Array, default: () => [] }
})
const emit = defineEmits(['update:modelValue', 'remove'])

const localList = ref([...props.modelValue])
const fileInput = ref(null)

// Synchronisiere nur **wenn Parent sich wirklich ändert**, nicht bei jedem Render
watch(() => props.modelValue, val => {
  localList.value = [...val]
})

function remove(element) {
  localList.value = localList.value.filter(e => e.key !== element.key)
  emit('update:modelValue', [...localList.value])
  emit('remove', element)
}

function triggerFileInput() {
  fileInput.value.click()
}

function onFileChange(event) {
  const files = Array.from(event.target.files).map(f => {
    const uuid = uuidv4()
    return {
      file: f,
      name: f.name,
      size: f.size,
      url: URL.createObjectURL(f),
      type: 'new',
      key: `file-${uuid}`,
      _uuid: uuid
    }
  })
  localList.value.push(...files)
  emit('update:modelValue', [...localList.value])
  event.target.value = ''
}

function formatSize(size) {
  if (!size) return ''
  if (size < 1024) return size + ' B'
  if (size < 1024 * 1024) return (size / 1024).toFixed(1) + ' KB'
  return (size / (1024 * 1024)).toFixed(1) + ' MB'
}

function onDragEnd() {
  emit('update:modelValue', [...localList.value])
}
</script>