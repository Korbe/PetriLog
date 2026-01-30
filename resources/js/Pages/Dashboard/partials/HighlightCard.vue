<template>
    <div
        class="w-full lg:w-1/3 bg-white dark:bg-gray-800 shadow-md hover:shadow-xl rounded-lg p-8 transition-all duration-200 transform hover:scale-105 flex flex-col">

        <header class="mb-2">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                Deine Highlights
            </h2>
        </header>
        <div>
            <ul class="text-gray-700 dark:text-gray-300 space-y-3">
                <li>🐟 Häufigste Fischart: {{ favoriteFish }}</li>
                <li>📍 Meist befischtes Gewässer: {{ favoriteLocation }}</li>
                <li>📅 Rekord Fänge an einem Tag: {{ mostCatchesDay }}</li>
            </ul>
        </div>
    </div>

</template>
<script setup>

const props = defineProps({
    favoriteFish: String,
    favoriteLocation: String,
    mostCatchesDay: Number,
});

</script>