<template>
    <Link v-if="catched" :href="route" class="ui-card ui-card-hover group flex h-full flex-col overflow-hidden">
        <div class="relative aspect-[16/10] overflow-hidden bg-(--app-surface-2)">
            <img v-if="catched.media?.length" :src="catched.media[0].original_url" :alt="fishName"
                class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy" />
            <img v-else src="/images/fish-default.png" :alt="fishName"
                class="h-full w-full object-contain p-6 opacity-80" loading="lazy" />
            <span class="ui-badge absolute left-3 top-3 bg-black/45! text-white! backdrop-blur-sm">{{ kicker }}</span>
        </div>
        <div class="flex flex-1 items-end justify-between gap-4 p-5">
            <div class="min-w-0">
                <p class="truncate text-lg font-semibold">{{ fishName }}</p>
                <p class="ui-muted truncate text-sm">{{ water }}</p>
            </div>
            <p class="shrink-0 text-right">
                <span class="ui-stat-value text-[2.2rem]!">{{ value }}</span>
                <span class="font-log ml-1 text-xs text-(--app-subtle)">{{ unit }}</span>
            </p>
        </div>
    </Link>

    <div v-else class="ui-card flex h-full flex-col items-center justify-center p-8 text-center">
        <p class="ui-kicker">{{ kicker }}</p>
        <p class="ui-muted mt-2 text-sm">Noch kein Fang mit diesem Wert eingetragen.</p>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    kicker: { type: String, required: true },
    catched: Object,
    route: String,
    value: [Number, String],
    unit: String,
})

const fishName = computed(() => props.catched?.fish?.name ?? 'Unbekannter Fisch')
const water = computed(() => props.catched?.lake?.name ?? props.catched?.river?.name ?? 'Unbekanntes Gewässer')
</script>
