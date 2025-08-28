<template>
  <PageWrapper title="Fang">
    <template v-slot:actions>
    </template>

    <div class="max-w-6xl mx-auto">

      <div class="md:hidden grid grid-cols-2 gap-2 mx-2">
        <!-- Erstes Bild groß über 2 Spalten -->
        <div class="col-span-2">
          <img v-if="props.catched.media[0]" @click="openLightbox(0)" :src="props.catched.media[0].original_url"
            class="w-full h-64 object-cover rounded-lg cursor-pointer" />
        </div>

        <!-- Restliche Bilder dynamisch -->
        <div v-for="(media, index) in props.catched.media.slice(1)" :key="media.id" class="col-span-1">
          <img @click="openLightbox(index + 1)" :src="media.original_url"
            class="w-full h-32 object-cover rounded-lg cursor-pointer" />
        </div>
      </div>

      <div class="hidden md:block">
        <ul role="list"
          class="px-5  md:mx-auto my-5 grid w-full grid-cols-3 gap-x-4 gap-y-4 sm:grid-cols-3 lg:grid-cols-3">
          <li v-for="(media, index) in props.catched.media" :key="media.name">
            <img @click="openLightbox(index)" class="aspect-square sm:aspect-[3/2] w-full rounded-lg object-cover"
              :src="media.original_url" alt="" />
          </li>
          <vue-easy-lightbox v-if="isLightboxOpen" :visible="isLightboxOpen"
            :imgs="props.catched.media.map(item => item.original_url)" :index="currentImageIndex"
            @hide="closeLightbox" />
        </ul>
      </div>

      <div class=" mx-2 mt-5">
        <div class="p-3 bg-white dark:bg-gray-800 border-1 dark:border-0 rounded-lg w-full">
          <h1 class="text-5xl text-primary-500">{{ catched.name }}</h1>
          <div class="flex justify-between">
            <p class="text-xl">{{ formatDate(catched.date) }} - {{ catched.waters }}</p>
          </div>
        </div>
      </div>

      <div class="mx-2 mt-5">
        <div class="w-full flex justify-around space-x-5">
          <VButton v-if="catched != null" class="w-full" :href="editUrl">Bearbeiten</VButton>
          <VButton class="w-full" @click="openShare"><span class="flex">
              <ShareIcon class="w-4 mr-2" />Teilen
            </span></VButton>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-800 px-4 sm:px-6 lg:px-8 border-1 dark:border-0  mt-5 m-2 rounded-lg">
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
                      catched.length +
                      "cm" : 'n/a ' }}</td>
                  </tr>

                  <tr>
                    <td
                      class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-gray-300  sm:pl-0">
                      Gewicht
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-300">{{ catched.weight ?
                      catched.weight +
                      "g" : 'n/a ' }}</td>
                  </tr>

                  <tr>
                    <td
                      class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-gray-300  sm:pl-0">
                      Tiefe</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-300">{{ catched.depth ?
                      catched.depth +
                      "cm" : 'n/a ' }}</td>
                  </tr>

                  <tr>
                    <td
                      class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-gray-300  sm:pl-0">
                      Temperatur
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-300">{{
                      catched.temperature
                        ?
                      catched.temperature + "°C" : 'n/a ' }}</td>
                  </tr>

                  <tr>
                    <td
                      class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-gray-300  sm:pl-0">
                      Luftdruck
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-300">{{
                      catched.air_pressure
                        ?
                      catched.air_pressure + "hPa" : 'n/a' }}</td>
                  </tr>

                  <tr>
                    <td
                      class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-gray-300  sm:pl-0">
                      Köder</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-300">{{ catched.bait ?
                      catched.bait :
                      'n/a' }}</td>
                  </tr>

                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-lg border-1 dark:border-0  mx-2 mt-5">
        <div v-if="catched.remark" class=" w-full   p-5">
          <p>{{ catched.remark }}</p>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-lg mx-2 mt-5 mb-20 border-1 dark:border-0  p-5">
        <p class="mb-5"><b>Hier gefangen</b><br> {{ catched.address }}</p>
        <div class="rounded-2xl  md:w-full" ref="map" style="width: 100%; height: 400px;"></div>

      </div>
    </div>

    <ShareDialog v-model="isShareOpen" :share-url="shareUrl" />
  </PageWrapper>
</template>
<script setup>
import { ref, onMounted, watch } from 'vue';
import VButton from '@/components/VButton.vue';
import VueEasyLightbox from 'vue-easy-lightbox';
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
import { ShareIcon } from '@heroicons/vue/24/solid';
import ShareDialog from "@/components/ShareDialog.vue";

const props = defineProps({
  catched: Object,
});

const map = ref(null);
const isLightboxOpen = ref(false);
const currentImageIndex = ref(0);
const isShareOpen = ref(false);
let gmap = null;

const openShare = () => {
  isShareOpen.value = true;
};

const loadGoogleMaps = () => {
  return new Promise((resolve, reject) => {
    if (window.google && window.google.maps) {
      resolve(window.google.maps);
      return;
    }

    // Script nur einmal anhängen
    if (document.getElementById("google-maps")) {
      window._initMapCallback = () => resolve(window.google.maps);
      return;
    }

    window._initMapCallback = () => resolve(window.google.maps);

    const script = document.createElement("script");
    script.id = "google-maps";
    script.src = "https://maps.googleapis.com/maps/api/js?key=AIzaSyDz9ywPxkkW1oOy70Rab2oqnhF02DLe5MA&libraries=marker&callback=_initMapCallback";
    script.async = true;
    script.defer = true;
    script.onerror = reject;
    document.head.appendChild(script);
  });
};

const initMap = (maps) => {
  if (!map.value || !props.catched.latitude || !props.catched.longitude) return;

  const latitude = parseFloat(props.catched.latitude);
  const longitude = parseFloat(props.catched.longitude);

  gmap = new maps.Map(map.value, {
    center: { lat: latitude, lng: longitude },
    zoom: 16,
    mapId: '6da85ff10ebc18655d496f80',
  });

  const { AdvancedMarkerElement } = maps.marker;

  new AdvancedMarkerElement({
    position: { lat: latitude, lng: longitude },
    map: gmap,
    title: props.catched.name ?? 'Markierter Punkt',
  });
};

const shareUrl = ref("");
const editUrl = ref("");

onMounted(async () => {
  shareUrl.value = route('public.catched.show', props.catched.id);
  editUrl.value = route('catched.edit', props.catched.id);

  try {
    const maps = await loadGoogleMaps();
    initMap(maps);
  } catch (err) {
    console.error("Google Maps konnte nicht geladen werden:", err);
  }
});

watch(() => props.catched, async () => {
  if (window.google && window.google.maps) {
    initMap(window.google.maps);
  } else {
    try {
      const maps = await loadGoogleMaps();
      initMap(maps);
    } catch (err) {
      console.error("Google Maps konnte nicht geladen werden:", err);
    }
  }
});

const formatDate = (dateString) => {
  const date = new Date(dateString);
  const day = String(date.getDate()).padStart(2, '0');
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const year = date.getFullYear();
  const hours = String(date.getHours()).padStart(2, '0');
  const minutes = String(date.getMinutes()).padStart(2, '0');
  return `${day}.${month}.${year} ${hours}:${minutes}`;
};

const openLightbox = (index) => {
  currentImageIndex.value = index;
  isLightboxOpen.value = true;
};

const closeLightbox = () => {
  isLightboxOpen.value = false;
};
</script>
