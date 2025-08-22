<template>
  <section class="relative">

    <PageIllustration />

    <div class="max-w-6xl mx-auto px-4 sm:px-6">

      <!-- Hero content -->
      <div class="pt-32 pb-12 md:pt-8 md:pb-20">

        <!-- Section header -->
        <div class="text-center pb-12 md:pb-16">
          <h1
            class="text-5xl md:text-6xl font-bold mb-6 "
            data-aos="zoom-y-out" data-aos-delay="150">Petri Heil! Fang dokumentieren, Erinnerungen bewahren.</h1>
          <div class="max-w-3xl mx-auto">
            <p class="text-lg text-gray-700 mb-8" data-aos="zoom-y-out" data-aos-delay="300">PetriLog hilft dir, deine schönsten Fänge zu dokumentieren und mit Freunden zu teilen.</p>
            <div
              class="relative before:absolute before:inset-0 before:border-y before:[border-image:linear-gradient(to_right,transparent,--theme(--color-slate-300/.8),transparent)1]">
              <div class="relative max-w-xs mx-auto sm:max-w-none sm:flex sm:justify-center" data-aos="zoom-y-out"
                data-aos-delay="450">
                <a class="btn text-white bg-linear-to-t from-blue-600 to-blue-500 bg-[length:100%_100%] hover:bg-[length:100%_150%] bg-[bottom] shadow-sm w-full mb-4 sm:w-auto sm:mb-0 group"
                  href="#0">
                  <span class="relative inline-flex items-center">
                    Start Free Trial <span
                      class="tracking-normal text-blue-300 group-hover:translate-x-0.5 transition-transform ml-1">-&gt;</span>
                  </span>
                </a>
                <a class="btn text-gray-800 bg-white hover:bg-gray-50 shadow-sm w-full sm:w-auto sm:ml-4" href="#0">Learn
                  More</a>
              </div>
            </div>
          </div>
        </div>

        <!-- Hero image -->
        <div class="max-w-3xl mx-auto" data-aos="zoom-y-out" data-aos-delay="600">
          <img src="/images/public/petrilog_dashboard.png" class="border-2 border-gray-300 rounded-md">
        </div>

      </div>

    </div>
  </section>
</template>
<script setup>
import PageIllustration from "../../PageIllustration.vue";
</script>
