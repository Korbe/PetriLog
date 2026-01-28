<template>
    <PageWrapper title="App installieren" backTo="/dashboard">


        <div class="w-full md:w-1/2 mx-auto">
            <div class="bg-white dark:bg-gray-800 p-3 md:p-10 rounded-b-lg">

                <h1 class="text-center font-bold text-4xl mb-2">App zum Startbildschirm hinzufügen</h1>

                <div class="flex justify-center">
                    <PwaInstallButton class="mx-auto my-5" />
                </div>

                <p class="pt-5 mb-5">
                    PetriLog ist eine Progressive Web App (PWA). Das bedeutet, dass du PetriLog sowohl im Web als auch
                    auf mobilen Geräten nutzen kannst, als wäre es eine App aus dem App Store.
                </p>
                <p>
                    Füge PetriLog zu deinem Startbildschirm hinzu, um schnellen Zugriff zu haben und die App im
                    Vollbildmodus zu verwenden.
                </p>

                <h2 class="font-bold text-2xl mt-5">Unter Chrome (Android)</h2>
                <p class="pt-2 mb-5">Klicke auf das Drei-Punkte-Menü in der oberen rechten Ecke und wähle
                    "Zum Startbildschirm hinzufügen".</p>
                <div class="flex justify-center">
                    <img src="/images/pwa/pwa-android.png" alt="PWA Installation in Chrome"
                        class="mb-5 rounded-lg shadow-md h-[50rem]" />
                </div>
                <h2 class="font-bold text-2xl mt-5">Unter Safari (iOS)</h2>
                <p class="pt-2 mb-5">Klicke auf das Teilen-Symbol (Quadrat mit Pfeil nach oben) und wähle
                    "Zum Home-Bildschirm hinzufügen".</p>
                <div class="flex justify-center">
                    <img src="/images/pwa/pwa-ios.jpg" alt="PWA Installation in IOS"
                        class="mb-5 rounded-lg shadow-md h-[50rem]" />
                </div>

                <p>Bei Fragen kontaktiere bitte den Support unter <a class="text-primary-500 underline"
                        href="mailto:info@petrilog.com">info@petrilog.com</a>.</p>
            </div>
        </div>

    </PageWrapper>
</template>
<script setup>
import PwaInstallButton from '@/components/PwaInstallButton.vue';
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
import { ArrowRightIcon } from '@heroicons/vue/24/solid';
import { onMounted } from 'vue';

onMounted(() => {
    sessionStorage.setItem('lastOrigin', window.location.href);
});
</script>
