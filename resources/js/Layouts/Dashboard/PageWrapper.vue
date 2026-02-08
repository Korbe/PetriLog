<template>

    <Head :title="title" />

    <!-- Actions -->
    <div class="sm:flex sm:justify-between sm:items-center">

        <!-- Left: Title -->
        <div v-if="!hideBackButton" class="mb-4 sm:mb-0 flex items-center md:mb-5">
            <Link :href="backUrl" class="flex items-center gap-2">
            <ChevronLeftIcon class="w-6 h-6 text-gray-800 dark:text-gray-100" />
            <span class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-gray-100">
                {{ title }}
            </span>
            </Link>
        </div>

        <!-- Right: Actions -->
        <div class="flex flex-wrap justify-start sm:justify-end gap-2">
            <slot name="actions" />
        </div>

    </div>

    <slot />
</template>
<script setup>
import { ChevronLeftIcon } from '@heroicons/vue/24/solid';
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

const backUrl = ref("");

onMounted(() => {
    backUrl.value = props.backTo ?? sessionStorage.getItem('lastOrigin') ?? route('app.dashboard');
})

const props = defineProps({
    title: String,
    backTo: String,
    hideBackButton: Boolean
});

</script>