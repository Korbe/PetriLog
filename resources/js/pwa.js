import { reactive } from 'vue';

// The browser's install prompt event. Kept outside the reactive store on purpose:
// calling prompt() through a Vue proxy throws "Illegal invocation".
let deferredPrompt = null;

export const pwa = reactive({
    // Browser offers a native install dialog (Chrome, Edge, Android, Samsung Internet …)
    canPrompt: false,
    // Running as installed app
    installed: false,
    // iOS has no install API — users add the app via Safari's share sheet
    isIos: false,
});

export function initPwa() {
    if (typeof window === 'undefined') return;

    pwa.installed = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    pwa.isIos = /iphone|ipad|ipod/i.test(navigator.userAgent)
        || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredPrompt = event;
        pwa.canPrompt = true;
    });

    window.addEventListener('appinstalled', () => {
        deferredPrompt = null;
        pwa.canPrompt = false;
        pwa.installed = true;
    });
}

/**
 * Opens the browser's install dialog. Resolves to 'accepted', 'dismissed' or 'unavailable'.
 */
export async function installPwa() {
    if (!deferredPrompt) return 'unavailable';

    const promptEvent = deferredPrompt;
    deferredPrompt = null;
    pwa.canPrompt = false;

    await promptEvent.prompt();
    const { outcome } = await promptEvent.userChoice;
    if (outcome === 'accepted') pwa.installed = true;

    return outcome;
}
