<template>

    <label v-if="label" :for="id" class="block text-md md:text-sm font-medium text-gray-700 mb-1">
        {{ label }}
        <span v-if="mandatory" class="text-red-500">*</span>
    </label>

    <div class="relative">
        <flat-pickr v-model="modelValue" :config="config" :id="id" :name="name" :disabled="disabled"
            class="form-input pl-9 dark:bg-gray-800 text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100 font-medium w-full" />
        <div class="absolute inset-0 right-auto flex items-center pointer-events-none">
            <svg class="fill-current text-gray-400 dark:text-gray-500 ml-3" width="16" height="16" viewBox="0 0 16 16">
                <path d="M5 4a1 1 0 0 0 0 2h6a1 1 0 1 0 0-2H5Z" />
                <path
                    d="M4 0a4 4 0 0 0-4 4v8a4 4 0 0 0 4 4h8a4 4 0 0 0 4-4V4a4 4 0 0 0-4-4H4ZM2 4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V4Z" />
            </svg>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import FlatPickr from 'vue-flatpickr-component'

const props = defineProps({
    modelValue: [String, Date],
    label: String,
    id: {
        type: String,
        default: 'datetime-picker'
    },
    name: {
        type: String,
        default: 'datetime'
    },
    mandatory: {
        type: Boolean,
        default: false
    },
    disabled: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['update:modelValue'])

watch(
    () => props.modelValue,
    (val) => {
        if (val !== modelValue.value) modelValue.value = val
    }
)

const modelValue = ref(props.modelValue)

watch(modelValue, (val) => {
    emit('update:modelValue', val)
})

const config = {
    enableTime: true,
    dateFormat: 'd.m.Y H:i',
    time_24hr: true
}
</script>