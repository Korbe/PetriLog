<script setup>
import Modal from './Modal.vue';

const emit = defineEmits(['close']);

defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
});

const close = () => {
    emit('close');
};
</script>

<template>
    <Modal
        :show="show"
        :max-width="maxWidth"
        :closeable="closeable"
        @close="close"
    >
        <div class="px-6 pb-4 pt-6 sm:px-7 sm:pt-7">
            <div class="sm:flex sm:items-start sm:gap-4">
                <div class="mx-auto flex size-12 shrink-0 items-center justify-center rounded-2xl bg-(--app-danger-soft) sm:mx-0">
                    <svg class="size-6 text-(--app-danger)" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>

                <div class="mt-4 text-center sm:mt-0 sm:text-start">
                    <h3 class="font-display text-3xl leading-tight text-(--app-ink)">
                        <slot name="title" />
                    </h3>

                    <div class="mt-3 text-sm leading-relaxed text-(--app-muted)">
                        <slot name="content" />
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-row flex-wrap justify-end gap-2 border-t border-(--app-border) bg-(--app-surface-2) px-6 py-4">
            <slot name="footer" />
        </div>
    </Modal>
</template>
