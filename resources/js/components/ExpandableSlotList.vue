<template>
    <div v-if="items && items.length">
        <p v-if="title" class="font-bold text-lg pt-5 pb-2">
            {{ title }}
        </p>

        <ul>
            <li v-for="item in visibleItems" :key="item.id" class="hover:bg-gray-100 dark:hover:bg-gray-700 rounded">
                <!-- Scoped Slot -->
                <slot :item="item" />
            </li>
        </ul>

        <p v-if="items.length > limit && !expanded"
            class="text-sm text-blue-500 hover:text-blue-700 cursor-pointer mt-2" @click="expanded = true">
            weiterlesen
        </p>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
    items: {
        type: Array,
        required: true
    },
    title: {
        type: String,
        default: ''
    },
    limit: {
        type: Number,
        default: 3
    }
})

const expanded = ref(false)

const visibleItems = computed(() =>
    expanded.value ? props.items : props.items.slice(0, props.limit)
)


</script>