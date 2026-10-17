<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button :type="type" class="cursor-pointer inline-flex items-center justify-center gap-2 rounded-full px-5 py-2.5 text-sm font-semibold transition-all duration-150 focus:outline-none focus-visible:ring-4 active:scale-[0.97] disabled:pointer-events-none disabled:opacity-50 border border-(--app-border-strong) bg-(--app-surface) text-(--app-ink) hover:bg-(--app-hover) focus-visible:ring-(--app-accent-soft)">
        <slot />
    </button>
</template>
