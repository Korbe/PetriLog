<template>
    <div class="w-full lg:w-1/3 bg-white dark:bg-gray-800 shadow-md hover:shadow-xl rounded-lg p-8 transition-all duration-200 transform hover:scale-105 flex flex-col"">
        <header class="mb-2">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                Deine letzten Fänge 🎣
            </h2>
        </header>
        <div v-for="catchItem in recentCatches" :key="catchItem.id" class="flex items-center space-x-4">
            <Link :href="catchItem.show_url">
                <div class="flex-1 hover:bg-gray-100 dark:hover:bg-gray-700 p-2 rounded-lg">
                    <div class="font-medium">{{ catchItem.fish.name }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ catchItem.lake?.name || catchItem.river?.name }} · {{
                        formatDate(catchItem.created_at) }}
                    </div>
                </div>
            </Link>
        </div>
    </div>
</template>
<script setup>
defineProps({
    recentCatches: {
        type: Array,
        required: true
    }
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return `${String(date.getDate()).padStart(2, '0')}.${String(date.getMonth() + 1).padStart(2, '0')}.${date.getFullYear()} ${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`;
};
</script>