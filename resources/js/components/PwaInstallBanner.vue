<script setup>
import { ref, onMounted } from 'vue';
import Cookies from 'js-cookie';

const showBanner = ref(false);
const cookieName = 'petriPwaBannerDismissed';
const cookieDurationHours = 24;

function setDismissCookie() {
    Cookies.set(cookieName, '1', { expires: cookieDurationHours / 24 });
}

function handleInstallClick() {
    if (window.deferredPrompt) {
        window.deferredPrompt.prompt();
        window.deferredPrompt.userChoice.then(choiceResult => {
            if (choiceResult.outcome === 'accepted') {
                console.log('PWA installiert!');
            }
            window.deferredPrompt = null;
            setDismissCookie();
            showBanner.value = false;
        });
    } else {
        alert('In Chrome die drei Punkte klicken und "Zum Startbildschirm hinzufügen" auswählen, unter Safari bitte das Teilen-Symbol in nutzen und "Zum Startbildschirm" auswählen.');
        setDismissCookie();
        showBanner.value = false;
    }
}

function handleDismissClick() {
    setDismissCookie();
    showBanner.value = false;
}

onMounted(() => {
    // Wenn Cookie gesetzt oder App bereits installiert → Banner nicht zeigen
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches;
    const isiOS = /iphone|ipad|ipod/.test(window.navigator.userAgent.toLowerCase());
    const isInstalledIOS = isiOS && window.navigator.standalone;

    if (Cookies.get(cookieName) || isStandalone || isInstalledIOS) return;

    // Banner nur auf kleinen Bildschirmen anzeigen
    if (window.innerWidth < 640) {
        showBanner.value = true;
    }

    window.addEventListener('appinstalled', () => {
        console.log('PWA erfolgreich installiert');
        setDismissCookie();
        showBanner.value = false;
    });
});
</script>

<template>
    <div v-if="showBanner"  class="bg-white dark:bg-gray-800 rounded-lg p-4 flex items-center shadow-xs justify-between sm:hidden">
        <p class="text-sm font-medium pr-1">
            PetriLog zum Startbildschirm hinzufügen
        </p>
        <div class="flex items-center space-x-2">
            <button @click="handleInstallClick"
                class="px-4 py-2 bg-primary-500 hover:bg-primary-600 rounded text-white">Installieren</button>
            <button @click="handleDismissClick" class="p-2 hover:bg-gray-700 rounded">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M10 9.293l3.646-3.647a.5.5 0 11.708.708L10.707 10l3.647 3.646a.5.5 0 11-.708.708L10 10.707l-3.646 3.647a.5.5 0 11-.708-.708L9.293 10 5.646 6.354a.5.5 0 11.708-.708L10 9.293z"
                        clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    </div>
</template>