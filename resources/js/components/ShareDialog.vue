<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from "@headlessui/vue";
import { XMarkIcon, ClipboardIcon, CheckIcon, LockClosedIcon, GlobeAltIcon } from "@heroicons/vue/24/outline";
import { confirmDialog } from "@/composables/useDialog";

const props = defineProps({
    modelValue: Boolean,
    // null = Fang ist privat (noch kein Freigabe-Link)
    shareUrl: { type: String, default: null },
    catchedId: { type: Number, required: true },
});

const emit = defineEmits(["update:modelValue"]);

const copied = ref(false);
const copyFailed = ref(false);
const urlInput = ref(null);

const close = () => emit("update:modelValue", false);

const busy = ref(false);

const createLink = () => {
    router.post(route('app.catched.share', props.catchedId), {}, {
        preserveScroll: true,
        onStart: () => (busy.value = true),
        onFinish: () => (busy.value = false),
    });
};

const revokeLink = async () => {
    close(); // Bestätigung erscheint im App-Dialog, nicht über diesem Dialog
    const ok = await confirmDialog({
        title: 'Freigabe beenden?',
        message: 'Der bisherige Link funktioniert danach nicht mehr – auch dort, wo du ihn schon geteilt hast. Du kannst jederzeit einen neuen Link erstellen.',
        confirmText: 'Freigabe beenden',
        tone: 'danger',
    });
    if (!ok) return;

    router.delete(route('app.catched.unshare', props.catchedId), {
        preserveScroll: true,
        onStart: () => (busy.value = true),
        onFinish: () => (busy.value = false),
    });
};

const canNativeShare = typeof navigator !== 'undefined' && !!navigator.share;

const nativeShare = async () => {
    try {
        await navigator.share({ title: 'Mein Fang – PetriLog', url: props.shareUrl });
    } catch { /* abgebrochen */ }
};

const copyToClipboard = async () => {
    try {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            // Moderne Browser
            await navigator.clipboard.writeText(props.shareUrl);
        } else {
            // Fallback für ältere Browser
            const textarea = document.createElement('textarea');
            textarea.value = props.shareUrl;
            textarea.setAttribute('readonly', '');
            textarea.style.position = 'absolute';
            textarea.style.left = '-9999px';
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
        }
        copyFailed.value = false;
        copied.value = true;
        setTimeout(() => (copied.value = false), 2000);
    } catch (err) {
        console.error("Fehler beim Kopieren:", err);
        // Statt eines Browser-Popups: Link markieren und Hinweis direkt unter dem Feld zeigen
        copyFailed.value = true;
        urlInput.value?.focus();
        urlInput.value?.select();
    }
};
</script>

<template>
    <TransitionRoot as="template" :show="props.modelValue">
        <Dialog as="div" class="relative z-50" @close="close">
            <!-- Overlay -->
            <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
                leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-[#021015]/60 backdrop-blur-sm" />
            </TransitionChild>

            <!-- Content -->
            <div class="fixed inset-0 flex items-end justify-center p-4 sm:items-center">
                <TransitionChild as="template" enter="ease-out duration-300"
                    enter-from="opacity-0 translate-y-4 scale-95" enter-to="opacity-100 translate-y-0 scale-100"
                    leave="ease-in duration-200" leave-from="opacity-100 translate-y-0 scale-100"
                    leave-to="opacity-0 translate-y-4 scale-95">
                    <DialogPanel
                        class="w-full max-w-md rounded-3xl border border-(--app-border) bg-(--app-surface) p-6 text-(--app-ink) shadow-(--app-shadow-lg)">
                        <div class="mb-5 flex items-start justify-between gap-4">
                            <div>
                                <p class="ui-kicker">Teilen</p>
                                <DialogTitle class="font-display mt-1 text-3xl">Fang teilen</DialogTitle>
                            </div>
                            <button type="button" @click="close" class="ui-icon-btn -mr-2 -mt-1" aria-label="Schließen">
                                <XMarkIcon class="w-5 h-5" />
                            </button>
                        </div>

                        <!-- Privat: erst nach ausdrücklicher Freigabe gibt es einen öffentlichen Link -->
                        <div v-if="!shareUrl">
                            <div class="flex gap-3 rounded-2xl bg-(--app-surface-2) p-4">
                                <LockClosedIcon class="size-5 shrink-0 text-(--app-muted)" />
                                <p class="text-sm text-(--app-muted)">
                                    Dieser Fang ist <strong class="text-(--app-ink)">privat</strong>. Erstelle einen Link, um ihn
                                    zu teilen – sehen kann ihn dann jeder, der den Link kennt. Deine anderen Fänge bleiben privat.
                                </p>
                            </div>
                            <button type="button" @click="createLink" :disabled="busy"
                                class="mt-4 flex w-full items-center justify-center gap-2 rounded-full bg-primary-500 px-4 py-3 text-sm font-semibold text-white transition hover:bg-primary-600 disabled:opacity-60">
                                <GlobeAltIcon class="size-5" />
                                {{ busy ? 'Wird erstellt …' : 'Öffentlichen Link erstellen' }}
                            </button>
                        </div>

                        <template v-else>
                        <button v-if="canNativeShare" type="button" @click="nativeShare"
                            class="mb-3 flex w-full items-center justify-center gap-2 rounded-full bg-primary-500 px-4 py-3 text-sm font-semibold text-white transition hover:bg-primary-600">
                            Teilen über …
                        </button>

                        <div class="grid grid-cols-3 gap-2">
                            <a :href="`https://wa.me/?text=${encodeURIComponent(`${shareUrl}`)}`" target="_blank"
                                rel="noopener"
                                class="flex flex-col items-center gap-1.5 rounded-2xl bg-[#25D366]/12 px-2 py-3 text-xs font-semibold text-[#128C4B] transition hover:bg-[#25D366]/20 dark:text-[#4ce08a]">
                                <svg class="size-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38a9.87 9.87 0 0 0 4.74 1.21h.01c5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2Zm5.8 14.16c-.24.68-1.41 1.3-1.95 1.35-.5.05-.97.23-3.27-.68-2.77-1.09-4.52-3.92-4.66-4.1-.13-.18-1.11-1.48-1.11-2.83 0-1.34.7-2 .95-2.28.25-.27.54-.34.72-.34h.52c.17 0 .39-.06.61.47.24.56.79 1.92.86 2.06.07.14.11.3.02.48-.09.18-.14.3-.27.46-.14.16-.29.36-.41.48-.14.14-.28.29-.12.56.16.27.71 1.17 1.52 1.89 1.04.93 1.92 1.22 2.19 1.35.27.14.43.11.59-.07.16-.18.68-.79.86-1.07.18-.27.36-.23.61-.14.25.09 1.59.75 1.86.89.27.14.45.2.52.32.07.11.07.66-.17 1.33Z" />
                                </svg>
                                WhatsApp
                            </a>
                            <a :href="`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}`"
                                target="_blank" rel="noopener"
                                class="flex flex-col items-center gap-1.5 rounded-2xl bg-[#1877F2]/10 px-2 py-3 text-xs font-semibold text-[#1877F2] transition hover:bg-[#1877F2]/18 dark:text-[#6aa7ff]">
                                <svg class="size-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06c0 5 3.66 9.15 8.44 9.94v-7.03H7.9v-2.9h2.54V9.84c0-2.52 1.49-3.91 3.78-3.91 1.1 0 2.24.2 2.24.2v2.47h-1.26c-1.24 0-1.63.78-1.63 1.57v1.89h2.78l-.44 2.9h-2.34V22c4.78-.79 8.43-4.94 8.43-9.94Z" />
                                </svg>
                                Facebook
                            </a>
                            <a :href="`https://twitter.com/intent/tweet?url=${encodeURIComponent(shareUrl)}&text=Schau was ich gefangen habe! - PetriLog`"
                                target="_blank" rel="noopener"
                                class="flex flex-col items-center gap-1.5 rounded-2xl bg-(--app-hover) px-2 py-3 text-xs font-semibold text-(--app-ink) transition hover:bg-(--app-border)">
                                <svg class="size-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M17.75 3h3.07l-6.7 7.66L22 21h-6.17l-4.83-6.32L5.47 21H2.4l7.17-8.2L2 3h6.33l4.37 5.78L17.75 3Zm-1.08 16.2h1.7L7.4 4.73H5.58L16.67 19.2Z" />
                                </svg>
                                X / Twitter
                            </a>
                        </div>

                        <!-- URL Copy -->
                        <p class="ui-kicker mb-2 mt-6">Link kopieren</p>
                        <div class="flex items-center gap-2 rounded-full border border-(--app-border-strong) bg-(--app-surface-2) py-1.5 pl-4 pr-1.5">
                            <input ref="urlInput" class="min-w-0 flex-1 truncate border-0 bg-transparent p-0 text-sm text-(--app-muted) focus:ring-0"
                                :value="shareUrl" readonly @focus="$event.target.select()" aria-label="Link zum Fang" />
                            <button type="button" @click="copyToClipboard"
                                class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-4 py-2 text-sm font-semibold transition"
                                :class="copied ? 'bg-(--app-success) text-white' : 'bg-(--app-ink) text-(--app-surface) hover:opacity-90'">
                                <CheckIcon v-if="copied" class="w-4 h-4" />
                                <ClipboardIcon v-else class="w-4 h-4" />
                                {{ copied ? 'Kopiert' : 'Kopieren' }}
                            </button>
                        </div>
                        <p v-if="copyFailed" class="mt-2 px-1 text-xs font-medium text-(--app-danger)" role="alert">
                            Automatisches Kopieren klappt in diesem Browser nicht – der Link ist markiert, kopiere ihn bitte manuell.
                        </p>

                        <div class="mt-6 flex items-center justify-between gap-3 border-t border-(--app-border) pt-4">
                            <p class="flex items-center gap-1.5 text-xs text-(--app-muted)">
                                <GlobeAltIcon class="size-4" /> Jeder mit dem Link kann den Fang sehen.
                            </p>
                            <button type="button" @click="revokeLink" :disabled="busy"
                                class="shrink-0 text-xs font-semibold text-(--app-danger) hover:underline disabled:opacity-60">
                                Freigabe beenden
                            </button>
                        </div>
                        </template>
                    </DialogPanel>
                </TransitionChild>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
