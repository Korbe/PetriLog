<script setup>
// Shell for all auth pages (login, register, password, verification …) in the public site design.
import { Head, usePage } from '@inertiajs/vue3';
import DepthContours from '@/components/public/DepthContours.vue';
import FishingFloat from '@/components/public/FishingFloat.vue';

defineProps({
    eyebrow: { type: String, default: '' },
    title: { type: String, default: '' },
});

// Flash-Fehler (z.B. abgelaufene Sitzung) – das Dashboard zeigt sie über das Banner, hier direkt über dem Formular
const page = usePage();
</script>

<template>
    <div class="petri-site petri-auth min-h-screen lg:grid lg:grid-cols-[minmax(0,5fr)_minmax(0,6fr)]">
        <Head>
            <link rel="preconnect" href="https://fonts.bunny.net" head-key="site-fonts-preconnect" />
            <link rel="stylesheet" head-key="site-fonts"
                href="https://fonts.bunny.net/css?family=manrope:500,600,700|schibsted-grotesk:400,500,600,700|jetbrains-mono:400,500&display=swap" />
        </Head>

        <!-- Brand panel -->
        <aside
            class="ph-grain relative isolate overflow-hidden bg-(--lake-900) px-6 pb-14 pt-6 text-(--paper) sm:px-10 lg:sticky lg:top-0 lg:flex lg:h-screen lg:flex-col lg:justify-between lg:px-14 lg:py-12">
            <div class="absolute inset-0 -z-20 bg-[radial-gradient(ellipse_70%_60%_at_80%_30%,rgba(17,141,240,0.3),transparent_70%),linear-gradient(180deg,var(--lake-950),var(--lake-900)_55%,var(--lake-800))]"
                aria-hidden="true"></div>
            <DepthContours class="absolute inset-0 -z-10 h-full w-full opacity-80" :labels="false" />

            <Link href="/" class="group inline-flex items-center gap-2.5" aria-label="PetriLog – zur Startseite">
                <img src="/logo-short.png" alt="" width="34" height="34"
                    class="size-8 transition-transform duration-500 group-hover:-rotate-12" />
                <span class="text-[1.35rem] font-medium tracking-tight">PetriLog</span>
            </Link>

            <div class="hidden lg:block">
                <p class="ph-rise font-log text-xs uppercase tracking-[0.22em] text-(--paper)/60">Petri Heil!</p>
                <p class="ph-rise font-display mt-5 text-6xl leading-[0.95] xl:text-7xl" style="animation-delay: 0.08s">
                    Jeder Fang verdient seine <em class="text-primary-300">Geschichte.</em>
                </p>
                <div class="ph-rise mt-10 inline-flex flex-col rounded-2xl border border-white/15 bg-(--lake-950)/60 px-5 py-4 backdrop-blur-md"
                    style="animation-delay: 0.16s">
                    <span class="font-display text-2xl leading-none">Regenbogenforelle</span>
                    <span class="font-log mt-2 text-xs text-primary-200">45 cm · 989 g · Faaker See</span>
                </div>
            </div>

            <div class="font-log hidden items-end justify-between text-[11px] uppercase tracking-[0.15em] text-(--paper)/45 lg:flex">
                <div class="flex items-end gap-8">
                    <FishingFloat :size="20" />
                    <span>&copy; PetriLog.com</span>
                </div>
                <div class="flex gap-5">
                    <Link href="/impressum" class="transition hover:text-white">Impressum</Link>
                    <Link href="/privacy-policy" class="transition hover:text-white">Datenschutz</Link>
                </div>
            </div>

            <!-- water edge (mobile) -->
            <svg class="absolute inset-x-0 bottom-0 h-6 w-full lg:hidden" viewBox="0 0 1440 40" preserveAspectRatio="none"
                aria-hidden="true">
                <path d="M0 22 C240 8 480 8 720 22 S1200 36 1440 22 V40 H0 Z" fill="var(--paper)" />
            </svg>
        </aside>

        <!-- Form -->
        <main class="flex justify-center px-4 py-12 sm:px-6 lg:items-center lg:py-16">
            <div class="w-full max-w-md">
                <p v-if="eyebrow" class="font-log text-xs uppercase tracking-[0.22em] text-(--ink)/55">{{ eyebrow }}</p>
                <h1 v-if="title" class="font-display mt-3 text-5xl leading-[1] sm:text-6xl">{{ title }}</h1>
                <div v-if="$slots.intro" class="mt-4 leading-relaxed text-(--ink)/65">
                    <slot name="intro" />
                </div>

                <div class="mt-10">
                    <p v-if="page.props.flash?.error" role="alert"
                        class="mb-6 rounded-xl bg-(--app-danger-soft) px-4 py-3 text-sm font-medium text-(--app-danger)">
                        {{ page.props.flash.error }}
                    </p>
                    <slot />
                </div>
            </div>
        </main>
    </div>
</template>
