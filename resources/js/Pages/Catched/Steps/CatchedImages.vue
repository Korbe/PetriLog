<template>
  <div>
    <!-- Info -->
    <p class="text-center block text-md font-medium text-gray-700 dark:text-gray-400 mb-5">
      Lade maximal drei Bilder hoch
    </p>

    <!-- Vorschau + Drag & Drop -->
    <div class="flex flex-wrap justify-center gap-2 min-h-[80px]">
      <draggable v-model="localItems" item-key="uid" :animation="150" ghost-class="ghost-preview" handle=".drag-handle"
        @end="onDragEnd" class="flex flex-wrap justify-center gap-2">
        <template #item="{ element }">
          <div class="w-28 flex flex-col items-center border p-1 rounded-md bg-white dark:bg-gray-800">

            <!-- Drag Handle (nur dieser Bereich ist ziehbar) -->
            <div class="drag-handle cursor-move mb-2">
              <img :src="element.url" class="w-20 h-20 object-cover rounded-md border" alt="Preview" />
            </div>

            <div v-if="element.name" class="text-center font-medium truncate w-full text-xs mb-1">
              {{ element.name }}
            </div>

            <div v-if="element.size" class="text-center text-[10px] text-gray-500 mb-2 w-full">
              {{ formatSize(element.size) }}
            </div>

            <button type="button"
              class="px-3 py-1 bg-gray-800 hover:bg-gray-700 rounded-md font-semibold text-xs text-white uppercase w-full"
              @click="removeItem(element)">
              Entfernen
            </button>

          </div>
        </template>
      </draggable>

      <!-- Plus-Karte zum Upload -->
      <div v-if="localItems.length < 3"
        class="w-28 h-28 flex flex-col items-center justify-center border-dashed border-2 rounded-md cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700"
        @click="triggerFileInput">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24"
          stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        <div class="text-xs text-gray-500 mt-1">Upload</div>
      </div>

      <!-- Unsichtbares File Input -->
      <input type="file" ref="fileInput" class="hidden" multiple @change="onFileChange" accept="image/*" />
    </div>

    <!-- Fehleranzeige -->
    <template v-for="(err, idx) in photoErrors" :key="idx">
      <div v-if="err" class="text-xs mt-1 text-red-500">
        {{ err }}
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onBeforeUnmount, computed } from 'vue'
import draggable from 'vuedraggable'

const props = defineProps({
  modelValue: Object, // { photos: [] }
  errors: Object
})

const emit = defineEmits(['update:modelValue', 'remove'])

const localItems = ref([])      // { file, uid, name, size, url }
const previews = ref([])

// Photo-Fehler
const photoErrors = computed(() => {
  return Array.from({ length: 3 }, (_, i) => props.errors?.[`photos.${i}`] ?? null)
})

// Neues File bauen
const buildItem = (file) => {
  const url = URL.createObjectURL(file)
  previews.value.push(url)
  return {
    file, // wichtig: echte File-Objekte behalten
    uid: `file-${Math.random().toString(36).substr(2, 9)}`,
    name: file.name,
    size: file.size,
    url,
  }
}

// File Input Trigger
const fileInput = ref(null)
const triggerFileInput = () => fileInput.value?.click()

// Dateien auswählen / hinzufügen
const onFileChange = (e) => {
  const files = Array.from(e.target.files)
  const availableSlots = 3 - localItems.value.length
  if (availableSlots <= 0) { e.target.value = ''; return }

  const filesToAdd = files.slice(0, availableSlots).map(buildItem)
  localItems.value.push(...filesToAdd)

  // Nur echte File-Objekte an Parent weitergeben
  emit('update:modelValue', { ...props.modelValue, photos: localItems.value.map(i => i.file) })

  e.target.value = ''
}

// Drag & Drop Ende
const onDragEnd = () => {
  emit('update:modelValue', {
    ...props.modelValue,
    photos: localItems.value.map(i => i.file),
  })
}

// Einzelnes Item entfernen
const removeItem = (item) => {
  localItems.value = localItems.value.filter(i => i.uid !== item.uid)
  emit('update:modelValue', { ...props.modelValue, photos: localItems.value.map(i => i.file) })
}

// Dateigrößen formatieren
const formatSize = (bytes) => {
  if (!bytes) return null
  const i = Math.floor(Math.log(bytes) / Math.log(1024))
  return (bytes / Math.pow(1024, i)).toFixed(2) + [' B', ' kB', ' MB'][i]
}

// ObjectURLs freigeben
onBeforeUnmount(() => previews.value.forEach(URL.revokeObjectURL))
</script>

<style scoped>
.ghost-preview {
  opacity: 0.5;
  border: 2px dashed #9ca3af;
  background-color: white;
}

.cursor-move {
  cursor: grab;
}

.cursor-move:active {
  cursor: grabbing;
}
</style>