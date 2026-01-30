<template>

    <div
        class="w-full lg:w-1/3 bg-white dark:bg-gray-800 shadow-md hover:shadow-xl rounded-lg p-8 transition-all duration-200 transform hover:scale-105 flex flex-col">

        <header class="mb-2">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                Dein schwerster Fang
            </h2>
        </header>
        <div v-if="catched" class="flex flex-col sm:flex-row items-start sm:items-center gap-4">


            <!-- Bild links -->
            <div class="w-full sm:w-1/3">
                <img v-if="catched.media?.length > 0" :src="catched.media[0].original_url" alt="Fangbild"
                    class="rounded-xl w-full h-auto object-cover" />
                <img v-else class="rounded-xl w-full h-auto object-cover" src="/images/fish-default.png" />
            </div>

            <!-- Text rechts -->
            <div class="flex-1">
                <p class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                    {{ catched.name }}
                </p>
                <p>Mit einem Gewicht von <b>{{ catched.weight }}</b> Gramm.</p>
                <p></p>

                <!-- Button unten -->
                <div class="mt-3">
                    <VButton :href="route">Mehr</VButton>
                </div>
            </div>
        </div>

        <div v-else class="text-center py-10">
            Noch kein Fang eingetragen
        </div>
    </div>
</template>

<script setup>
import VButton from '@/components/VButton.vue'

const props = defineProps({
    catched: Object,
    route: String,
})
</script>
