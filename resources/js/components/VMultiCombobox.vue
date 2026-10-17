<template>
  <div ref="root" class="relative" @keydown="onKeydown">
    <!-- Feld: gewählte Einträge als Chips -->
    <div
      class="flex min-h-[46px] w-full cursor-pointer items-center gap-2 rounded-[0.85rem] border bg-(--app-surface) py-1.5 pr-1.5 pl-2 transition"
      :class="open
        ? 'border-(--app-accent) shadow-[0_0_0_4px_var(--app-accent-soft)]'
        : 'border-(--app-border-strong) hover:border-[color-mix(in_srgb,var(--app-ink)_30%,transparent)]'"
      @click="toggle"
    >
      <div class="flex min-w-0 flex-1 flex-wrap items-center gap-1.5">
        <span
          v-for="item in modelValue"
          :key="item[trackBy]"
          class="inline-flex max-w-full items-center gap-1 rounded-full bg-(--app-accent-soft) py-1 pr-1 pl-2.5 text-[0.85rem] font-medium text-(--app-accent-ink)"
        >
          <span class="truncate">{{ item[label] }}</span>
          <button
            type="button"
            class="grid size-5 shrink-0 place-items-center rounded-full transition hover:bg-(--app-accent) hover:text-white"
            :aria-label="`${item[label]} entfernen`"
            @click.stop="remove(item)"
          >
            <svg class="size-3" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M4 4l8 8M12 4l-8 8" /></svg>
          </button>
        </span>
        <span v-if="!modelValue.length" class="px-1.5 py-1 text-[0.95rem] text-(--app-subtle)">{{ placeholder }}</span>
      </div>

      <button
        ref="trigger"
        type="button"
        class="grid size-8 shrink-0 place-items-center rounded-full text-(--app-muted) transition hover:bg-(--app-hover) hover:text-(--app-ink)"
        aria-haspopup="listbox"
        :aria-expanded="open"
        :aria-controls="listId"
        :aria-label="`${ariaLabel || placeholder} – ${modelValue.length} ausgewählt`"
        @click.stop="toggle"
      >
        <svg class="size-4 transition-transform" :class="open && 'rotate-180'" viewBox="0 0 16 16" fill="none"
          stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6l4 4 4-4" /></svg>
      </button>
    </div>

    <!-- Panel -->
    <transition
      enter-active-class="transition duration-150 ease-out"
      enter-from-class="opacity-0 -translate-y-1"
      leave-active-class="transition duration-100 ease-in"
      leave-to-class="opacity-0 -translate-y-1"
    >
      <div
        v-if="open"
        class="absolute inset-x-0 top-full z-30 mt-1.5 overflow-hidden rounded-[0.85rem] border border-(--app-border) bg-(--app-surface) shadow-(--app-shadow-lg)"
      >
        <!-- Suche -->
        <div class="relative border-b border-(--app-border)">
          <svg class="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-(--app-subtle)" viewBox="0 0 16 16"
            fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
            <circle cx="7" cy="7" r="4.5" /><path d="M10.5 10.5L14 14" />
          </svg>
          <input
            ref="search"
            v-model="query"
            type="text"
            class="w-full border-0 bg-transparent py-3 pr-3 pl-10 text-[0.95rem] text-(--app-ink) placeholder:text-(--app-subtle) focus:ring-0 focus:outline-none"
            :placeholder="searchPlaceholder"
            role="combobox"
            :aria-label="ariaLabel ? `${ariaLabel} suchen` : undefined"
            autocomplete="off"
            aria-autocomplete="list"
            :aria-controls="listId"
            :aria-expanded="true"
            :aria-activedescendant="activeIndex >= 0 ? optionId(activeIndex) : undefined"
          />
        </div>

        <!-- Optionen -->
        <ul :id="listId" ref="list" role="listbox" aria-multiselectable="true" :aria-label="ariaLabel || undefined"
          class="relative max-h-72 overflow-y-auto overscroll-contain p-1.5">
          <li
            v-for="(option, i) in filtered"
            :id="optionId(i)"
            :key="option[trackBy]"
            role="option"
            :aria-selected="isSelected(option)"
            class="flex cursor-pointer items-center gap-2.5 rounded-lg px-3 py-2.5 text-[0.925rem] transition-colors"
            :class="[
              i === activeIndex ? 'bg-(--app-hover)' : '',
              isSelected(option) ? 'font-semibold text-(--app-accent-ink)' : 'text-(--app-ink)',
            ]"
            @mousemove="activeIndex = i"
            @mousedown.prevent
            @click="toggleOption(option)"
          >
            <!-- Checkbox-Optik -->
            <span
              class="grid size-[1.1rem] shrink-0 place-items-center rounded-[0.35rem] border transition-colors"
              :class="isSelected(option) ? 'border-(--app-accent) bg-(--app-accent) text-white' : 'border-(--app-border-strong)'"
            >
              <svg v-if="isSelected(option)" class="size-3" viewBox="0 0 16 16" fill="none" stroke="currentColor"
                stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M3.5 8.5l3 3 6-7" /></svg>
            </span>
            <span class="min-w-0 flex-1 truncate">{{ option[label] }}</span>
          </li>

          <li v-if="!filtered.length" class="px-3 py-6 text-center text-sm text-(--app-muted)">
            Keine Treffer für „{{ query }}“
          </li>
        </ul>

        <!-- Fußzeile -->
        <div class="flex items-center justify-between gap-3 border-t border-(--app-border) px-4 py-2.5 text-xs">
          <span class="text-(--app-muted)">{{ modelValue.length }} ausgewählt</span>
          <div class="flex items-center gap-4">
            <button v-if="modelValue.length" type="button" class="font-semibold text-(--app-muted) hover:text-(--app-ink)"
              @click="emit('update:modelValue', [])">Alle entfernen</button>
            <button type="button" class="font-semibold text-(--app-accent-ink)" @click="close(true)">Fertig</button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, useId, watch } from 'vue'

/**
 * Mehrfachauswahl mit Suche (Admin: Bundesländer, Fischarten eines Gewässers).
 * v-model ist ein Array von Objekten aus `options`. Gegenstück zu VCombobox.
 */
const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  options: { type: Array, default: () => [] },
  label: { type: String, default: 'name' },
  trackBy: { type: String, default: 'id' },
  placeholder: { type: String, default: 'Bitte auswählen' },
  searchPlaceholder: { type: String, default: 'Suchen …' },
  // Name des Felds für Screenreader, wenn kein verknüpftes <label> existiert
  ariaLabel: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue'])

const root = ref(null)
const trigger = ref(null)
const search = ref(null)
const list = ref(null)
const open = ref(false)
const query = ref('')
const activeIndex = ref(-1)

const listId = `multicombobox-${useId()}`
const optionId = i => `${listId}-option-${i}`

const selectedKeys = computed(() => new Set(props.modelValue.map(item => item[props.trackBy])))
const isSelected = option => selectedKeys.value.has(option[props.trackBy])

// Groß/klein und Umlaute ignorieren: "karnten" findet "Kärnten"
const normalize = s => String(s ?? '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/ß/g, 'ss')

const filtered = computed(() => {
  const q = normalize(query.value.trim())
  if (!q) return props.options
  return props.options.filter(o => normalize(o[props.label]).includes(q))
})

watch(query, () => { activeIndex.value = filtered.value.length ? 0 : -1 })

const isTouch = typeof window !== 'undefined' && window.matchMedia?.('(pointer: coarse)').matches

async function openPanel() {
  if (open.value) return
  open.value = true
  query.value = ''
  await nextTick()
  activeIndex.value = filtered.value.length ? 0 : -1
  if (!isTouch) search.value?.focus()
}

function close(focusTrigger = false) {
  open.value = false
  if (focusTrigger) trigger.value?.focus()
}

const toggle = () => (open.value ? close() : openPanel())

function toggleOption(option) {
  const key = option[props.trackBy]
  emit('update:modelValue', isSelected(option)
    ? props.modelValue.filter(item => item[props.trackBy] !== key)
    : [...props.modelValue, option])
}

function remove(item) {
  emit('update:modelValue', props.modelValue.filter(i => i[props.trackBy] !== item[props.trackBy]))
}

function move(delta) {
  const n = filtered.value.length
  if (!n) return
  activeIndex.value = (activeIndex.value + delta + n) % n
  nextTick(() => {
    const el = document.getElementById(optionId(activeIndex.value))
    const box = list.value
    if (!el || !box) return
    // Nur die Liste scrollen, nicht die ganze Seite
    if (el.offsetTop < box.scrollTop) box.scrollTop = el.offsetTop
    else if (el.offsetTop + el.offsetHeight > box.scrollTop + box.clientHeight) box.scrollTop = el.offsetTop + el.offsetHeight - box.clientHeight
  })
}

function onKeydown(e) {
  if (!open.value) {
    if (['ArrowDown', 'ArrowUp'].includes(e.key)) {
      e.preventDefault()
      openPanel()
    }
    return
  }

  switch (e.key) {
    case 'ArrowDown': e.preventDefault(); move(1); break
    case 'ArrowUp': e.preventDefault(); move(-1); break
    case 'Enter':
      e.preventDefault()
      if (filtered.value[activeIndex.value]) toggleOption(filtered.value[activeIndex.value])
      break
    case 'Backspace':
      // Leere Suche + Backspace entfernt den zuletzt gewählten Eintrag
      if (!query.value && props.modelValue.length) remove(props.modelValue[props.modelValue.length - 1])
      break
    case 'Escape': e.preventDefault(); close(true); break
    case 'Tab': close(); break
  }
}

function onClickOutside(e) {
  if (open.value && !root.value?.contains(e.target)) close()
}

onMounted(() => document.addEventListener('pointerdown', onClickOutside))
onBeforeUnmount(() => document.removeEventListener('pointerdown', onClickOutside))
</script>
