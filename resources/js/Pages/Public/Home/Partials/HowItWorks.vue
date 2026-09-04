<script setup>
const steps = [
  {
    number: '1',
    title: 'Fang machen',
    text: 'Petri Heil! Du hast einen Fisch gelandet – jetzt heißt es Rute weglegen und Handy zücken.',
  },
  {
    number: '2',
    title: 'Details erfassen',
    text: 'Fischart, Länge, Gewicht, Köder, Wassertiefe, Temperatur, Standort und bis zu 3 Fotos in wenigen Klicks eintragen.',
  },
  {
    number: '3',
    title: 'Erinnerung sichern',
    text: 'Dein Fang landet automatisch in deinem privaten Tagebuch – jederzeit abrufbar und optional mit Freunden teilbar.',
  },
]
</script>

<template>
  <section class="relative">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
      <div class="py-12 md:py-20 border-t border-gray-200">

        <!-- Section header -->
        <div class="max-w-3xl mx-auto text-center pb-12 md:pb-16">
          <h2 class="text-3xl md:text-4xl font-bold mb-4">In drei Schritten zum digitalen Fangbuch</h2>
          <p class="text-lg text-gray-700">Kein Papier, kein Aufwand – PetriLog begleitet dich direkt an Fluss und See.</p>
        </div>

        <!-- Steps -->
        <div class="relative grid gap-10 sm:grid-cols-3" data-aos="zoom-y-out" data-aos-delay="150">
          <div class="hidden sm:block absolute top-6 left-[16.6%] right-[16.6%] h-px bg-linear-to-r from-transparent via-slate-300 to-transparent" aria-hidden="true"></div>
          <div v-for="step in steps" :key="step.number" class="relative text-center sm:text-left">
            <div class="mx-auto sm:mx-0 mb-5 inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary-500 text-white text-lg font-bold shadow-sm ring-4 ring-white">
              {{ step.number }}
            </div>
            <h3 class="font-medium text-lg mb-2">{{ step.title }}</h3>
            <p class="text-[15px] text-gray-700">{{ step.text }}</p>
          </div>
        </div>

      </div>
    </div>
  </section>
</template>
