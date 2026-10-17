<script setup>
defineProps({
    type: {
        type: String,
        default: 'submit',
    },
});
</script>

<template>
    <button :type="type" class="cursor-pointer inline-flex items-center justify-center gap-2 rounded-full px-5 py-2.5 text-sm font-semibold transition-all duration-150 focus:outline-none focus-visible:ring-4 active:scale-[0.97] disabled:pointer-events-none disabled:opacity-50 bg-primary-500 text-white shadow-sm shadow-primary-500/25 hover:bg-primary-600 focus-visible:ring-primary-500/40">
        <slot />
    </button>
</template>
