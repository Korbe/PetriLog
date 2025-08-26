<script setup>
import { ref, onMounted } from 'vue'
import Cookies from 'js-cookie'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const user = page.props.auth?.user

const showBanner = ref(false)
const cookieName = 'trial_banner_dismissed'
const cookieDurationHours = 24

function dismissBanner() {
    Cookies.set(cookieName, '1', { expires: cookieDurationHours / 24 })
    showBanner.value = false
}

onMounted(() => {
    if (!user?.onTrial) return // Kein Trial → kein Banner
    if (Cookies.get(cookieName)) return // Schon dismissed → nicht zeigen

    showBanner.value = true
})
</script>

<template>
    <div v-if="showBanner" class="bg-yellow-100 border border-yellow-300 text-yellow-900 
         dark:bg-yellow-900 dark:border-yellow-700 dark:text-yellow-100 
         px-4 py-3 rounded-lg relative flex items-start sm:items-center sm:justify-between shadow-md">
        <p class="text-sm font-medium">
            Du befindest dich in der <strong>Testphase</strong>, diese läuft am
            <span class="font-semibold">
                {{ new Date(user.trialEndsAt).toLocaleDateString() }}
            </span> aus.
        </p>
        <button @click="dismissBanner" class="absolute right-2 top-1/2 -translate-y-1/2 
         text-yellow-700 hover:text-yellow-900 
         dark:text-yellow-300 dark:hover:text-yellow-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M10 9.293l3.646-3.647a.5.5 0 11.708.708L10.707 10l3.647 3.646a.5.5 0 11-.708.708L10 10.707l-3.646 3.647a.5.5 0 11-.708-.708L9.293 10 5.646 6.354a.5.5 0 11.708-.708L10 9.293z"
                    clip-rule="evenodd" />
            </svg>
        </button>
    </div>
</template>