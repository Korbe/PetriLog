<template>

    <Head :title="title" />

    <!-- Actions -->
    <div class="sm:flex sm:justify-between sm:items-center mb-5">

        <!-- Left: Title -->
        <div class="mb-4 sm:mb-0 flex items-center">
            <Link v-if="!hideBackButton" :href="backUrl" preserve-scroll preserve-state>
            <ChevronLeftIcon class="w-6 h-6 mr-2"></ChevronLeftIcon>
            </Link>
            <h1 class="p-0 m-0 text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">{{ title }}</h1>
        </div>

        <!-- Right: Actions -->
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
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
    backUrl.value = props.backTo ?? sessionStorage.getItem('lastOrigin')
})

const props = defineProps({
    title: String,
    backTo: String,
    hideBackButton: Boolean
});

</script>