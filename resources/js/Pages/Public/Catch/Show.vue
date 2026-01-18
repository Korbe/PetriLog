<template>
  <Layout>

    <Head :title="fishName" />
    <PageIllustration />

    <div class="max-w-6xl mt-20 md:mt-10 mx-auto ">

      <!-- Mobile Bildergalerie -->
      <div class="md:hidden grid grid-cols-2 gap-2 mx-2">
        <div class="col-span-2">
          <img v-if="props.catched.media?.[0]" @click="openLightbox(0)" :src="props.catched.media[0].original_url"
            class="w-full h-64 object-cover rounded-lg cursor-pointer" alt="user-image-1" />
        </div>

        <div v-for="(media, index) in props.catched.media?.slice(1) ?? []" :key="media.id" class="col-span-1">
          <img @click="openLightbox(index + 1)" :src="media.original_url"
            class="w-full h-32 object-cover rounded-lg cursor-pointer" :alt="'user-image-' + (index + 2)" />
        </div>
      </div>

      <!-- Fang Info -->
      <div class="mx-2 mt-5">
        <div class="p-3 border-1 rounded-lg w-full">
          <h1 class="text-5xl text-primary-500">{{ fishName }}</h1>
          <div class="flex justify-between">
            <p class="text-xl">
              {{ formatDate(catched.date) }} -
              {{ catched.lake?.name ?? catched.river?.name ?? 'Unbekanntes Gewässer' }}
            </p>

            <VButton v-if="props.catched != null && $page.props?.auth?.user?.id === props.catched.user_id"
              :href="`/catched/${catched.id}/edit`">
              Bearbeiten
            </VButton>
          </div>

          <p>Gefangen von <span class="text-xl"><b>{{ user }}</b></span></p>
        </div>
      </div>

      <!-- Desktop Bildergalerie -->
      <div class="hidden md:block">
        <ul role="list"
          class="px-5 md:mx-auto my-5 grid w-full grid-cols-3 gap-x-4 gap-y-4 sm:grid-cols-3 lg:grid-cols-3">
          <li v-for="(media, index) in props.catched.media ?? []" :key="media.name">
            <img @click="openLightbox(index)" class="aspect-square sm:aspect-[3/2] w-full rounded-lg object-cover"
              :src="media.original_url" :alt="'user-image-' + index" />
          </li>

          <vue-easy-lightbox v-if="isLightboxOpen" :visible="isLightboxOpen"
            :imgs="props.catched.media?.map(item => item.original_url) ?? []" :index="currentImageIndex"
            @hide="closeLightbox" />
        </ul>
      </div>

      <!-- Tabelleninfos -->
      <div class="px-4 sm:px-6 lg:px-8 border-1 mt-5 m-2 rounded-lg">
        <div class="flow-root">
          <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
              <table class="relative min-w-full divide-y divide-gray-300">
                <tbody class="divide-y divide-gray-200">
                  <tr>
                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-0">Länge</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ catched.length ? catched.length +
                      "cm" : 'n/a ' }}</td>
                  </tr>
                  <tr>
                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-0">Gewicht</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ catched.weight ? catched.weight +
                      "g" : 'n/a ' }}</td>
                  </tr>
                  <tr>
                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-0">Tiefe</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ catched.depth ? catched.depth +
                      "cm" : 'n/a ' }}</td>
                  </tr>
                  <tr>
                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-0">Temperatur
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ catched.temperature ?
                      catched.temperature + "°C" : 'n/a ' }}</td>
                  </tr>
                  <tr>
                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-0">Luftdruck
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ catched.air_pressure ?
                      catched.air_pressure + "hPa" : 'n/a' }}</td>
                  </tr>
                  <tr>
                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-0">Köder</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ catched.bait ?? 'n/a' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Bemerkungen -->
      <div class="mx-2 mt-5">
        <div v-if="catched.remark" class="w-full border-1 rounded-lg p-5">
          <p>{{ catched.remark }}</p>
        </div>
      </div>

      <!-- Karte -->
      <div class="mx-2 mt-5 mb-20 border-1 p-5 rounded-lg">
        <p class="mb-5"><b>Hier gefangen</b><br> {{ catched.address }}</p>
        <GoogleMap :latitude="catched.latitude" :longitude="catched.longitude" :title="fishName" />
      </div>

      <CtaAlternative class="overflow-hidden" heading="Logge jetzt deine Fangmomente" buttonText="Registrieren"
        buttonLink="/register" />
    </div>
  </Layout>
</template>

<script setup>
import { ref, computed } from 'vue';
import VueEasyLightbox from 'vue-easy-lightbox';
import Layout from '@/Layouts/Public/Layout.vue';
import PageIllustration from '../PageIllustration.vue';
import CtaAlternative from '../CtaAlternative.vue';
import VButton from '@/components/VButton.vue';
import { Head } from '@inertiajs/vue3';
import GoogleMap from '@/components/GoogleMap.vue';

const props = defineProps({
  catched: Object,
  user: String,
});

const isLightboxOpen = ref(false);
const currentImageIndex = ref(0);

const openLightbox = (index) => {
  currentImageIndex.value = index;
  isLightboxOpen.value = true;
};

const closeLightbox = () => {
  isLightboxOpen.value = false;
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return `${String(date.getDate()).padStart(2, '0')}.${String(date.getMonth() + 1).padStart(2, '0')}.${date.getFullYear()} ${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`;
};

const fishName = computed(() => props.catched.fish?.name ?? 'Unbekannter Fisch');
const catched = props.catched;
</script>
