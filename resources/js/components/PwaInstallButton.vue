<script setup>
import { ref, onMounted } from 'vue';
import { pwa } from '@/pwa';

const showInstallButton = ref(false);

// Plattform-Erkennung
const ua = window.navigator.userAgent.toLowerCase();
const isiOS = /iphone|ipad|ipod/.test(ua);
let isStandalone = false;

onMounted(() => {
    // Prüfen, ob App bereits installiert ist
    isStandalone =
        window.matchMedia('(display-mode: standalone)').matches ||
        window.navigator.standalone === true;

    // Button nur anzeigen, wenn App noch nicht installiert ist
    if (!isStandalone) {
        showInstallButton.value = true;
    }

    // Event falls App später installiert wird (z.B. über Chrome Menü)
    window.addEventListener('appinstalled', () => {
        showInstallButton.value = false;
        console.log('PWA installiert');
    });
});

// Install Click
function handleInstallClick() {
    if (isiOS) {
        alert('Unter IOS tippe bitte auf das Teilen-Symbol und „Zum Startbildschirm hinzufügen“.');
        return;
    }

    if (pwa.deferredPrompt) {
        pwa.deferredPrompt.prompt();
        pwa.deferredPrompt.userChoice.then(({ outcome }) => {
            pwa.deferredPrompt = null;
            pwa.installable = false;
            if (outcome === 'accepted') console.log('PWA installiert!');
            showInstallButton.value = false;
        });
    } else {
        alert('Im Browser-Menü bitte „Zum Startbildschirm hinzufügen“ auswählen.');
    }
}
</script>

<template>
    <button v-if="showInstallButton" @click="handleInstallClick"
        class="px-4 py-2 bg-primary-500 hover:bg-primary-600 rounded text-white text-sm">
        Installieren
    </button>
</template>
