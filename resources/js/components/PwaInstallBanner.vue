<template>
    <div v-if="showBanner"
        class="bg-white dark:bg-gray-800 rounded-lg p-4 flex items-center shadow-xs justify-between sm:hidden">
        <p class="text-sm font-medium pr-2">
            Zum Startbildschirm hinzufügen
        </p>

        <div class="flex items-center space-x-2">
            <button @click="handleInstallClick"
                class="px-4 py-2 bg-primary-500 hover:bg-primary-600 rounded text-white text-sm">
                Installieren
            </button>

            <button @click="dismissBanner" class="p-2 hover:bg-gray-200 dark:hover:bg-gray-700 rounded"
                aria-label="Schließen">
                ✕
            </button>
        </div>
    </div>
</template>
<script setup>
import { ref, onMounted, watch } from 'vue';
import Cookies from 'js-cookie';
import { pwa } from '@/pwa';

const showBanner = ref(false);
const COOKIE_NAME = 'petriPwaBannerDismissed';
const COOKIE_DURATION_SEC = 10;

// Plattform-Erkennung
const ua = window.navigator.userAgent.toLowerCase();
const isiOS = /iphone|ipad|ipod/.test(ua);
const isStandalone =
    window.matchMedia('(display-mode: standalone)').matches ||
    window.navigator.standalone === true;

// Banner-Dismiss-Funktion
function dismissBanner() {
    Cookies.set(COOKIE_NAME, '1', { expires: COOKIE_DURATION_SEC / 86400 });
    showBanner.value = false;
}

// Install Click
function handleInstallClick() {
    if (isiOS) {
        alert('Tippe auf das Teilen-Symbol und „Zum Startbildschirm hinzufügen“.');
        dismissBanner();
        return;
    }

    if (pwa.deferredPrompt) {
        pwa.deferredPrompt.prompt();
        pwa.deferredPrompt.userChoice.then(({ outcome }) => {
            pwa.deferredPrompt = null;
            pwa.installable = false;
            dismissBanner();
            if (outcome === 'accepted') console.log('PWA installiert!');
        });
    } else {
        alert('Im Browser-Menü „Zum Startbildschirm hinzufügen“ auswählen.');
    }
}

// Reaktives Watcher – zeigt Banner sobald pwa.installable true wird
watch(
    () => pwa.installable,
    (installable) => {
        if (installable && !Cookies.get(COOKIE_NAME) && !isStandalone) {
            showBanner.value = true;
        }
    }
);

onMounted(() => {
    if (isiOS && !Cookies.get(COOKIE_NAME) && !isStandalone) {
        showBanner.value = true; // iOS Banner direkt anzeigen
    }

    window.addEventListener('appinstalled', () => {
        dismissBanner();
        console.log('PWA erfolgreich installiert!');
    });
});
</script>