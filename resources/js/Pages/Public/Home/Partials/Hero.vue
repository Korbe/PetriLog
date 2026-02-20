<template>
  <section class="relative">

    <PageIllustration />

    <div class="max-w-6xl mx-auto px-4 sm:px-6">

      <!-- Hero content -->
      <div class="pt-32 pb-12 md:pt-8 md:pb-20">

        <!-- Section header -->
        <div class="text-center pb-12 md:pb-16">
          <h1 class="text-5xl md:text-6xl font-bold mb-6 " data-aos="zoom-y-out" data-aos-delay="150">Petri Heil! Fang
            dokumentieren, Erinnerungen bewahren.</h1>
          <div class="max-w-3xl mx-auto">
            <p class="text-lg text-gray-700 mb-8" data-aos="zoom-y-out" data-aos-delay="300">PetriLog hilft dir, deine
              schönsten Fänge in Österreich zu dokumentieren und mit Freunden zu teilen.</p>
            <div
              class="relative before:absolute before:inset-0 before:border-y before:[border-image:linear-gradient(to_right,transparent,--theme(--color-slate-300/.8),transparent)1]">
              <div class="relative max-w-xs mx-auto sm:max-w-none sm:flex sm:justify-center" data-aos="zoom-y-out"
                data-aos-delay="450">
                <Link
                  class="btn text-white bg-linear-to-t bg-primary-500 bg-[length:100%_100%] hover:bg-[length:100%_150%] bg-[bottom] shadow-sm w-full mb-4 sm:w-auto sm:mb-0 group"
                  href="/register">
                <span class="relative inline-flex items-center"> Jetzt Fang eintragen </span>
                </Link>
              </div>
            </div>
          </div>
        </div>

        <!-- Desktop: statische Grid-Ansicht -->
        <div class="hidden md:grid md:grid-cols-5 gap-6 max-w-[94rem] mx-auto py-10">
          <img v-for="(s, i) in screenshots" :key="`desktop-${i}`" :src="s.img"
            class="h-96 w-auto cursor-pointer rounded-xl shadow-lg object-contain" @click="open(s.img)" />
        </div>

        <!-- Mobile: horizontale Scroll-Slideshow -->
        <div class="md:hidden overflow-x-auto snap-x snap-mandatory flex gap-6 px-6 py-10">
          <img v-for="(s, i) in screenshots" :key="`mobile-${i}`" :src="s.img"
            class="snap-center shrink-0 w-auto h-[70vh] cursor-pointer rounded-xl shadow-lg object-contain"
            @click="open(s.img)" />
        </div>

        <!-- Lightbox -->
        <transition name="fade">
          <div v-if="activeImage" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4"
            @click.self="close">
            <img :src="activeImage" class="max-h-[90vh] max-w-[90vw] rounded-xl shadow-2xl" />
          </div>
        </transition>
      </div>
    </div>
  </section>
</template>
<script setup>
import PageIllustration from "../../PageIllustration.vue";

import { ref, onMounted, onUnmounted } from 'vue'

const screenshots = [
  { img: '/images/icons/screenshots/Screenshot_1.png' },
  { img: '/images/icons/screenshots/Screenshot_2.png' },
  { img: '/images/icons/screenshots/Screenshot_3.png' },
  { img: '/images/icons/screenshots/Screenshot_4.png' },
  { img: '/images/icons/screenshots/Screenshot_5.png' },
]

const activeImage = ref(null)

const open = img => {
  activeImage.value = img
  document.body.style.overflow = 'hidden'
}

const close = () => {
  activeImage.value = null
  document.body.style.overflow = ''
}

const onKey = e => {
  if (e.key === 'Escape') close()
}

onMounted(() => window.addEventListener('keydown', onKey))
onUnmounted(() => window.removeEventListener('keydown', onKey))
</script>
<style>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.25s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>