<script setup>
import SponsorCard from './SponsorCard.vue'

const companies = [
  { img: '/images/sponsor/falle.png',   name: 'Falle',    link: 'https://www.falle.at/' },
  { img: '/images/sponsor/falle.png',   name: 'Falle',    link: 'https://www.falle.at/' },
  { img: '/images/sponsor/falle.png',   name: 'Falle',    link: 'https://www.falle.at/' },
  { img: '/images/sponsor/falle.png',   name: 'Falle',    link: 'https://www.falle.at/' }
]
</script>

<template>
  <section class="relative before:absolute before:inset-0 before:h-[120%] before:pointer-events-none before:bg-linear-to-b before:from-gray-100 before:-z-10 py-12">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
      <div class="max-w-3xl mx-auto text-center mb-6">
        <h2 class="text-3xl md:text-4xl font-bold">Petrilog wird unterstützt von diesen Firmen</h2>
      </div>

      <div class="flex justify-center">
        <SponsorCard :company="companies[0]" />
      </div>

      <!-- <div class="relative flex justify-center max-w-[94rem] mx-auto">
        <div class="w-full inline-flex flex-nowrap [mask-image:_linear-gradient(to_right,transparent_0,_black_10%,_black_90%,transparent_100%)] py-6 md:py-10 group">
          <div class="flex items-start justify-center md:justify-start *:mx-3 animate-[infinite-scroll_60s_linear_infinite] group-hover:[animation-play-state:paused]">
            <SponsorCard v-for="(c, i) in companies" :key="`c1-${i}`" :company="c" />
          </div>

          <div class="flex items-start justify-center md:justify-start *:mx-3 animate-[infinite-scroll_60s_linear_infinite] group-hover:[animation-play-state:paused]" aria-hidden="true">
            <SponsorCard v-for="(c, i) in companies" :key="`c2-${i}`" :company="c" />
          </div>
        </div>
      </div>-->
    </div> 
  </section>
</template>