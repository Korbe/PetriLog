<template>
    <div>
        <p v-if="label" class="block text-sm font-medium mb-1.5 text-(--app-ink)">{{ label }}</p>
        <div class="inline-flex gap-1 rounded-full bg-(--app-surface-2) p-1" :class="{ 'flex w-full': block }" role="radiogroup"
            :aria-label="label || 'Verbleib'">
            <button v-for="option in options" :key="option.label" type="button" role="radio" :aria-checked="modelValue === option.value"
                :disabled="disabled"
                class="flex flex-1 items-center justify-center gap-1.5 whitespace-nowrap rounded-full font-semibold transition disabled:opacity-50"
                :class="[
                    compact ? 'px-2.5 py-1 text-xs' : 'px-4 py-2 text-sm',
                    modelValue === option.value ? option.active : 'text-(--app-muted) hover:text-(--app-ink)',
                ]"
                @click="select(option.value)">
                <component :is="option.icon" :class="compact ? 'size-3.5' : 'size-4'" />
                {{ option.label }}
            </button>
        </div>
        <p v-if="hint" class="text-xs mt-1.5 text-(--app-muted)">{{ hint }}</p>
    </div>
</template>

<script setup>
import { ArrowUturnLeftIcon, ShoppingBagIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    // true = zurückgesetzt, false = entnommen, null = keine Angabe
    modelValue: { type: Boolean, default: null },
    label: { type: String, default: '' },
    hint: { type: String, default: '' },
    compact: { type: Boolean, default: false },
    block: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue'])

const options = [
    { value: false, label: 'Entnommen', icon: ShoppingBagIcon, active: 'bg-(--app-accent) text-white shadow-(--app-shadow)' },
    { value: true, label: 'Zurückgesetzt', icon: ArrowUturnLeftIcon, active: 'bg-(--app-success) text-white shadow-(--app-shadow)' },
]

// Erneutes Antippen der aktiven Option setzt die Angabe zurück
const select = (value) => emit('update:modelValue', props.modelValue === value ? null : value)
</script>
