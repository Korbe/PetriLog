<script setup>
import { ref, computed, onMounted } from 'vue';
import Cookies from 'js-cookie';
import { pwa } from '@/pwa';

const showBanner = ref(false);

const COOKIE_NAME = 'petriPwaBannerDismissed';
const COOKIE_HOURS = 24;

// Plattform-Erkennung
const ua = window.navigator.userAgent.toLowerCase();
const isiOS = /iphone|ipad|ipod/.test(ua);
const isStandalone =
    window.matchMedia('(display-mode: standalone)').matches ||
    window.navigator.standalone === true;

// Banner darf angezeigt werden?
const canShowBanner = computed(() => {
    if (Cookies.get(COOKIE_NAME)) return false;
    if (isStandalone) return false;

    // iOS: immer erlauben
    if (isiOS) return true;

    alert('PWA installable: ' + pwa.installable);

    // Android / Chrome: nur wenn installierbar
    return pwa.installable;
});

function dismissBanner() {
    Cookies.set(COOKIE_NAME, '1', { expires: 10 / 86400 });//{ expires: COOKIE_HOURS / 24 });
    showBanner.value = false;
}

function handleInstallClick() {
    // iOS hat kein Install-Prompt
    if (isiOS) {
        alert(
            'Tippe auf das Teilen-Symbol und wähle „Zum Home-Bildschirm hinzufügen“.'
        );
        dismissBanner();
        return;
    }

    // Android / Chrome
    if (!pwa.deferredPrompt) {
        alert(
            'Bitte im Browser-Menü „Zum Startbildschirm hinzufügen“ auswählen.'
        );
        return;
    }

    pwa.deferredPrompt.prompt();

    pwa.deferredPrompt.userChoice.then(({ outcome }) => {
        if (outcome === 'accepted') {
            alert('PWA installiert');
        }
        pwa.deferredPrompt = null;
        pwa.installable = false;
        dismissBanner();
    });
}

onMounted(() => {
    if (!canShowBanner.value) return;

    // Optional: nur mobil anzeigen
    if (window.innerWidth < 640) {
        showBanner.value = true;
    }

    // Falls App installiert wird
    window.addEventListener('appinstalled', () => {
        alert('PWA installiert hide banner');
        dismissBanner();
    });
});
</script>

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