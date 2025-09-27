<script setup>
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
import VFileInput from '@/components/VFileInput.vue';
import VButton from '@/components/VButton.vue';
import VInput from '@/components/VInput.vue';
import VTextarea from '@/components/VTextarea.vue';
import VDateTimePicker from '@/components/VDateTimePicker.vue';
import { useForm, router, usePage } from '@inertiajs/vue3';
import { watch, computed, ref } from 'vue';
import ImagePreview from './ImagePreview.vue';
import GoogleMapPicker from '@/components/GoogleMapPicker.vue';
import FullLoadingScreen from '@/components/FullLoadingScreen.vue';
import { fishSpecies, waters } from './config.js';
import Multiselect from 'vue-multiselect'

const props = defineProps({
  catched: Object,
  errors: Object,
});

const page = usePage()

// User aus den Inertia-Props
const user = computed(() => page.props.auth.user)

const form = useForm({
  name: null,
  length: null,
  weight: null,
  depth: null,
  temperature: null,
  waters: null,
  date: new Date(),
  latitude: null,
  longitude: null,
  address: null,
  remark: null,
  air_pressure: null,
  bait: null,
  photos: [],
  media: [],
});

const showCustomWatersField = ref(false);
const showCustomFishField = ref(false);

watch(
  () => props.catched,
  (catched) => {
    if (catched) {
      form.name = catched.name || null;
      form.length = catched.length || null;
      form.weight = catched.weight || null;
      form.depth = catched.depth || null;
      form.temperature = catched.temperature || null;
      form.waters = catched.waters || null;
      form.date = new Date(catched.date) || new Date();
      form.latitude = catched.latitude || null;
      form.longitude = catched.longitude || null;
      form.address = catched.address || null;
      form.air_pressure = catched.air_pressure || null;
      form.bait = catched.bait || null;
      form.remark = catched.remark || null;
      form.media = catched.media || []
    }
  },
  { immediate: true }
);

watch(
  () => form.photos,
  (newPhotos) => {
    const maxUploads = 3;
    const alreadyUploaded = form.media?.length || 0;
    const allowedUploads = maxUploads - alreadyUploaded;

    if (newPhotos.length > allowedUploads) {
      // Beschränke auf nur erlaubte Anzahl
      form.photos = newPhotos.slice(0, allowedUploads);
    }
  }
);

const canUploadMore = computed(() => {
  return (form.media?.length ?? 0) < 3;
});

const allImages = computed(() => {
  return [...form.media, ...form.photos];
});

const loading = ref(false);

const submit = () => {
  loading.value = true;

  form.post(route('catched.update', props.catched.id), {
    onFinish: () => {
      loading.value = false
    }
  })
}

const deleteCatched = () => {
  if (confirm('Fang wirklich löschen?')) {
    form.delete(route('catched.destroy', props.catched.id));
  }
};

const updateLocation = ({ lat, lng, address }) => {
  form.latitude = lat
  form.longitude = lng
  form.address = address
}

const removeImage = (item) => {
  if (item instanceof File) {
    form.photos = form.photos.filter(photo => photo !== item);
  } else {
    if (confirm('Bild wirklich löschen?')) {
      router.delete(route('catched.photo.delete', item.id), {
        onSuccess: () => {
          form.media = form.media.filter(media => media.id !== item.id);
        }
      });
    }
  }
};
</script>

<template>
  <PageWrapper title="Fang bearbeiten" :backTo="`/catched/${catched.id}`">

    <div class="bg-white dark:bg-gray-800 shadow-xl sm:rounded-lg p-5">

      <FullLoadingScreen v-if="loading" />

      <form @submit.prevent="submit" class="space-y-5">

        <VFileInput v-if="canUploadMore" type="file" v-model="form.photos" :multiple="true" :max="3" accept="image/*"
          class="block w-full focus:ring-brand-primary focus:border-brand-primary" />

        <div v-if="errors?.['photos.0']" class="text-xs mt-1 text-red-500">{{ errors['photos.0'] }}</div>
        <div v-if="errors?.['photos.1']" class="text-xs mt-1 text-red-500">{{ errors['photos.1'] }}</div>
        <div v-if="errors?.['photos.2']" class="text-xs mt-1 text-red-500">{{ errors['photos.2'] }}</div>


        <ImagePreview :modelValue="allImages" @remove="removeImage" />


        <VDateTimePicker v-model="form.date" label="Datum" mandatory />



        <label class="block text-md md:text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">
            Fish Art<span class="text-red-500"> *</span>
        </label>
        <multiselect v-model="form.name" :options="fishSpecies" placeholder=""></multiselect>
        <div v-if="errors?.name" class="text-xs mt-1 text-red-500">{{ errors?.name }}</div>

        <span class="block text-sm font-medium mb-5" v-if="!showCustomFishField"
            @click="showCustomFishField = true">
            Dein Fisch ist nicht dabei? Klick hier
        </span>

        <VInput v-if="showCustomFishField" label="Gib deine Fisch Art ein" v-model="form.name"
            :error="errors?.name" />

          
            
        <label class="block text-md md:text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">
            Gewässer<span class="text-red-500"> *</span>
        </label>
        <multiselect v-model="form.waters" :options="waters" placeholder=""></multiselect>
        <div v-if="errors?.waters" class="text-xs mt-1 text-red-500">{{ errors?.waters }}</div>

        <span class="block text-sm font-medium mb-5" v-if="!showCustomWatersField"
            @click="showCustomWatersField = true">
            Dein Gewässer ist nicht dabei? Klick hier
        </span>

        <VInput v-if="showCustomWatersField" label="Gib dein Gewässer ein" v-model="form.waters"
            :error="errors?.waters" />



        <VInput label="Länge (Centimeter)" type="number" mandatory v-model="form.length" :error="errors?.length" />
        <VInput label="Gewicht (Gramm)" type="number" v-model="form.weight" :error="errors?.weight" />
        <VInput label="Tiefe (Centimeter)" type="number" v-model="form.depth" :error="errors?.depth" />
        <VInput label="Temperatur (°C)" type="number" v-model="form.temperature" :error="errors?.temperature" />
        <VInput label="Luftdruck (hPa)" type="number" v-model="form.air_pressure" :error="errors?.air_pressure" />
        <VInput label="Köder" type="text" v-model="form.bait" :error="errors?.bait" />

        <VTextarea v-model="form.remark" label="Bemerkungen"></VTextarea>



        <GoogleMapPicker label="Tippe auf die Position in der Karte um die Position zu aktualisieren"
          :initialLat="form.latitude" :initialLng="form.longitude" @locationSelected="updateLocation" />

        <VInput label="Adresse" v-model="form.address" :error="errors?.address" />
        <VInput label="Latitude" v-model="form.latitude" :error="errors?.latitude" />
        <VInput label="Longitude" v-model="form.longitude" :error="errors?.longitude" />


        <div class="flex justify-end">
          <VButton type="submit">Aktualisieren</VButton>
          <VButton v-if="!user.subscribed" href="/billing?message=Um+etwas+zu+löschen+upgrade+bitte+zu+einem+Jahresabo"
            variant="danger" class="ml-3">Löschen</VButton>
          <VButton v-else :disabled="!user.subscribed" type="button" @click="deleteCatched" variant="danger"
            class="ml-3">
            Löschen
          </VButton>
        </div>
      </form>


    </div>
  </PageWrapper>
</template>
