<script setup>
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
import VFileInput from '@/components/VFileInput.vue';
import VButton from '@/components/VButton.vue';
import VInput from '@/components/VInput.vue';
import VSelect from '@/components/VSelect.vue';
import VTextarea from '@/components/VTextarea.vue';
import VDateTimePicker from '@/components/VDateTimePicker.vue';
import { useForm, router, usePage } from '@inertiajs/vue3';
import { watch, computed, ref, onMounted } from 'vue';
import ImagePreview from './ImagePreview.vue';
import GoogleMapPicker from '@/components/GoogleMapPicker.vue';
import TrialEndedBanner from '@/components/TrialEndedBanner.vue';


const fishSpeciesAustria = [
  { label: 'Aal', value: 'Aal' },
  { label: 'Aalrutte', value: 'Aalrutte' },
  { label: 'Adriastör', value: 'Adriastör' },
  { label: 'Aitel', value: 'Aitel' },
  { label: 'Amur', value: 'Amur' },
  { label: 'Atlantischer Stör', value: 'Atlantischer Stör' },
  { label: 'Äsche', value: 'Äsche' },
  { label: 'Bachforelle', value: 'Bachforelle' },
  { label: 'Bachneunauge', value: 'Bachneunauge' },
  { label: 'Bachsaibling', value: 'Bachsaibling' },
  { label: 'Bachschmerle', value: 'Bachschmerle' },
  { label: 'Barbe', value: 'Barbe' },
  { label: 'Bitterling', value: 'Bitterling' },
  { label: 'Blaubandbärbling', value: 'Blaubandbärbling' },
  { label: 'Brachse', value: 'Brachse' },
  { label: 'Donaukaulbarsch', value: 'Donaukaulbarsch' },
  { label: 'Dreistachliger Stichling', value: 'Dreistachliger Stichling' },
  { label: 'Elritze', value: 'Elritze' },
  { label: 'Flussbarsch', value: 'Flussbarsch' },
  { label: 'Frauennerfling', value: 'Frauennerfling' },
  { label: 'Giebel', value: 'Giebel' },
  { label: 'Glattdick', value: 'Glattdick' },
  { label: 'Goldsteinbeißer', value: 'Goldsteinbeißer' },
  { label: 'Gründling', value: 'Gründling' },
  { label: 'Güster', value: 'Güster' },
  { label: 'Hasel', value: 'Hasel' },
  { label: 'Hausen', value: 'Hausen' },
  { label: 'Hecht', value: 'Hecht' },
  { label: 'Huchen', value: 'Huchen' },
  { label: 'Karausche', value: 'Karausche' },
  { label: 'Karpfen', value: 'Karpfen' },
  { label: 'Kaulbarsch', value: 'Kaulbarsch' },
  { label: 'Kesslergründling', value: 'Kesslergründling' },
  { label: 'Koppe', value: 'Koppe' },
  { label: 'Laube', value: 'Laube' },
  { label: 'Malermuschel', value: 'Malermuschel' },
  { label: 'Moderlieschen', value: 'Moderlieschen' },
  { label: 'Nackthalsgrundel', value: 'Nackthalsgrundel' },
  { label: 'Nase', value: 'Nase' },
  { label: 'Nerfling', value: 'Nerfling' },
  { label: 'Perlfisch', value: 'Perlfisch' },
  { label: 'Rapfen', value: 'Rapfen' },
  { label: 'Regenbogenforelle', value: 'Regenbogenforelle' },
  { label: 'Reinanke', value: 'Reinanke' },
  { label: 'Rotauge', value: 'Rotauge' },
  { label: 'Rotfeder', value: 'Rotfeder' },
  { label: 'Rußnase', value: 'Rußnase' },
  { label: 'Schleie', value: 'Schleie' },
  { label: 'Schlammpeitzger', value: 'Schlammpeitzger' },
  { label: 'Schneider', value: 'Schneider' },
  { label: 'Schrätzer', value: 'Schrätzer' },
  { label: 'Schwarzmundgrundel', value: 'Schwarzmundgrundel' },
  { label: 'Seeforelle', value: 'Seeforelle' },
  { label: 'Seelaube', value: 'Seelaube' },
  { label: 'Seesaibling', value: 'Seesaibling' },
  { label: 'Semling', value: 'Semling' },
  { label: 'Sichling', value: 'Sichling' },
  { label: 'Sonnenbarsch', value: 'Sonnenbarsch' },
  { label: 'Steingreßling', value: 'Steingreßling' },
  { label: 'Steinbeißer', value: 'Steinbeißer' },
  { label: 'Sterlet', value: 'Sterlet' },
  { label: 'Sternhausen', value: 'Sternhausen' },
  { label: 'Streber', value: 'Streber' },
  { label: 'Tolstolob', value: 'Tolstolob' },
  { label: 'Ukrainisches Bachneunauge', value: 'Ukrainisches Bachneunauge' },
  { label: 'Waxdick', value: 'Waxdick' },
  { label: 'Weißflossengründling', value: 'Weißflossengründling' },
  { label: 'Weißer Stör', value: 'Weißer Stör' },
  { label: 'Wels', value: 'Wels' },
  { label: 'Wolgazander', value: 'Wolgazander' },
  { label: 'Zander', value: 'Zander' },
  { label: 'Zingel', value: 'Zingel' },
  { label: 'Zobel', value: 'Zobel' },
  { label: 'Zope', value: 'Zope' },
  { label: 'Zwergwels', value: 'Zwergwels' }
];

const watersAustria = [
  { label: 'Achensee', value: 'Achensee' },
  { label: 'Ach', value: 'Ach' },
  { label: 'Ager', value: 'Ager' },
  { label: 'Aist', value: 'Aist' },
  { label: 'Alfenz', value: 'Alfenz' },
  { label: 'Alm', value: 'Alm' },
  { label: 'Altausseer See', value: 'Altausseer See' },
  { label: 'Alter Rhein', value: 'Alter Rhein' },
  { label: 'Antiesen', value: 'Antiesen' },
  { label: 'Arbesbach', value: 'Arbesbach' },
  { label: 'Aschach', value: 'Aschach' },
  { label: 'Aurach', value: 'Aurach' },
  { label: 'Attersee', value: 'Attersee' },
  { label: 'Bodensee', value: 'Bodensee' },
  { label: 'Donau', value: 'Donau' },
  { label: 'Drau', value: 'Drau' },
  { label: 'Erlaufsee', value: 'Erlaufsee' },
  { label: 'Enns', value: 'Enns' },
  { label: 'Faaker See', value: 'Faaker See' },
  { label: 'Fuschlsee', value: 'Fuschlsee' },
  { label: 'Gail', value: 'Gail' },
  { label: 'Grabensee', value: 'Grabensee' },
  { label: 'Grundlsee', value: 'Grundlsee' },
  { label: 'Gurk', value: 'Gurk' },
  { label: 'Haldensee', value: 'Haldensee' },
  { label: 'Hallstätter See', value: 'Hallstätter See' },
  { label: 'Heiterwanger See', value: 'Heiterwanger See' },
  { label: 'Hintersee', value: 'Hintersee' },
  { label: 'Hintersteiner See', value: 'Hintersteiner See' },
  { label: 'Illmitzer Zicksee', value: 'Illmitzer Zicksee' },
  { label: 'Inn', value: 'Inn' },
  { label: 'Irrsee', value: 'Irrsee' },
  { label: 'Kamp', value: 'Kamp' },
  { label: 'Keutschacher See', value: 'Keutschacher See' },
  { label: 'Klopeiner See', value: 'Klopeiner See' },
  { label: 'Lange Lacke', value: 'Lange Lacke' },
  { label: 'Längsee', value: 'Längsee' },
  { label: 'Leitha', value: 'Leitha' },
  { label: 'Lunzer See', value: 'Lunzer See' },
  { label: 'March', value: 'March' },
  { label: 'Mattsee', value: 'Mattsee' },
  { label: 'Millstätter See', value: 'Millstätter See' },
  { label: 'Mondsee', value: 'Mondsee' },
  { label: 'Mur', value: 'Mur' },
  { label: 'Neufelder See', value: 'Neufelder See' },
  { label: 'Neusiedler See', value: 'Neusiedler See' },
  { label: 'Obertrumer See', value: 'Obertrumer See' },
  { label: 'Offensee', value: 'Offensee' },
  { label: 'Ossiacher See', value: 'Ossiacher See' },
  { label: 'Plansee', value: 'Plansee' },
  { label: 'Pressegger See', value: 'Pressegger See' },
  { label: 'Raab', value: 'Raab' },
  { label: 'Salzach', value: 'Salzach' },
  { label: 'Thaya', value: 'Thaya' },
  { label: 'Traun', value: 'Traun' },
  { label: 'Traunsee', value: 'Traunsee' },
  { label: 'Vilsalpsee', value: 'Vilsalpsee' },
  { label: 'Vorderer Gosausee', value: 'Vorderer Gosausee' },
  { label: 'Walchsee', value: 'Walchsee' },
  { label: 'Wallersee', value: 'Wallersee' },
  { label: 'Weissensee', value: 'Weissensee' },
  { label: 'Wolfgangsee', value: 'Wolfgangsee' },
  { label: 'Wörthersee', value: 'Wörthersee' },
  { label: 'Ybbs', value: 'Ybbs' },
  { label: 'Zeller See', value: 'Zeller See' },
  { label: 'Zicksee', value: 'Zicksee' }
];

const props = defineProps({
  catched: Object,
  errors: Object,
});

const page = usePage()

// User aus den Inertia-Props
const user = computed(() => page.props.auth.user)

// Trial-Status
const isOnTrial = computed(() => user.value?.onTrial)

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

    <TrialEndedBanner v-if="!user.subscribed && !isOnTrial" />

    <div v-else class="bg-white dark:bg-gray-800 shadow-xl sm:rounded-lg p-5">

      <div v-if="loading" class="fixed inset-0 flex flex-col items-center justify-center bg-gray-100 z-50">
        <div class="relative w-32 h-32 mb-6">
          <div class="absolute inset-0 border-8 border-primary-500 border-t-transparent rounded-full animate-spin">
          </div>
          <div class="absolute inset-4 border-4 border-blue-300 border-t-transparent rounded-full animate-spin-slow">
          </div>
        </div>
        <p class="text-3xl font-bold text-gray-700 animate-pulse">Bitte warten...</p>
      </div>

      <form @submit.prevent="submit" class="space-y-5">

        <VFileInput v-if="canUploadMore" type="file" v-model="form.photos" :multiple="true" :max="3" accept="image/*"
          class="block w-full focus:ring-brand-primary focus:border-brand-primary" />

        <div v-if="errors?.['photos.0']" class="text-xs mt-1 text-red-500">{{ errors['photos.0'] }}</div>
        <div v-if="errors?.['photos.1']" class="text-xs mt-1 text-red-500">{{ errors['photos.1'] }}</div>
        <div v-if="errors?.['photos.2']" class="text-xs mt-1 text-red-500">{{ errors['photos.2'] }}</div>


        <ImagePreview :modelValue="allImages" @remove="removeImage" />


        <VDateTimePicker v-model="form.date" label="Datum" mandatory />

        <VSelect label="Fisch Art" id="fishname" :options="fishSpeciesAustria" placeholder="Bitte wählen..." mandatory
          v-model="form.name" :error="errors?.name" :reduce="option => option.value" />

        <VSelect label="Gewässer" id="watername" placeholder="Bitte wählen..." v-model="form.waters"
          :reduce="option => option.value" :options="watersAustria" :error="errors?.waters" mandatory />

        <span class="block text-sm font-medium mb-10" v-if="!showCustomWatersField"
          @click="showCustomWatersField = true">Dein Gewässer ist nicht dabei? Klick hier</span>

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
          <VButton type="button" @click="deleteCatched" variant="danger" class="ml-3">
            Löschen
          </VButton>
        </div>
      </form>


    </div>
  </PageWrapper>
</template>
