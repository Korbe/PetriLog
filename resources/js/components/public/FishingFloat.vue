<script setup>
// A fishing float (Pose) sitting on the water line, drawing ripples.
// The component's bottom edge is the water line.
import { useId } from 'vue';

const gradientId = `ph-float-${useId().replace(/[^a-zA-Z0-9-]/g, '-')}`;

defineProps({
    size: { type: Number, default: 26 },
    rippleColor: { type: String, default: 'rgba(243, 238, 227, 0.55)' },
});
</script>

<template>
    <div class="relative pointer-events-none" :style="{ width: `${size}px`, height: `${size * 1.6}px` }"
        aria-hidden="true">
        <span v-for="i in 3" :key="i" class="ph-ripple absolute left-1/2 rounded-[50%] border-[1.5px]" :style="{
            width: `${size * 4.5}px`,
            height: `${size * 0.9}px`,
            bottom: `${-size * 0.45}px`,
            borderColor: rippleColor,
            animationDelay: `${(i - 1) * 1.2}s`,
        }" />

        <svg class="ph-bob absolute inset-0 h-full w-full overflow-visible" viewBox="0 0 40 64">
            <defs>
                <linearGradient :id="gradientId" x1="0" y1="0" x2="1" y2="0">
                    <stop offset="0" stop-color="#d63c0b" />
                    <stop offset="0.45" stop-color="#ff6a2b" />
                    <stop offset="1" stop-color="#c2330a" />
                </linearGradient>
            </defs>
            <!-- antenna -->
            <rect x="18.5" y="0" width="3" height="26" rx="1.5" fill="#ff6a2b" />
            <rect x="18.5" y="0" width="3" height="7" rx="1.5" fill="#fff4ea" />
            <!-- body above the water line -->
            <path d="M7 64 C7 42 13 26 20 26 C27 26 33 42 33 64 Z" :fill="`url(#${gradientId})`" />
            <path d="M7 64 C7 58 7.5 54 8.4 50 L31.6 50 C32.5 54 33 58 33 64 Z" fill="#f7f1e6" />
            <path d="M13 36 C14.5 31 16.5 29 18 28.5" stroke="#ffd2b8" stroke-width="1.6" stroke-linecap="round"
                fill="none" opacity="0.8" />
        </svg>
    </div>
</template>
