<template>
    <div v-if="openBanner"
        class="bg-white dark:bg-gray-800 rounded-lg p-4 flex items-center shadow-xs justify-between sm:hidden">
        <p class="text-sm font-medium pr-2">
            Zum Startbildschirm hinzufügen
        </p>

        <div class="flex items-center space-x-2">
            <Link href="/pwa" class="px-4 py-2 bg-primary-500 hover:bg-primary-600 rounded text-white text-sm">
            Installieren
            </Link>

            <button class="p-2 hover:bg-gray-200 dark:hover:bg-gray-700 rounded" aria-label="Schließen"
                @click="closeBanner">
                ✕
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const openBanner = ref(false);

function closeBanner() {
    openBanner.value = false;
}

// Mobile-Erkennung über User-Agent
function isMobileDevice() {
    const ua = navigator.userAgent || navigator.vendor || window.opera;
    return /android|iphone|ipad|ipod|mobile/i.test(ua);
}

onMounted(() => {
    // Prüfen, ob App bereits installiert ist
    const isStandalone =
        window.matchMedia('(display-mode: standalone)').matches ||
        window.navigator.standalone === true;

    // Banner nur öffnen auf echten mobilen Geräten, die die App noch nicht installiert haben
    if (!isStandalone && isMobileDevice()) {
        openBanner.value = true;
    }
});
</script>
