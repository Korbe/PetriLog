<template>
  <PageWrapper title="Fang" :backTo="route('app.catched.index')">
    <template v-slot:actions></template>

    <div class="max-w-6xl mx-auto">

      <!-- 📱 Mobile: horizontale Scroll-Slideshow -->
      <div v-if="media.length" class="md:hidden overflow-x-auto snap-x snap-mandatory flex gap-4 px-4">
        <img v-for="(m, i) in media" :key="`mobile-${i}`" :src="m.original_url"
          class="snap-center shrink-0 w-[85vw] h-64 object-cover rounded-lg cursor-pointer" @click="openLightbox(i)" />
      </div>

      <!-- 🖥 Desktop: Grid -->
      <div v-if="media.length" class="hidden md:grid grid-cols-[repeat(auto-fit,minmax(220px,1fr))] 
           gap-4 px-3 my-6">
        <img v-for="(m, i) in media" :key="`desktop-${i}`" :src="m.original_url"
          class="aspect-[3/2] w-full object-cover rounded-lg cursor-pointer" @click="openLightbox(i)" />
      </div>

      <!-- 🔍 Lightbox -->
      <transition name="fade">
        <div v-if="activeIndex !== null" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4"
          @click.self="closeLightbox">
          <img :src="media[activeIndex].original_url" class="max-h-[90vh] max-w-[90vw] rounded-xl shadow-2xl" />

          <!-- ❌ Close Button -->
          <button class="absolute top-5 right-5 text-white text-3xl" @click="closeLightbox">
            ×
          </button>

          <!-- ◀️▶️ Navigation -->
          <button v-if="media.length > 1" class="absolute left-5 top-1/2 transform -translate-y-1/2 text-white text-4xl"
            @click.stop="prevImage">
            ‹
          </button>
          <button v-if="media.length > 1"
            class="absolute right-5 top-1/2 transform -translate-y-1/2 text-white text-4xl" @click.stop="nextImage">
            ›
          </button>
        </div>
      </transition>

      <!-- Fang Info -->
      <div class="mx-2 mt-5">
        <div class="p-3 bg-white dark:bg-gray-800 border-1 dark:border-0 rounded-lg w-full">
          <h1 class="text-5xl text-primary-500">
            {{ catched.fish?.name ?? 'Unbekannter Fisch' }}
          </h1>
          <div class="flex justify-between">
            <p class="text-xl">
              {{ catched.lake?.name ?? catched.river?.name ?? 'Unbekanntes Gewässer' }} · {{ formatDate(catched.date) }}
            </p>
          </div>

          <!-- Buttons -->
          <div class="mx-2 mt-5">
            <div class="w-full flex justify-around space-x-5">
              <VButton v-if="catched != null" class="w-full" :href="editUrl">Bearbeiten</VButton>
              <VButton class="w-full" @click="openShare">
                <span class="flex">
                  <ShareIcon class="w-4 mr-2" />Teilen
                </span>
              </VButton>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabelleninfos -->
      <div class="bg-white dark:bg-gray-800 px-4 sm:px-6 lg:px-8 border-1 dark:border-0 mt-5 m-2 rounded-lg">
        <div class="flow-root">
          <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
              <table class="relative min-w-full divide-y divide-gray-300">
                <tbody class="divide-y divide-gray-200">
                  <tr>
                    <td
                      class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-gray-300 sm:pl-0">
                      Länge</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-300">{{ catched.length ?
                      catched.length + "cm" : 'n/a ' }}</td>
                  </tr>
                  <tr>
                    <td
                      class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-gray-300 sm:pl-0">
                      Gewicht</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-300">{{ catched.weight ?
                      catched.weight + "g" : 'n/a ' }}</td>
                  </tr>
                  <tr>
                    <td
                      class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-gray-300 sm:pl-0">
                      Tiefe</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-300">{{ catched.depth ?
                      catched.depth + "cm" : 'n/a ' }}</td>
                  </tr>
                  <tr>
                    <td
                      class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-gray-300 sm:pl-0">
                      Temperatur</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-300">{{
                      catched.temperature
                        ? catched.temperature + "°C" : 'n/a ' }}</td>
                  </tr>
                  <tr>
                    <td
                      class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-gray-300 sm:pl-0">
                      Luftdruck</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-300">{{
                      catched.air_pressure
                        ? catched.air_pressure + "hPa" : 'n/a' }}</td>
                  </tr>
                  <tr>
                    <td
                      class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-gray-300 sm:pl-0">
                      Köder</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-300">{{ catched.bait ??
                      'n/a' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Bemerkungen -->
      <div v-if="catched.remark" class="bg-white dark:bg-gray-800 rounded-lg border-1 dark:border-0 mx-2 mt-5">
        <div class="w-full p-5">
          <p v-html="catched.remark"></p>
        </div>
      </div>

      <!-- Karte -->
      <div class="bg-white dark:bg-gray-800 rounded-lg mx-2 mt-5 mb-20 border-1 dark:border-0 p-5">
        <p class="mb-5"><b>Hier gefangen</b><br> {{ catched.address }}</p>

        <div class="flex gap-2">
          <a :href="mapsLink" target="_blank"
            class="mb-5 btn cursor-pointer bg-primary-500 text-gray-100 hover:bg-primary-600 active:bg-primary-600 transition-all duration-150 dark:bg-primary-500 dark:text-gray-100 dark:hover:bg-primary-600 dark:active:bg-primary-600">
            Auf Google Maps anzeigen
          </a>
          <a :href="appleMapsLink" target="_blank"
            class="mb-5 btn cursor-pointer bg-primary-500 text-gray-100 hover:bg-primary-600 active:bg-primary-600 transition-all duration-150 dark:bg-primary-500 dark:text-gray-100 dark:hover:bg-primary-600 dark:active:bg-primary-600">
            Auf Apple Maps anzeigen
          </a>
        </div>

        <GoogleMap :latitude="catched.latitude" :longitude="catched.longitude" :title="catched.fish?.name ?? 'Fang'" />
      </div>
    </div>

    <ShareDialog v-model="isShareOpen" :share-url="shareUrl" />
  </PageWrapper>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import VButton from '@/components/VButton.vue';
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
import ShareDialog from "@/components/ShareDialog.vue";
import GoogleMap from '@/components/GoogleMap.vue';
import { ShareIcon } from '@heroicons/vue/24/solid';

const props = defineProps({
  catched: Object,
  shareUrl: String,
  editUrl: String,
});

const mapsLink = `https://www.google.com/maps/search/?api=1&query=${props.catched.latitude},${props.catched.longitude}`;
const appleMapsLink = `https://maps.apple.com/?ll=${props.catched.latitude},${props.catched.longitude}&q=${encodeURIComponent(props.catched.fish?.name ?? 'Fang')}`;

const isShareOpen = ref(false);

const openShare = () => { isShareOpen.value = true; };

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return `${String(date.getDate()).padStart(2, '0')}.${String(date.getMonth() + 1).padStart(2, '0')}.${date.getFullYear()} ${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`;
};

const media = computed(() => {
  return (props.catched?.media ?? []).slice() // Kopie, damit wir das Original nicht mutieren
    .sort((a, b) => a.order_column - b.order_column)
})

const activeIndex = ref(null)

const openLightbox = index => {
  activeIndex.value = index
  document.body.style.overflow = 'hidden'
}

const closeLightbox = () => {
  activeIndex.value = null
  document.body.style.overflow = ''
}

const prevImage = () => {
  if (activeIndex.value === null) return
  activeIndex.value = (activeIndex.value - 1 + media.value.length) % media.value.length
}

const nextImage = () => {
  if (activeIndex.value === null) return
  activeIndex.value = (activeIndex.value + 1) % media.value.length
}

const onKey = e => {
  if (e.key === 'Escape') closeLightbox()
}

onMounted(() => window.addEventListener('keydown', onKey))
onUnmounted(() => window.removeEventListener('keydown', onKey))

const catched = props.catched;
</script>
