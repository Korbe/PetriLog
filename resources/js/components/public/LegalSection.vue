<script setup>
defineProps({
    id: { type: String, required: true },
    number: { type: Number, required: true },
    title: { type: String, required: true },
});
</script>

<template>
    <section :id="id" class="scroll-mt-28 border-t border-(--ink)/10 py-10 first:border-t-0 first:pt-0">
        <div class="flex items-baseline gap-4">
            <span class="font-log text-xs text-(--ink)/40">{{ String(number).padStart(2, '0') }}</span>
            <h2 class="font-display text-3xl leading-tight md:text-4xl">{{ title }}</h2>
        </div>
        <div class="ph-prose mt-5 max-w-3xl md:pl-8">
            <slot />
        </div>
    </section>
</template>
