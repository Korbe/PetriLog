<template>
    <div ref="root" class="petri-site flex min-h-screen flex-col overflow-hidden supports-[overflow:clip]:overflow-clip">
        <Head>
            <link rel="preconnect" href="https://fonts.bunny.net" head-key="site-fonts-preconnect" />
            <link rel="stylesheet" head-key="site-fonts"
                href="https://fonts.bunny.net/css?family=manrope:500,600,700|schibsted-grotesk:400,500,600,700|jetbrains-mono:400,500&display=swap" />
        </Head>

        <!-- Site header -->
        <Header :overlay="overlay" />

        <!-- Page content -->
        <main class="grow" :class="overlay ? '' : 'pt-20'">
            <slot />
        </main>

        <CookieBanner />

        <!-- Site footer -->
        <Footer />
    </div>
</template>
<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { Head } from '@inertiajs/vue3';
import Header from './Header/Header.vue';
import Footer from './Footer/Footer.vue';
import CookieBanner from '@/components/CookieBanner.vue';

defineProps({
    // The page starts with a dark hero, so the header can sit transparently on top of it.
    overlay: { type: Boolean, default: false },
});

const root = ref(null);
let observer = null;

// Scroll reveal: content stays visible without JS (SSR), it is only hidden
// once the observer is ready so nothing ends up permanently invisible.
onMounted(() => {
    if (!('IntersectionObserver' in window)) return;

    observer = new IntersectionObserver((entries) => {
        for (const entry of entries) {
            if (!entry.isIntersecting) continue;
            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
        }
    }, { rootMargin: '0px 0px -10% 0px', threshold: 0.1 });

    root.value.classList.add('ph-anim-ready');
    root.value.querySelectorAll('.ph-reveal').forEach((el) => observer.observe(el));
});

onBeforeUnmount(() => observer?.disconnect());
</script>
