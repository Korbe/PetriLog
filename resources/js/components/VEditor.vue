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
import Quill from 'quill'
import 'quill/dist/quill.snow.css'

const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    },
    label: {
        type: String,
        default: ''
    },
    error: {
        type: String,
        default: ''
    }
})

const emit = defineEmits(['update:modelValue'])

const editor = ref(null)
let quill = null

onMounted(() => {
    quill = new Quill(editor.value, {
        theme: 'snow',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['link'],
                ['clean']
            ]
        }
    })

    // Initialwert
    quill.root.innerHTML = props.modelValue || ''

    // Änderungen nach außen geben
    quill.on('text-change', () => {
        emit('update:modelValue', quill.root.innerHTML)
    })
})

// Falls v-model von außen geändert wird (z. B. Reset)
watch(
    () => props.modelValue,
    (value) => {
        if (quill && value !== quill.root.innerHTML) {
            quill.root.innerHTML = value || ''
        }
    }
)
</script>