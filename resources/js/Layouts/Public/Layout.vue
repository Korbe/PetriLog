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
</script>