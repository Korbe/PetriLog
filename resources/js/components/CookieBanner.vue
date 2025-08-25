<script setup>
import { ref, onMounted } from 'vue';
import Cookies from 'js-cookie';
import { Link } from '@inertiajs/vue3';

const showBanner = ref(false);
const cookieName = 'petriCookieConsent';
const cookieDurationDays = 365;

const acceptCookies = () => {
    Cookies.set(cookieName, '1', { expires: cookieDurationDays, path: '/' });
    showBanner.value = false;
};

onMounted(() => {
    if (!Cookies.get(cookieName)) {
        showBanner.value = true;
    }
});
</script>

<template>
    <div v-if="showBanner"
        class="fixed bottom-0 inset-x-0 bg-gray-800 text-white p-4 flex items-center justify-between shadow-lg z-50">
        <p class="text-sm">
            Wenn du diese Seite nutzt, stimmst du unserer
            <Link :href="route('policy.show')" class="underline hover:text-gray-300">Datenschutzerklärung</Link> zu.
        </p>
        <button @click="acceptCookies" class="ml-4 text-white hover:text-gray-300 focus:outline-none">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M10 9.293l3.646-3.647a.5.5 0 11.708.708L10.707 10l3.647 3.646a.5.5 0 11-.708.708L10 10.707l-3.646 3.647a.5.5 0 11-.708-.708L9.293 10 5.646 6.354a.5.5 0 11.708-.708L10 9.293z"
                    clip-rule="evenodd" />
            </svg>
        </button>
    </div>
</template>
