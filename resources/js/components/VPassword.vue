<template>
    <div>
        <!-- Label -->
        <div class="flex items-center justify-between" v-if="label">
            <label :for="id" class="block text-md md:text-sm font-medium mb-1 dark:text-gray-400">
                {{ label }}
                <span v-if="mandatory" class="text-red-500">*</span>
            </label>
        </div>

        <div class="relative">
            <!-- Input -->
            <input :id="id" class="form-input w-full" :class="inputClass" :type="visible ? 'text' : 'password'"
                :placeholder="placeholder" :required="mandatory" :disabled="disabled" :value="modelValue"
                @input="$emit('update:modelValue', $event.target.value)" />

            <!-- Rechts: Vergessen? oder Icon -->
            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                <!-- Vergessen? -->
                <Link v-if="showForgot && !modelValue" :href="route('password.request')"
                    class="text-sm text-primary-600 hover:underline whitespace-nowrap">
                Vergessen?
                </Link>

                <!-- Toggle Auge -->
                <button v-else type="button" class="focus:outline-none" @click="toggleVisible">
                    <EyeIcon v-if="!visible" class="w-5 h-5 text-gray-400 hover:text-gray-600" />
                    <EyeSlashIcon v-else class="w-5 h-5 text-gray-400 hover:text-gray-600" />
                </button>
            </div>
        </div>

        <!-- Fehler / Hinweis -->
        <div v-if="error" class="text-xs mt-1 text-red-500">{{ error }}</div>
        <div v-if="success" class="text-xs mt-1 text-green-500">{{ success }}</div>
        <div v-if="supportingText" class="text-xs mt-1">{{ supportingText }}</div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { EyeIcon, EyeSlashIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    id: { type: String },
    label: { type: String, default: '' },
    placeholder: { type: String, default: '' },
    mandatory: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    error: { type: String, default: '' },
    success: { type: String, default: '' },
    supportingText: { type: String, default: '' },
    modelValue: { type: [String, Number], default: '' },
    showForgot: { type: Boolean, default: true }
})

const emit = defineEmits(['update:modelValue'])

const visible = ref(false)

const toggleVisible = () => {
    visible.value = !visible.value
}

const inputClass = computed(() => {
    return {
        'pr-16': true,
        'border-red-300': props.error,
        'border-green-300': props.success,
        'dark:disabled:placeholder:text-gray-600 disabled:border-gray-200 dark:disabled:border-gray-700 disabled:bg-gray-100 dark:disabled:bg-gray-800 disabled:text-gray-400 dark:disabled:text-gray-600 disabled:cursor-not-allowed shadow-none': props.disabled,
    }
})
</script>
