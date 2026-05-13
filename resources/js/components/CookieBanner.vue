<template>
    <Transition name="fade">
        <div v-if="visible" class="fixed bottom-6 left-1/2 z-[9999] w-[95%] max-w-xl -translate-x-1/2">
            <div
                class="border border-neutral-800 bg-neutral-900/95 backdrop-blur rounded-2xl shadow-2xl overflow-hidden">

                <!-- Content -->
                <div class="p-6">

                    <!-- Top -->
                    <div class="flex items-start gap-4 mb-5">

                        <!-- Cookie Placeholder -->
                        <div
                            class="flex items-center justify-center h-14 w-14 rounded-xl bg-neutral-800 border border-neutral-700 shrink-0">
                            🍪
                        </div>

                        <div>
                            <h2 class="text-white text-lg font-semibold mb-2">
                                Cookies & Datenschutz
                            </h2>

                            <p class="text-neutral-400 text-sm leading-relaxed">
                                Diese Website verwendet Cookies um die Benutzererfahrung zu verbessern. Du kannst selbst entscheiden, ob du zustimmen möchtest. Google Maps wird nicht funktionieren wenn du ablehnst.
                            </p>
                        </div>

                    </div>

                    <!-- Buttons -->
                    <div class="flex flex-col sm:flex-row gap-3">

                       <!-- Reject -->
                        <button @click="reject"
                            class="flex-1 border border-neutral-700 text-neutral-300 py-3 rounded-xl hover:border-neutral-500 transition">
                            Ablehnen
                        </button>

                        <!-- Accept -->
                        <button @click="accept"
                            class="flex-1 transition px-4 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-primary-500 hover:bg-primary-600 sm:px-8
                            
                            ">
                            Akzeptieren
                        </button>

 

                    </div>

                    <!-- Footer -->
                    <div class="mt-4 text-xs text-neutral-500 text-center">
                        Mehr Infos findest du in der <a href="/privacy-policy" class="text-brand underline">Datenschutzerklärung</a>.
                    </div>

                </div>

            </div>
        </div>
    </Transition>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { initAnalytics, initGoogleMaps } from '@/google.js'

const visible = ref(false)

onMounted(() => {
    const consent = localStorage.getItem('cookie-consent')

    if (consent === 'accepted') {
        initAnalytics()
        initGoogleMaps()
    } else if (!consent) {
        visible.value = true
    }
})

function accept() {
    localStorage.setItem('cookie-consent', 'accepted')
    initAnalytics();
    initGoogleMaps();
    visible.value = false
}

function reject() {
    localStorage.setItem('cookie-consent', 'rejected')
    visible.value = false
}
</script>