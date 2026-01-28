<template>
    <div v-if="items && items.length">
        <p v-if="title" class="font-bold text-lg pt-5 pb-2">
            {{ title }}
        </p>

        <ul>
            <li v-for="item in visibleItems" :key="item.id" class="hover:bg-gray-100 dark:hover:bg-gray-700 rounded">
                <Link :href="`${baseUrl}/${item.id}`"
                    class="flex items-center justify-between gap-2 p-2 text-blue-500 hover:text-blue-700">
                    <span>{{ item.name }}</span>
                    <ArrowRightIcon class="w-5 h-5" />
                </Link>
            </li>
        </ul>

        <!-- Weiterlesen -->
        <p v-if="items.length > limit" class="text-sm text-blue-500 hover:text-blue-700 cursor-pointer mt-2 select-none"
            @click="expanded = !expanded">
            {{ expanded ? 'Weniger anzeigen' : 'Weiterlesen' }}
        </p>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { ArrowRightIcon } from '@heroicons/vue/24/solid'

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