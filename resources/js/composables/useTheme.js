import { useDark } from '@vueuse/core'

const STORAGE_KEY = 'petrilog-theme'
const LEGACY_KEY = 'vueuse-color-scheme'

/**
 * Dunkelmodus der App – Standard ist der helle Modus (nicht die Systemeinstellung).
 * Eine früher bewusst gewählte Einstellung (hell/dunkel) wird übernommen.
 */
export function useAppDark() {
    if (typeof window !== 'undefined') {
        try {
            const legacy = localStorage.getItem(LEGACY_KEY)
            if (!localStorage.getItem(STORAGE_KEY) && (legacy === 'dark' || legacy === 'light')) {
                localStorage.setItem(STORAGE_KEY, legacy)
            }
            localStorage.removeItem(LEGACY_KEY)
        } catch { /* Speicher nicht verfügbar */ }
    }

    return useDark({
        selector: 'html',
        storageKey: STORAGE_KEY,
        initialValue: 'light',
    })
}
