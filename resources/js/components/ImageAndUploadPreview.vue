<template>
  <div class="flex gap-2 flex-wrap">

    <!-- Draggable Bilderliste -->
    <draggable v-model="localList" item-key="key" class="flex gap-2 flex-wrap" handle=".handle" @end="onDragEnd">
      <template #item="{ element }">
        <div class="w-28 flex flex-col items-center border p-1 rounded-md bg-white dark:bg-gray-800">

          <!-- Drag Handle -->
          <div class="cursor-move mb-2 handle">
            <img :src="element.url || element.original_url" class="w-20 h-20 object-cover rounded-md border" />
          </div>

          <!-- Name & Größe -->
          <div v-if="element.name" class="text-center font-medium truncate w-full text-xs mb-1">
            {{ element.name }}
          </div>
          <div v-if="element.size" class="text-center text-[10px] text-gray-500 mb-2 w-full">
            {{ formatSize(element.size) }}
          </div>

          <!-- Entfernen Button -->
          <button type="button"
            class="px-3 py-1 bg-gray-800 hover:bg-gray-700 rounded-md font-semibold text-xs text-white uppercase w-full"
            @click="confirmRemove(element)">
            Entfernen
          </button>
        </div>
      </template>
    </draggable>

    <!-- Upload Karte -->
    <div v-if="localList.length < 3"
      class="w-28 h-28 flex flex-col items-center justify-center border-dashed border-2 rounded-md cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700"
      @click="triggerFileInput">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24"
        stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
      </svg>
      <div class="text-xs text-gray-500 mt-1">Upload</div>
    </div>

    <!-- Unsichtbares File Input -->
    <input ref="fileInput" type="file" multiple accept="image/*" class="hidden" @change="onFileChange" />

    <!-- =========================
         Remove Dialog
    ========================== -->
    <transition name="fade">
      <div v-if="toDelete" class="fixed inset-0 z-50 flex items-center justify-center">

        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/50" @click="toDelete = null" />

        <!-- Dialog -->
        <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6 z-10">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
            Bild wirklich löschen?
          </h3>
          <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
            Diese Aktion kann nicht rückgängig gemacht werden.
          </p>
          <div class="flex justify-end gap-2">
            <VButton variant="secondary" @click="toDelete = null">
              Abbrechen
            </VButton>
            <VButton variant="danger" @click="removeConfirmed">
              Löschen
            </VButton>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import draggable from 'vuedraggable'
import { v4 as uuidv4 } from 'uuid'
import { router } from '@inertiajs/vue3'
import VButton from './VButton.vue'

const props = defineProps({
  modelValue: { type: Array, default: () => [] }
})
const emit = defineEmits(['update:modelValue'])

const localList = ref([...props.modelValue])
const fileInput = ref(null)
const toDelete = ref(null) // aktuell markiertes Bild zum Löschen

// Props -> lokale Liste sync
watch(() => props.modelValue, val => {
  localList.value = [...val]
})

// =========================
// Funktionen
// =========================
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

// =========================
// Entfernen mit Dialog
// =========================
function confirmRemove(element) {
  toDelete.value = element
}

function removeConfirmed() {
  const element = toDelete.value
  localList.value = localList.value.filter(e => e.key !== element.key)
  emit('update:modelValue', [...localList.value])
  toDelete.value = null

  if (element.id) {
    router.delete(route('app.catched.photo.delete', element.id))
  }
}
</script>
<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>