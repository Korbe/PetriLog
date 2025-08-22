<template>

  <Layout>

    <PageIllustration />

    <div class="max-w-6xl mt-20 md:mt-10 mx-auto ">

      <div class="gap-5 md:my-5">
        <!-- Erste Box: volle Breite auf Mobile -->
        <div class="w-full md:w-[32%] overflow-hidden rounded-lg px-5">
          <h1 class="mr-4 text-5xl text-primary-500">{{ catched.name }}</h1>
          <p class="text-xl">{{ formatDate(catched.date) }} - {{ catched.waters }}</p>
          <p>Gefangen von <b>{{ user }}</b></p>
        </div>

        <div class="flex flex-col md:flex-row w-full md:w-[50%] space-x-5 p-5">
          <div class="w-full md:w-[50%] rounded-lg py-3 space-y-3">
            <p><b>Länge:</b> {{ catched.length ?? 'n/a ' }}cm</p>
            <p><b>Gewicht:</b> {{ catched.weight ?? 'n/a ' }}g</p>
          </div>

          <div class="md:w-[50%] rounded-lg py-3 space-y-3">
            <p><b>Tiefe:</b> {{ catched.depth ?? 'n/a ' }}cm</p>
            <p><b>Temperatur:</b> {{ catched.temperature ?? 'n/a ' }}°C</p>
          </div>

          <div class="md:w-[50%] rounded-lg py-3 space-y-3">
            <p><b>Luftdruck:</b> {{ catched.air_pressure ?? 'n/a ' }}hPa</p>
            <p><b>Köder:</b> {{ catched.bait ?? 'n/a ' }}</p>
          </div>
        </div>
      </div>

      <ul role="list"
        class="px-5 md:mx-auto my-5 grid w-full grid-cols-3 gap-x-4 gap-y-4 sm:grid-cols-3 lg:grid-cols-3">
        <li v-for="(media, index) in props.catched.media" :key="media.name">
          <img @click="openLightbox(index)" class="aspect-square sm:aspect-[3/2] w-full rounded-lg object-cover"
            :src="media.original_url" alt="" />
        </li>
        <vue-easy-lightbox v-if="isLightboxOpen" :visible="isLightboxOpen"
          :imgs="props.catched.media.map(item => item.original_url)" :index="currentImageIndex" @hide="closeLightbox" />
      </ul>


      <div v-if="catched.remark" class="w-full bg-white dark:bg-gray-800 overflow-hidden rounded-lg p-5">
        <p>{{ catched.remark }}</p>
      </div>




      <div class="mb-20 mx-5">
        <p class="mb-5"><b>Adressse:</b><br> {{ catched.address }}</p>
        <div class="rounded-2xl  md:w-full" ref="map" style="width: 100%; height: 400px;"></div>

      </div>

      <CtaAlternative class="overflow-hidden" heading="Logge jetzt deine Fangmomente" buttonText="Registrieren"
        buttonLink="/register" />
    </div>




  </Layout>
</template>
<script setup>
import { ref, onMounted, watch } from 'vue';
import VueEasyLightbox from 'vue-easy-lightbox';
import Layout from '@/Layouts/Public/Layout.vue';
import PageIllustration from '../PageIllustration.vue';
import CtaAlternative from '../CtaAlternative.vue';

const props = defineProps({
  catched: Object,
  user: String,
});

const map = ref(null);
const isLightboxOpen = ref(false);
const currentImageIndex = ref(0);

let gmap = null;

const initMap = () => {
  if (!map.value) return;

  const latitude = parseFloat(props.catched.latitude);
  const longitude = parseFloat(props.catched.longitude);

  gmap = new google.maps.Map(map.value, {
    center: { lat: latitude, lng: longitude },
    zoom: 16,
    mapId: '6da85ff10ebc18655d496f80', // Optional, für Advanced Markers Themes
  });

  // Nutze AdvancedMarkerElement
  const { AdvancedMarkerElement } = google.maps.marker;

  new AdvancedMarkerElement({
    position: { lat: latitude, lng: longitude },
    map: gmap,
    title: props.catched.name ?? 'Markierter Punkt',
  });
};

onMounted(() => {
  if (!window.google) {
    const script = document.createElement('script');
    script.src = 'https://maps.googleapis.com/maps/api/js?key=AIzaSyDz9ywPxkkW1oOy70Rab2oqnhF02DLe5MA&libraries=marker&loading=async';
    script.async = true;
    script.defer = true;
    script.onload = () => initMap();
    document.head.appendChild(script);
  } else {
    initMap();
  }
});

watch(() => props.catched, () => {
  if (window.google) {
    initMap();
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
}

const openLightbox = (index) => {
  currentImageIndex.value = index;
  isLightboxOpen.value = true;
};

const closeLightbox = () => {
  isLightboxOpen.value = false;
};
</script>
