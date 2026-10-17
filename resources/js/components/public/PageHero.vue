<script setup>
// Dark "lake" header band for the public sub pages. Use with <Layout overlay>.
import DepthContours from './DepthContours.vue';

defineProps({
    eyebrow: { type: String, required: true },
});
</script>

<template>
    <section class="ph-grain relative isolate overflow-hidden bg-(--lake-900) text-(--paper)">
        <div class="absolute inset-0 -z-20 bg-[radial-gradient(ellipse_60%_70%_at_80%_20%,rgba(17,141,240,0.28),transparent_70%),linear-gradient(180deg,var(--lake-950),var(--lake-900))]"
            aria-hidden="true"></div>
        <DepthContours class="absolute inset-0 -z-10 h-full w-full opacity-80" :labels="false" />

        <div class="max-w-6xl mx-auto px-4 sm:px-6 pb-24 pt-36 md:pb-28 md:pt-44">
            <p class="ph-rise font-log text-xs uppercase tracking-[0.22em] text-(--paper)/60">{{ eyebrow }}</p>
            <h1 class="ph-rise font-display mt-5 max-w-4xl text-[2.9rem] leading-[0.98] sm:text-6xl md:text-7xl"
                style="animation-delay: 0.08s">
                <slot name="title" />
            </h1>
            <div v-if="$slots.default" class="ph-rise mt-7 max-w-2xl text-lg leading-relaxed text-(--paper)/75"
                style="animation-delay: 0.16s">
                <slot />
            </div>
        </div>

        <!-- water edge -->
        <svg class="absolute inset-x-0 bottom-0 h-8 w-full md:h-10" viewBox="0 0 1440 40" preserveAspectRatio="none"
            aria-hidden="true">
            <path d="M0 22 C240 8 480 8 720 22 S1200 36 1440 22 V40 H0 Z" fill="var(--paper)" />
        </svg>
    </section>
</template>
