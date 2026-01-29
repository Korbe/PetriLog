<script setup>
import { ref, onMounted } from 'vue'
import Cookies from 'js-cookie'
import { useForm, usePage } from '@inertiajs/vue3'
import VButton from './VButton.vue'

const page = usePage()
const user = page.props.auth?.user

const showBanner = ref(false)
const showSuccessMessage = ref(false)
const cookieName = 'email_verification_banner_dismissed'
const cookieDurationHours = 24

function dismissBanner() {
    Cookies.set(cookieName, '1', { expires: cookieDurationHours / 24 })
    showBanner.value = false
}

onMounted(() => {
    if (user?.verified) return
    if (Cookies.get(cookieName)) return // Schon dismissed → nicht zeigen
    showBanner.value = true
})

const form = useForm({})

const sendAgain = () => {
    showSuccessMessage.value = false // Reset bei neuem Klick
    form.post(route('verification.send'), {
        onSuccess: () => {
            showSuccessMessage.value = true
        },
    })
}
</script>

<template>
    <div v-if="showBanner" class="bg-white border-1 dark:bg-gray-800 dark:text-gray-100 
               px-4 py-3 rounded-lg relative flex items-start sm:items-center sm:justify-between shadow-md">
        <p class="text-sm font-medium">
            Bitte bestätige deine <strong>E-Mail-Adresse</strong>.
            <span class="block sm:inline">Überprüfe dein Postfach!</span>

            <span v-if="showSuccessMessage" class="ml-2 text-green-500">
                Ein neuer Bestätigungslink ist auf dem Weg zu dir.
            </span>
        </p>

        <VButton @click="sendAgain" :disabled="form.processing" :loading="form.processing" class="mr-5">
            Erneut senden
        </VButton>

        <button @click="dismissBanner" class="absolute right-2 top-1/2 -translate-y-1/2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M10 9.293l3.646-3.647a.5.5 0 11.708.708L10.707 10l3.647 3.646a.5.5 0 11-.708.708L10 10.707l-3.646 3.647a.5.5 0 11-.708-.708L9.293 10 5.646 6.354a.5.5 0 11.708-.708L10 9.293z"
                    clip-rule="evenodd" />
            </svg>
        </button>
    </div>
</template>
