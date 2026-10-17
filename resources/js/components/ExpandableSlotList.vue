<template>
    <div v-if="items && items.length">
        <div v-if="title" class="flex items-baseline justify-between gap-3 pb-2 pt-6">
            <h3 class="text-base font-semibold text-(--app-ink)">{{ title }}</h3>
            <span class="ui-badge">{{ items.length }}</span>
        </div>

        <ul class="-mx-2">
            <li v-for="item in visibleItems" :key="item.id">
                <!-- Scoped Slot -->
                <slot :item="item" />
            </li>
        </ul>

        <button v-if="items.length > limit" type="button"
            class="mt-2 inline-flex items-center gap-1 text-sm font-semibold text-(--app-accent-ink) hover:underline"
            @click="expanded = !expanded">
            {{ expanded ? 'Weniger anzeigen' : `Alle ${items.length} anzeigen` }}
        </button>
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
