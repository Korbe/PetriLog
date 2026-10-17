<template>
    <button @click="$emit('click')"
        class="cursor-pointer inline-flex size-[2.75rem] items-center justify-center rounded-full border border-(--app-border-strong) bg-(--app-surface) text-(--app-muted) transition hover:bg-(--app-hover) hover:text-(--app-ink)" title="Zeitraum zurücksetzen">
        <span class="sr-only">Zeitraum zurücksetzen</span>
        <ArrowUturnLeftIcon class="h-5" />
    </button>
</template>

<script setup lang="ts">
import { ArrowUturnLeftIcon } from '@heroicons/vue/24/outline'

defineEmits<{
    (e: 'click'): void
}>()
</script>