<template>
    <button @click="$emit('click')"
        class="cursor-pointer btn px-2.5 bg-white dark:bg-gray-800 border-gray-200 hover:border-gray-300 dark:border-gray-700/60 dark:hover:border-gray-600 text-gray-400 dark:text-gray-500">
        <span class="sr-only">Reset</span>
        <ArrowUturnLeftIcon class="h-5" />
    </button>
</template>

<script setup lang="ts">
import { ArrowUturnLeftIcon } from '@heroicons/vue/24/outline'

defineEmits<{
    (e: 'click'): void
}>()
</script>