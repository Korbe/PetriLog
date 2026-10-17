<template>
  <div>
    <!-- Info -->
    <p class="text-center text-sm text-(--app-muted) mb-5">
      Bis zu <strong class="text-(--app-ink)">3 Fotos</strong> – das erste wird zum Titelbild. Reihenfolge per Ziehen ändern.
    </p>

    <!-- Vorschau + Drag & Drop -->
    <div class="flex flex-wrap justify-center gap-2 min-h-[80px]">
      <draggable v-model="localItems" item-key="uid" :animation="150" ghost-class="ghost-preview" handle=".drag-handle"
        @end="onDragEnd" class="flex flex-wrap justify-center gap-2">
        <template #item="{ element }">
          <div class="w-28 flex flex-col items-center gap-1 rounded-2xl border border-(--app-border) bg-(--app-surface) p-1.5 shadow-(--app-shadow)">

            <!-- Drag Handle (nur dieser Bereich ist ziehbar) -->
            <div class="drag-handle cursor-move w-full">
              <img :src="element.url" class="w-full aspect-square object-cover rounded-xl" alt="Vorschau" />
            </div>

            <div v-if="element.name" class="text-center font-medium truncate w-full text-xs mb-1">
              {{ element.name }}
            </div>

            <div v-if="element.size" class="text-center text-[10px] text-(--app-subtle) w-full">
              {{ formatSize(element.size) }}
            </div>

            <button type="button"
              class="w-full rounded-full px-3 py-1.5 text-xs font-semibold text-(--app-danger) transition hover:bg-(--app-danger-soft)"
              @click="removeItem(element)">
              Entfernen
            </button>

          </div>
        </template>
      </draggable>

      <!-- Plus-Karte zum Upload -->
      <div v-if="localItems.length < 3"
        class="w-28 aspect-[4/5] flex flex-col items-center justify-center gap-1 rounded-2xl border-2 border-dashed border-(--app-border-strong) bg-(--app-surface-2) cursor-pointer text-(--app-muted) transition hover:border-primary-500 hover:text-(--app-accent-ink)"
        @click="triggerFileInput">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24"
          stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        <div class="text-xs font-semibold">Foto hinzufügen</div>
      </div>

      <!-- Unsichtbares File Input -->
      <input type="file" ref="fileInput" class="hidden" multiple @change="onFileChange" accept="image/*" />
    </div>

    <!-- Fehleranzeige -->
    <template v-for="(err, idx) in photoErrors" :key="idx">
      <div v-if="err" class="text-xs mt-2 text-center font-medium text-(--app-danger)">
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