<template>
    <div v-if="items && items.length">
        <div v-if="title" class="flex items-baseline justify-between gap-3 pb-2 pt-6">
            <h3 class="text-base font-semibold text-(--app-ink)">{{ title }}</h3>
            <span class="ui-badge">{{ items.length }}</span>
        </div>

        <ul class="-mx-2">
            <li v-for="item in visibleItems" :key="item.id">
                <Link :href="`${baseUrl}/${item.id}`" class="ui-row group justify-between">
                    <span class="truncate">{{ item.name }}</span>
                    <ArrowRightIcon class="w-4 h-4 shrink-0 text-(--app-subtle) transition group-hover:translate-x-0.5 group-hover:text-(--app-accent-ink)" />
                </Link>
            </li>
        </ul>

        <!-- Weiterlesen -->
        <button v-if="items.length > limit" type="button"
            class="mt-2 inline-flex items-center gap-1 text-sm font-semibold text-(--app-accent-ink) hover:underline"
            @click="expanded = !expanded">
            {{ expanded ? 'Weniger anzeigen' : `Alle ${items.length} anzeigen` }}
        </button>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { ArrowRightIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    items: {
        type: Array,
        required: true
    },
    title: {
        type: String,
        default: ''
    },
    baseUrl: {
        type: String,
        required: true
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
