<template>
    <div>
        <label v-if="label" class="block text-sm font-medium mb-1">
            {{ label }}
        </label>

        <div ref="editor" class="bg-white"></div>

        <p v-if="error" class="text-red-500 text-sm mt-1">
            {{ error }}
        </p>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import 'quill/dist/quill.snow.css'

const props = defineProps({
    modelValue: { type: String, default: '' },
    label: { type: String, default: '' },
    error: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue'])

const editor = ref(null)
let quill = null

onMounted(async () => {
    // ⛔ SSR-GUARD
    if (typeof window === 'undefined') return

    // ✅ Lazy import
    const Quill = (await import('quill')).default

    quill = new Quill(editor.value, {
        theme: 'snow',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['link'],
                ['clean'],
            ],
        },
    })

    quill.root.innerHTML = props.modelValue ?? ''

    quill.on('text-change', () => {
        emit('update:modelValue', normalizeContent(quill.root.innerHTML))
    })
})

watch(
    () => props.modelValue,
    (value) => {
        if (quill && value !== quill.root.innerHTML) {
            quill.root.innerHTML = value || ''
        }
    }
)

function normalizeContent(html) {
    if (!html) return null

    const cleaned = html
        .replace(/<p><br><\/p>/gi, '')
        .replace(/<p>\s*<\/p>/gi, '')
        .replace(/<br>/gi, '')
        .replace(/&nbsp;/gi, '')
        .trim()

    return cleaned === '' ? null : html
}
</script>