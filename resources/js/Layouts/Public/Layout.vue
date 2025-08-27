<template>

    <div class="flex flex-col min-h-screen overflow-hidden supports-[overflow:clip]:overflow-clip">

        <!-- Site header -->
        <Header />

        <!-- Page content -->
        <main class="grow">
            <slot />
        </main>

        <CookieBanner />

        <!-- Site footer -->
        <Footer />

    </div>
</template>
<script setup>
import Header from './Header/Header.vue';
import Footer from './Footer/Footer.vue';
import CookieBanner from '@/components/CookieBanner.vue';
import { onMounted } from 'vue'

onMounted(() => {
    if (typeof window !== 'undefined') {
        window.deferredPrompt = null;

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            window.deferredPrompt = e;
        });
    }
});
</script>