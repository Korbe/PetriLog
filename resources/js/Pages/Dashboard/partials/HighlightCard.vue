<template>
    <div class="ui-card flex h-full flex-col p-6">
        <p class="ui-kicker">Deine Highlights</p>
        <ul class="mt-4 flex-1 space-y-3">
            <li v-for="item in items" :key="item.label" class="flex items-center gap-3">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-xl" :class="item.tone">
                    <component :is="item.icon" class="size-5" />
                </span>
                <div class="min-w-0">
                    <p class="text-xs text-(--app-subtle)">{{ item.label }}</p>
                    <p class="truncate font-semibold">{{ item.value ?? '–' }}</p>
                </div>
            </li>
        </ul>
    </div>
</template>
<script setup>
import { computed, h } from 'vue';
import { MapPinIcon, CalendarDaysIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    favoriteFish: String,
    favoriteLocation: String,
    mostCatchesDay: Number,
});

const FishIcon = (p) => h('svg', { ...p, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': 1.5 }, [
    h('path', { d: 'M2.5 12c2.6-3.6 6.1-5.5 10-5.5 3.1 0 5.6 1.4 7.5 4L22 8.5v7l-2 -2c-1.9 2.6-4.4 4-7.5 4-3.9 0-7.4-1.9-10-5.5Z', 'stroke-linejoin': 'round' }),
    h('circle', { cx: 7.5, cy: 11, r: 0.9, fill: 'currentColor', stroke: 'none' }),
]);

const items = computed(() => [
    { label: 'Häufigste Fischart', value: props.favoriteFish, icon: FishIcon, tone: 'bg-(--app-accent-soft) text-(--app-accent-ink)' },
    { label: 'Lieblings-Spot', value: props.favoriteLocation, icon: MapPinIcon, tone: 'bg-(--app-success-soft) text-(--app-success)' },
    {
        label: 'Rekord an einem Tag',
        value: props.mostCatchesDay ? `${props.mostCatchesDay} ${props.mostCatchesDay === 1 ? 'Fang' : 'Fänge'}` : null,
        icon: CalendarDaysIcon,
        tone: 'bg-[color-mix(in_srgb,var(--app-float)_14%,transparent)] text-(--app-float)',
    },
]);
</script>
