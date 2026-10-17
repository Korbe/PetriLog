<script setup>
import { ref } from 'vue'

const props = defineProps({
  title: String,
  id: String,
  active: Boolean,
})

const accordionOpen = ref(props.active)
</script>

<template>
  <div class="border-b border-(--ink)/10 first:border-t">
    <h3>
      <button :id="`accordion-title-${id}`" type="button"
        class="group flex w-full items-center justify-between gap-6 py-6 text-left"
        @click="accordionOpen = !accordionOpen" :aria-expanded="accordionOpen" :aria-controls="`accordion-text-${id}`">
        <span class="text-lg font-semibold transition-colors group-hover:text-primary-700">{{ title }}</span>
        <span
          class="relative flex size-8 shrink-0 items-center justify-center rounded-full border border-(--ink)/15 transition-colors group-hover:border-(--ink)/40"
          aria-hidden="true">
          <span class="absolute h-px w-3 bg-current"></span>
          <span class="absolute h-3 w-px bg-current transition-transform duration-300"
            :class="accordionOpen ? 'scale-y-0' : ''"></span>
        </span>
      </button>
    </h3>
    <div :id="`accordion-text-${id}`" role="region" :aria-labelledby="`accordion-title-${id}`"
      class="grid overflow-hidden transition-all duration-300 ease-in-out"
      :class="accordionOpen ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0'">
      <div class="overflow-hidden">
        <p class="max-w-2xl pb-6 leading-relaxed text-(--ink)/70">
          <slot />
        </p>
      </div>
    </div>
  </div>
</template>
