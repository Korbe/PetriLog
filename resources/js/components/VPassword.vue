<template>
    <div>
        <!-- Label -->
        <div class="flex items-center justify-between" v-if="label">
            <label :for="id" class="block text-sm font-medium mb-1.5 text-(--app-ink)">
                {{ label }}
                <span v-if="mandatory" class="text-(--app-float)">*</span>
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
                <Link v-if="showForgot && !modelValue" href="/forgot-password"
                    class="text-sm font-medium text-(--app-accent-ink) hover:underline whitespace-nowrap">
                Vergessen?
                </Link>

                <!-- Toggle Auge -->
                <button v-else type="button" class="focus:outline-none" @click="toggleVisible"
                    :aria-label="visible ? 'Passwort verbergen' : 'Passwort anzeigen'">
                    <EyeIcon v-if="!visible" class="w-5 h-5 text-(--app-subtle) hover:text-(--app-ink)" />
                    <EyeSlashIcon v-else class="w-5 h-5 text-(--app-subtle) hover:text-(--app-ink)" />
                </button>
            </div>
        </div>

        <!-- Fehler / Hinweis -->
        <div v-if="error" class="text-xs mt-1.5 font-medium text-(--app-danger)">{{ error }}</div>
        <div v-if="success" class="text-xs mt-1.5 font-medium text-(--app-success)">{{ success }}</div>
        <div v-if="supportingText" class="text-xs mt-1.5 text-(--app-muted)">{{ supportingText }}</div>
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
        'border-(--app-danger)!': props.error,
        'border-(--app-success)!': props.success,
        'cursor-not-allowed': props.disabled,
    }
})
</script>
