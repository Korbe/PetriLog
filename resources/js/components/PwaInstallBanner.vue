<script setup>
import { ref, onMounted } from 'vue';

const showBanner = ref(false);
let deferredPrompt = null;
const cookieName = 'petriPwaBannerDismissed';

function setBannerCookie() {
    localStorage.setItem(cookieName, '1');
}

function handleInstallClick() {
    if (deferredPrompt) {
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then(choiceResult => {
            if (choiceResult.outcome === 'accepted') {
                console.log('PWA installiert!');
            }
            deferredPrompt = null;
            setBannerCookie();
            showBanner.value = false;
        });
    }
}

onMounted(() => {
    if (localStorage.getItem(cookieName)) return;

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        showBanner.value = true;
    });

    window.addEventListener('appinstalled', () => {
        console.log('PWA erfolgreich installiert');
        setBannerCookie();
        showBanner.value = false;
    });
});
</script>

<template>
    <div v-if="showBanner"
        class="fixed bottom-0 inset-x-0 bg-gray-800 text-white p-4 flex items-center justify-between shadow-lg z-50">
        <p class="text-sm">
            Füge PetriLog auf deinem Startbildschirm hinzu für schnellen Zugriff.
        </p>
        <button @click="handleInstallClick" class="ml-4 px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded text-white">
            Installieren
        </button>
    </div>
</template>