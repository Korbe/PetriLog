<script setup>
import {
  PhotoIcon,
  TagIcon,
  ArrowsUpDownIcon,
  ScaleIcon,
  BeakerIcon,
  ChevronDoubleDownIcon,
  FireIcon,
  MapPinIcon,
} from '@heroicons/vue/24/solid';

const fields = [
  { icon: PhotoIcon, label: 'Bis zu 3 Fotos', text: 'Halte den schönsten Winkel deines Fangs fest.' },
  { icon: TagIcon, label: 'Fischart', text: 'Aus der Fischdatenbank auswählen oder frei eintragen.' },
  { icon: ArrowsUpDownIcon, label: 'Länge', text: 'Präzise in Zentimetern dokumentieren.' },
  { icon: ScaleIcon, label: 'Gewicht', text: 'Dein Rekordfisch bleibt nachvollziehbar.' },
  { icon: BeakerIcon, label: 'Köder', text: 'Welcher Köder hat heute funktioniert?' },
  { icon: ChevronDoubleDownIcon, label: 'Wassertiefe', text: 'Auf welcher Tiefe hat es gebissen?' },
  { icon: FireIcon, label: 'Wassertemperatur', text: 'Bedingungen für den nächsten Erfolg merken.' },
  { icon: MapPinIcon, label: 'Standort', text: 'Der Spot wird per GPS automatisch gespeichert.' },
]
</script>

<template>
  <section class="relative">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
      <div class="py-12 md:py-20">

        <!-- Section header -->
        <div class="max-w-3xl mx-auto text-center pb-12 md:pb-16">
          <h2 class="text-3xl md:text-4xl font-bold mb-4">Alle Fangdaten, die wirklich zählen</h2>
          <p class="text-lg text-gray-700">Zu jedem Fang erfasst du genau die Details, die Angler wirklich interessieren.</p>
        </div>

        <!-- Grid -->
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4" data-aos="zoom-y-out" data-aos-delay="150">
          <div v-for="field in fields" :key="field.label"
            class="rounded-xl bg-white border border-gray-200 shadow-sm p-6 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
            <div class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-primary-50 mb-4">
              <component :is="field.icon" class="w-5 h-5 text-primary-500" />
            </div>
            <h3 class="font-medium mb-1">{{ field.label }}</h3>
            <p class="text-[15px] text-gray-700">{{ field.text }}</p>
          </div>
        </div>

      </div>
    </div>
  </section>
</template>
