<template>
    <div class="ui-card flex h-full flex-col p-6">
        <div class="flex items-center justify-between gap-3">
            <p class="ui-kicker">Letzte Fänge</p>
            <Link href="/catched" class="text-sm font-semibold text-(--app-accent-ink) hover:underline">Alle ansehen</Link>
        </div>

        <ul v-if="recentCatches.length" class="-mx-3 mt-3 flex-1">
            <li v-for="catchItem in recentCatches" :key="catchItem.id">
                <Link :href="catchItem.show_url" class="ui-row group">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-(--app-surface-2) font-log text-xs text-(--app-muted)">
                        {{ day(catchItem.date ?? catchItem.created_at) }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate font-semibold">{{ catchItem.fish?.name ?? 'Unbekannter Fisch' }}</p>
                        <p class="truncate text-sm text-(--app-muted)">
                            {{ catchItem.lake?.name || catchItem.river?.name || 'Unbekanntes Gewässer' }}
                            · {{ formatDate(catchItem.date ?? catchItem.created_at) }}
                        </p>
                    </div>
                    <span v-if="catchItem.length" class="font-log shrink-0 text-sm text-(--app-muted)">{{ catchItem.length }} cm</span>
                </Link>
            </li>
        </ul>
        <p v-else class="ui-muted mt-4 text-sm">Noch keine Fänge eingetragen.</p>
    </div>
</template>

<script setup>
defineProps({
    recentCatches: {
        type: Array,
        required: true
    }
});

const day = (dateString) => String(new Date(dateString).getDate()).padStart(2, '0');

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return `${String(date.getDate()).padStart(2, '0')}.${String(date.getMonth() + 1).padStart(2, '0')}.${date.getFullYear()} ${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`;
};
</script>
