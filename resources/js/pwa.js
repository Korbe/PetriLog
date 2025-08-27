import { onMounted } from 'vue'

onMounted(() => {
    if (typeof window !== 'undefined') {
        window.deferredPrompt = null;

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            window.deferredPrompt = e;
        });
    }
});