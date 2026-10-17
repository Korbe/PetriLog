<template>

    <Head :title="title" />

    <!-- Page header -->
    <div class="app-fade-up mb-6 flex flex-col gap-4 md:mb-8 md:flex-row md:items-end md:justify-between">

        <!-- Left: Back + Title -->
        <div class="flex min-w-0 items-center gap-3">
            <Link v-if="!hideBackButton" :href="backUrl || '/dashboard'" aria-label="Zurück"
                class="ui-icon-btn shrink-0 border border-(--app-border) bg-(--app-surface) shadow-(--app-shadow)">
                <ChevronLeftIcon class="size-5" />
            </Link>
            <div class="min-w-0">
                <p v-if="eyebrow" class="ui-kicker mb-1">{{ eyebrow }}</p>
                <h1 class="font-display break-words text-[2.4rem] leading-[1.05] text-(--app-ink) md:text-5xl">
                    {{ title }}
                </h1>
            </div>
        </div>

        <!-- Right: Actions -->
        <div v-if="$slots.actions" class="flex flex-wrap items-center gap-2 md:justify-end">
            <slot name="actions" />
        </div>

    </div>

    <slot />
</template>
<script setup>
import { ChevronLeftIcon } from '@heroicons/vue/24/outline';
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

const backUrl = ref("");

onMounted(() => {
    backUrl.value = props.backTo ?? sessionStorage.getItem('lastOrigin') ?? route('app.dashboard');
})

const props = defineProps({
    title: String,
    eyebrow: String,
    backTo: String,
    hideBackButton: Boolean
});

</script>
