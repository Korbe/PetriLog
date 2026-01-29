<template>
    <Multiselect
        ref="ms"
        v-bind="$attrs"
        v-model="model"
        :searchable="true"
        @open="onOpen"
    />
</template>
<script setup>
import { ref, computed } from 'vue'
import Multiselect from 'vue-multiselect'

const props = defineProps({
    modelValue: {
        required: true
    }
})

const emit = defineEmits(['update:modelValue'])

const model = computed({
    get: () => props.modelValue,
    set: v => emit('update:modelValue', v)
})

const ms = ref(null)

const isMobile = /android|iphone|ipad|ipod/i.test(navigator.userAgent)

function onOpen() {
    if (!isMobile) return

    requestAnimationFrame(() => {
        const input = ms.value?.$el.querySelector('input')
        if (!input) return

        input.setAttribute('readonly', 'true')

        input.addEventListener(
            'touchstart',
            () => {
                input.removeAttribute('readonly')
                input.focus()
            },
            { once: true }
        )
    })
}
</script>