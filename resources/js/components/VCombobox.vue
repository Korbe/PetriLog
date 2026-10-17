<template>
  <div ref="root" class="relative" @keydown="onKeydown">
    <!-- Feld -->
    <button
      ref="trigger"
      type="button"
      class="ui-combobox-field flex w-full items-center gap-2 rounded-[0.85rem] border bg-(--app-surface) py-[0.7rem] pr-2.5 pl-[0.9rem] text-left text-[0.95rem] leading-[1.4] transition"
      :class="open
        ? 'border-(--app-accent) shadow-[0_0_0_4px_var(--app-accent-soft)]'
        : 'border-(--app-border-strong) hover:border-[color-mix(in_srgb,var(--app-ink)_30%,transparent)]'"
      aria-haspopup="listbox"
      :aria-label="ariaLabel ? `${ariaLabel}: ${selected ? selected[label] : placeholder}` : undefined"
      :aria-expanded="open"
      :aria-controls="listId"
      @click="toggle"
    >
      <span class="min-w-0 flex-1 truncate" :class="selected ? 'text-(--app-ink)' : 'text-(--app-subtle)'">
        {{ selected ? selected[label] : placeholder }}
      </span>

      <span
        v-if="selected && clearable"
        role="button"
        tabindex="-1"
        class="grid size-6 shrink-0 place-items-center rounded-full text-(--app-subtle) transition hover:bg-(--app-hover) hover:text-(--app-ink)"
        aria-label="Auswahl entfernen"
        @click.stop="clear"
      >
        <svg class="size-3.5" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 4l8 8M12 4l-8 8" /></svg>
      </span>

      <svg class="size-4 shrink-0 text-(--app-muted) transition-transform" :class="open && 'rotate-180'" viewBox="0 0 16 16"
        fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <path d="M4 6l4 4 4-4" />
      </svg>
    </button>

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
        <ul :id="listId" ref="list" role="listbox" :aria-label="ariaLabel || undefined" class="relative max-h-72 overflow-y-auto overscroll-contain p-1.5">
          <li
            v-for="(option, i) in filtered"
            :id="optionId(i)"
            :key="option[trackBy]"
            role="option"
            :aria-selected="isSelected(option)"
            class="flex cursor-pointer items-center gap-2 rounded-lg px-3 py-2.5 text-[0.925rem] transition-colors"
            :class="[
              i === activeIndex ? 'bg-(--app-hover)' : '',
              isSelected(option) ? 'font-semibold text-(--app-accent-ink)' : 'text-(--app-ink)',
            ]"
            @mousemove="activeIndex = i"
            @mousedown.prevent
            @click="choose(option)"
          >
            <span class="min-w-0 flex-1 truncate">{{ option[label] }}</span>
            <svg v-if="isSelected(option)" class="size-4 shrink-0" viewBox="0 0 16 16" fill="none" stroke="currentColor"
              stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3.5 8.5l3 3 6-7" /></svg>
          </li>

          <li v-if="!filtered.length" class="px-3 py-6 text-center text-sm text-(--app-muted)">
            Keine Treffer für „{{ query }}“
          </li>
        </ul>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, useId, watch } from 'vue'

/**
 * Einzelauswahl mit Suche (Fischart, See, Fluss).
 * v-model ist das gewählte Objekt aus `options` oder null.
 */
const props = defineProps({
  modelValue: { type: Object, default: null },
  options: { type: Array, default: () => [] },
  label: { type: String, default: 'name' },
  trackBy: { type: String, default: 'id' },
  placeholder: { type: String, default: 'Bitte auswählen' },
  searchPlaceholder: { type: String, default: 'Suchen …' },
  clearable: { type: Boolean, default: true },
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

const listId = `combobox-${useId()}`
const optionId = i => `${listId}-option-${i}`

const selected = computed(() => props.modelValue)
const isSelected = option => selected.value?.[props.trackBy] === option[props.trackBy]

// Groß/klein und Umlaute ignorieren: "asche" findet "Äsche"
const normalize = s => String(s ?? '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/ß/g, 'ss')

const filtered = computed(() => {
  const q = normalize(query.value.trim())
  if (!q) return props.options
  return props.options.filter(o => normalize(o[props.label]).includes(q))
})

watch(query, () => { activeIndex.value = filtered.value.length ? 0 : -1 })

// Auf Touch-Geräten nicht automatisch die Tastatur öffnen – Scrollen in der Liste ist meist schneller
const isTouch = typeof window !== 'undefined' && window.matchMedia?.('(pointer: coarse)').matches

async function openPanel() {
  if (open.value) return
  open.value = true
  query.value = ''
  await nextTick()
  activeIndex.value = Math.max(0, props.options.findIndex(isSelected))
  if (!isTouch) search.value?.focus()
  // Gewählten Eintrag mittig zeigen, damit er nicht am Rand (oder unter der Bottom-Navigation) klebt
  scrollActiveIntoView('center')
}

function close(focusTrigger = false) {
  open.value = false
  if (focusTrigger) trigger.value?.focus()
}

const toggle = () => (open.value ? close() : openPanel())

function choose(option) {
  emit('update:modelValue', option)
  close(true)
}

function clear() {
  emit('update:modelValue', null)
  close(true)
}

function move(delta) {
  const n = filtered.value.length
  if (!n) return
  activeIndex.value = (activeIndex.value + delta + n) % n
  scrollActiveIntoView()
}

function scrollActiveIntoView(block = 'nearest') {
  nextTick(() => {
    const el = document.getElementById(optionId(activeIndex.value))
    const box = list.value
    if (!el || !box) return
    // Nur die Liste scrollen, nicht die ganze Seite
    if (block === 'center') box.scrollTop = el.offsetTop - box.clientHeight / 2 + el.offsetHeight / 2
    else if (el.offsetTop < box.scrollTop) box.scrollTop = el.offsetTop
    else if (el.offsetTop + el.offsetHeight > box.scrollTop + box.clientHeight) box.scrollTop = el.offsetTop + el.offsetHeight - box.clientHeight
  })
}

function onKeydown(e) {
  if (!open.value) {
    // Pfeiltasten oder Tippen auf dem Feld öffnen die Liste
    if (['ArrowDown', 'ArrowUp', 'Enter', ' '].includes(e.key)) {
      e.preventDefault()
      openPanel()
    } else if (e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
      e.preventDefault()
      const key = e.key
      openPanel().then(() => { query.value = key })
    }
    return
  }

  switch (e.key) {
    case 'ArrowDown': e.preventDefault(); move(1); break
    case 'ArrowUp': e.preventDefault(); move(-1); break
    case 'Enter':
      e.preventDefault()
      if (filtered.value[activeIndex.value]) choose(filtered.value[activeIndex.value])
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
