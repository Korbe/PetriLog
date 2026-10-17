<script setup>
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

// Flash messages from the server, shown as a toast in the top right corner.
const page = usePage();
const show = ref(false);
const style = ref('success');
const message = ref('');
let timeout = null;

// Watch auf flash-Props
watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) {
            style.value = 'success';
            message.value = flash.success;
            showBanner();
        } else if (flash?.error) {
            style.value = 'danger';
            message.value = flash.error;
            showBanner();
        }
    },
    { immediate: true }
);

// Banner anzeigen und Timeout starten
function showBanner() {
    show.value = true;

    if (timeout) clearTimeout(timeout);

    timeout = setTimeout(() => {
        show.value = false;
    }, 5000);
}

function dismiss() {
    show.value = false;
    if (timeout) clearTimeout(timeout);
}
</script>

<template>
    <div class="pointer-events-none fixed inset-x-0 top-3 z-[60] flex justify-center px-4 sm:inset-x-auto sm:right-6 sm:top-20 sm:justify-end"
        aria-live="polite">
        <Transition enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 -translate-y-3 sm:translate-y-0 sm:translate-x-4"
            leave-active-class="transition duration-200 ease-in" leave-to-class="opacity-0">
            <div v-if="show && message" role="status"
                class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-2xl border border-(--app-border) bg-(--app-surface) p-4 text-(--app-ink) shadow-(--app-shadow-lg)">
                <span class="flex size-9 shrink-0 items-center justify-center rounded-xl"
                    :class="style == 'success' ? 'bg-(--app-success-soft) text-(--app-success)' : 'bg-(--app-danger-soft) text-(--app-danger)'">
                    <svg v-if="style == 'success'" class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                    <svg v-else class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </span>

                <p class="min-w-0 flex-1 pt-1.5 text-sm font-medium">{{ message }}</p>

                <button type="button" class="ui-icon-btn -mr-1 -mt-0.5 size-8! shrink-0" aria-label="Meldung schließen"
                    @click="dismiss">
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </Transition>
    </div>
</template>
