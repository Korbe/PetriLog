<template>
  <PageWrapper title="Fang">
    <template v-slot:actions>
      <VButton :href="route('catched.edit', catched.id)">Bearbeiten</VButton>
    </template>

    <div class="flex flex-wrap gap-5 my-5">
      <!-- Erste Box: volle Breite auf Mobile -->
      <div class="w-full md:w-[32%] bg-white dark:bg-gray-800 shadow-xs overflow-hidden rounded-lg p-5">
        <h1 class="mr-4 text-4xl">{{ catched.name }}</h1>
        <p class="text-xl">{{ formatDate(catched.date) }} - {{ catched.waters }}</p>
      </div>

      <div class="flex w-full md:w-[50%] xl:w-[30%]  space-x-5">
        <div class="w-[50%] bg-white dark:bg-gray-800 shadow-xs rounded-lg p-5 space-y-3">
          <p><b>Länge:</b> {{ catched.length }}cm</p>
          <p><b>Gewicht:</b> {{ catched.weight }}g</p>
        </div>

        <div class="w-[50%] bg-white dark:bg-gray-800 shadow-xs rounded-lg p-5 space-y-3">
          <p><b>Tiefe:</b> {{ catched.depth }}m</p>
          <p><b>Temperatur:</b> {{ catched.temperature }}°C</p>
        </div>
      </div>
    </div>


    <div v-if="catched.remark" class="w-full bg-white dark:bg-gray-800 shadow-xs overflow-hidden rounded-lg p-5">
      <p>{{ catched.remark }}</p>
    </div>


    <ul role="list" class="mx-auto my-5 grid w-full grid-cols-3 gap-x-4 gap-y-4 sm:grid-cols-3 lg:grid-cols-3">
      <li v-for="(media, index) in props.catched.media" :key="media.name">
        <img @click="openLightbox(index)" class="aspect-square sm:aspect-[3/2] w-full rounded-lg object-cover"
          :src="media.original_url" alt="" />
      </li>
    </ul>

    <div>
      <div class="rounded-2xl my-5" ref="map" style="width: 100%; height: 400px;"></div>
      <p><b>Adressse:</b> {{ catched.address }}</p>
    </div>

    <vue-easy-lightbox v-if="isLightboxOpen" :visible="isLightboxOpen"
      :imgs="props.catched.media.map(item => item.original_url)" :index="currentImageIndex" @hide="closeLightbox" />
  </PageWrapper>
</template>
<script setup>
import { ref, onMounted, watch } from 'vue';
import VButton from '@/components/VButton.vue';
import VueEasyLightbox from 'vue-easy-lightbox';
import PageWrapper from '@/Layouts/PageWrapper.vue';

const props = defineProps({
  catched: Object,
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
  return `${day}.${month}.${year}`;
};

const openLightbox = (index) => {
  currentImageIndex.value = index;
  isLightboxOpen.value = true;
};

const closeLightbox = () => {
  isLightboxOpen.value = false;
};
</script>
