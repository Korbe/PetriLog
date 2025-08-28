<script setup>
import { ref } from "vue";
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from "@headlessui/vue";
import { XMarkIcon, ClipboardIcon } from "@heroicons/vue/24/outline";
import VInput from "./VInput.vue";
import VButton from "./VButton.vue";

const props = defineProps({
    modelValue: Boolean,
    shareUrl: { type: String, required: true },
});

const emit = defineEmits(["update:modelValue"]);

const copied = ref(false);

const close = () => emit("update:modelValue", false);

const copyToClipboard = async () => {
    try {
        await navigator.clipboard.writeText(props.shareUrl);
        copied.value = true;
        setTimeout(() => (copied.value = false), 2000);
    } catch (err) {
        console.error("Fehler beim Kopieren:", err);
    }
};
</script>

<template>
    <TransitionRoot as="template" :show="props.modelValue">
        <Dialog as="div" class="relative z-50" @close="close">
            <!-- Overlay -->
            <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
                leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-black/30" />
            </TransitionChild>

            <!-- Content -->
            <div class="fixed inset-0 flex items-center justify-center p-4">
                <TransitionChild as="template" enter="ease-out duration-300"
                    enter-from="opacity-0 translate-y-4 scale-95" enter-to="opacity-100 translate-y-0 scale-100"
                    leave="ease-in duration-200" leave-from="opacity-100 translate-y-0 scale-100"
                    leave-to="opacity-0 translate-y-4 scale-95">
                    <DialogPanel class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                        <div class="flex justify-between items-center mb-4">
                            <DialogTitle class="text-lg font-medium text-gray-900">
                                Fang teilen
                            </DialogTitle>
                            <button @click="close" class="rounded-md p-2 text-gray-400 hover:text-gray-600">
                                <XMarkIcon class="w-5 h-5" />
                            </button>
                        </div>

                        <!-- Facebook Share -->
                        <div class="mb-4 flex space-x-2">
                            <a :href="`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}`"
                                target="_blank" rel="noopener"
                                class="flex w-full items-center space-x-2 px-4 py-2 bg-blue-600 text-white rounded-lg">
                                <span>Auf Facebook teilen</span>
                            </a>
                            <a :href="`https://twitter.com/intent/tweet?url=${encodeURIComponent(shareUrl)}&text=Schau was ich gefangen habe! - PetriLog`"
                                target="_blank" rel="noopener"
                                class="flex w-full items-center space-x-2 px-4 py-2 bg-gray-900 text-white rounded-lg">
                                <span>Auf Twitter teilen</span>
                            </a>
                        </div>

                        <!-- URL Copy -->
                         <p class="pb-2">Oder direkt den Link posten:</p>
                        <div class="flex items-center space-x-2">
                            
                            <VInput class="flex-1" :model-value="shareUrl" readonly />
                            <VButton @click="copyToClipboard" class="p-2 bg-gray-200 rounded-lg hover:bg-gray-300">
                                <ClipboardIcon class="w-5 h-5 text-white" />
                            </VButton>
                        </div>
                        <p v-if="copied" class="text-green-600 text-sm mt-2">URL kopiert!</p>
                    </DialogPanel>
                </TransitionChild>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
