<script setup>
// Bathymetric lake map ("Tiefenkarte") drawn from pre-generated contour paths.
import contours from './contourPaths.js';

defineProps({
    labels: { type: Boolean, default: true },
    animated: { type: Boolean, default: true },
    stroke: { type: String, default: '207, 232, 252' }, // rgb triplet
});
</script>

<template>
    <svg viewBox="0 0 1200 800" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
        <g fill="none" stroke-linejoin="round">
            <path v-for="(c, i) in contours" :key="c.depth" :d="c.d" pathLength="1"
                :class="animated ? 'ph-contour' : ''" :style="{ animationDelay: `${0.15 + i * 0.12}s` }"
                :stroke="i === contours.length - 1 ? 'rgba(111, 185, 246, 0.7)' : `rgba(${stroke}, ${0.1 + i * 0.03})`"
                :stroke-width="i % 2 ? 1 : 1.4" />
        </g>
        <g v-if="labels" class="font-log" font-size="11" letter-spacing="1">
            <text v-for="c in contours.filter((_, i) => i % 2 === 1)" :key="`l${c.depth}`" :x="c.label[0] + 6"
                :y="c.label[1] - 6" :fill="`rgba(${stroke}, 0.45)`">−{{ c.depth }} m</text>
        </g>
        <!-- deepest point -->
        <g transform="translate(936 502)">
            <path d="M-6 -6 L6 6 M6 -6 L-6 6" stroke="rgba(255, 106, 43, 0.85)" stroke-width="1.6" />
        </g>
    </svg>
</template>
