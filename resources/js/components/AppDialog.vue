<template>
    <!-- Native <dialog>: liegt im Top-Layer und damit auch über anderen offenen Modals -->
    <dialog ref="dialog" class="app-dialog m-auto w-[calc(100%-2rem)] max-w-md overflow-visible bg-transparent p-0 text-(--app-ink)"
        :aria-labelledby="titleId" :aria-describedby="dialogState.message ? messageId : undefined"
        @cancel.prevent="cancel" @click.self="dialogState.type === 'confirm' ? cancel() : confirm()">
        <div class="app-dialog-panel overflow-hidden rounded-3xl border border-(--app-border) bg-(--app-surface) shadow-(--app-shadow-lg)">
            <div class="px-6 pb-5 pt-6 sm:px-7 sm:pt-7">
                <span class="flex size-12 items-center justify-center rounded-2xl" :class="tone.badge">
                    <component :is="tone.icon" class="size-6" />
                </span>
                <h2 :id="titleId" class="font-display mt-4 text-[1.7rem] leading-tight">{{ dialogState.title }}</h2>
                <p v-if="dialogState.message" :id="messageId" class="mt-2 whitespace-pre-line text-sm leading-relaxed text-(--app-muted)">
                    {{ dialogState.message }}
                </p>
            </div>

            <div class="flex flex-col-reverse gap-2 border-t border-(--app-border) bg-(--app-surface-2) px-6 py-4 sm:flex-row sm:justify-end sm:px-7">
                <VButton v-if="dialogState.type === 'confirm'" variant="secondary" @click="cancel">
                    {{ dialogState.cancelText }}
                </VButton>
                <button ref="confirmButton" type="button"
                    class="inline-flex items-center justify-center gap-2 rounded-full px-5 py-2.5 text-sm font-semibold text-white transition focus:outline-none focus-visible:ring-4 active:scale-[0.97]"
                    :class="tone.button" @click="confirm">
                    {{ dialogState.confirmText }}
                </button>
            </div>
        </div>
    </dialog>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import VButton from '@/components/VButton.vue'
import { dialogState, settleDialog } from '@/composables/useDialog'
import {
    CheckCircleIcon, ExclamationTriangleIcon, InformationCircleIcon, TrashIcon,
} from '@heroicons/vue/24/outline'

const dialog = ref(null)
const confirmButton = ref(null)
const titleId = 'app-dialog-title'
const messageId = 'app-dialog-message'

const TONES = {
    primary: {
        icon: InformationCircleIcon,
        badge: 'bg-(--app-accent-soft) text-(--app-accent-ink)',
        button: 'bg-primary-500 hover:bg-primary-600 focus-visible:ring-primary-500/40',
    },
    danger: {
        icon: TrashIcon,
        badge: 'bg-(--app-danger-soft) text-(--app-danger)',
        button: 'bg-red-600 hover:bg-red-700 focus-visible:ring-red-500/40',
    },
    warning: {
        icon: ExclamationTriangleIcon,
        badge: 'bg-[color-mix(in_srgb,var(--app-float)_14%,transparent)] text-(--app-float)',
        button: 'bg-primary-500 hover:bg-primary-600 focus-visible:ring-primary-500/40',
    },
    success: {
        icon: CheckCircleIcon,
        badge: 'bg-(--app-success-soft) text-(--app-success)',
        button: 'bg-primary-500 hover:bg-primary-600 focus-visible:ring-primary-500/40',
    },
}
const tone = computed(() => TONES[dialogState.tone] ?? TONES.primary)

const confirm = () => settleDialog(true)
const cancel = () => settleDialog(false)

watch(() => dialogState.open, async (open) => {
    if (!dialog.value) return
    if (open && !dialog.value.open) {
        dialog.value.showModal()
        await nextTick()
        // Bei Lösch-Abfragen nicht versehentlich mit Enter bestätigen
        if (dialogState.tone !== 'danger') confirmButton.value?.focus()
    } else if (!open && dialog.value.open) {
        dialog.value.close()
    }
})

// Escape gehört dem obersten Dialog – sonst würden darunterliegende Modals mitgeschlossen
const onKeydown = (e) => {
    if (e.key === 'Escape' && dialogState.open) {
        e.preventDefault()
        e.stopImmediatePropagation()
        dialogState.type === 'confirm' ? cancel() : confirm()
    }
}

let removeNavigateListener = null
onMounted(() => {
    window.addEventListener('keydown', onKeydown, true)
    // Seitenwechsel bricht offene Abfragen ab
    removeNavigateListener = router.on('navigate', () => dialogState.open && cancel())
})
onBeforeUnmount(() => {
    window.removeEventListener('keydown', onKeydown, true)
    removeNavigateListener?.()
})
</script>

<style scoped>
.app-dialog::backdrop {
    background: rgb(2 16 21 / 0.6);
    backdrop-filter: blur(4px);
}

.app-dialog[open] .app-dialog-panel {
    animation: app-dialog-in 0.22s cubic-bezier(0.2, 0.7, 0.2, 1);
}

.app-dialog[open]::backdrop {
    animation: app-dialog-fade 0.22s ease;
}

@keyframes app-dialog-in {
    from { opacity: 0; transform: translateY(10px) scale(0.97); }
    to { opacity: 1; transform: none; }
}

@keyframes app-dialog-fade {
    from { opacity: 0; }
}

@media (prefers-reduced-motion: reduce) {
    .app-dialog[open] .app-dialog-panel,
    .app-dialog[open]::backdrop { animation: none; }
}
</style>
