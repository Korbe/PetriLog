<script setup>
import { ref, onMounted } from 'vue';
import Cookies from 'js-cookie';

const showBanner = ref(false);
let deferredPrompt = null;
const cookieName = 'petriPwaBannerDismissed';
const cookieDurationHours = 24;

function setDismissCookie() {
    Cookies.set(cookieName, '1', { expires: cookieDurationHours / 24 });
}

function handleInstallClick() {
    if (deferredPrompt) {
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then(choiceResult => {
            if (choiceResult.outcome === 'accepted') {
                console.log('PWA installiert!');
            }
            deferredPrompt = null;
            setDismissCookie();
            showBanner.value = false;
        });
    } else {
        alert('Bitte das Teilen-Symbol in Safari nutzen und "Zum Startbildschirm" auswählen.');
        setDismissCookie();
        showBanner.value = false;
    }
}

function handleDismissClick() {
    setDismissCookie();
    showBanner.value = false;
}

onMounted(() => {
    if (Cookies.get(cookieName)) return;

    // Banner nur auf kleinen Bildschirmen anzeigen
    if (window.innerWidth < 640) { // Tailwind "sm" breakpoint
        showBanner.value = true;
    }

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
    });

    window.addEventListener('appinstalled', () => {
        console.log('PWA erfolgreich installiert');
        setDismissCookie();
        showBanner.value = false;
    });
});
</script>

<template>
    <div v-if="showBanner"
        class="fixed bottom-0 inset-x-0 bg-gray-800 text-white p-4 flex items-center justify-between shadow-lg z-50 sm:hidden">
        <p class="text-sm">
            PetriLog zum Startbildschirm hinzufügen für schnellen Zugriff.
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