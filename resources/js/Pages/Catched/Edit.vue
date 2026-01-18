<script setup>
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue'
import VFileInput from '@/components/VFileInput.vue'
import VButton from '@/components/VButton.vue'
import VInput from '@/components/VInput.vue'
import VTextarea from '@/components/VTextarea.vue'
import VDateTimePicker from '@/components/VDateTimePicker.vue'
import { useForm, router, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import ImagePreview from '@/components/ImagePreview.vue'
import GoogleMapPicker from '@/components/GoogleMapPicker.vue'
import FullLoadingScreen from '@/components/FullLoadingScreen.vue'
import { waters } from './config.js'
import Multiselect from 'vue-multiselect'

const props = defineProps({
  catched: Object,
  errors: Object,
  fish: Array,
})

const page = usePage()
const user = computed(() => page.props.auth.user)

/**
 * Fish-Objekt für Multiselect
 */
const selectedFish = computed({
  get() {
    return props.fish.find(f => f.id === form.fish_id) ?? null
  },
  set(value) {
    form.fish_id = value?.id ?? null
  },
})

const form = useForm({
  fish_id: props.catched?.fish_id ?? null,
  length: props.catched?.length ?? null,
  weight: props.catched?.weight ?? null,
  depth: props.catched?.depth ?? null,
  temperature: props.catched?.temperature ?? null,
  waters: props.catched?.waters ?? null,
  date: props.catched?.date ? new Date(props.catched.date) : new Date(),
  latitude: props.catched?.latitude ?? null,
  longitude: props.catched?.longitude ?? null,
  address: props.catched?.address ?? null,
  remark: props.catched?.remark ?? null,
  air_pressure: props.catched?.air_pressure ?? null,
  bait: props.catched?.bait ?? null,
  photos: [],
  media: props.catched?.media ?? [],
})

/**
 * Upload-Limit
 */
watch(() => form.photos, newPhotos => {
  const allowed = 3 - (form.media?.length ?? 0)
  if (newPhotos.length > allowed) {
    form.photos = newPhotos.slice(0, allowed)
  }
})

const canUploadMore = computed(() => (form.media?.length ?? 0) < 3)
const allImages = computed(() => [...form.media, ...form.photos])
const loading = ref(false)

const submit = () => {
  loading.value = true
  form.post(route('catched.update', props.catched.id), {
    onFinish: () => (loading.value = false),
  })
}

const deleteCatched = () => {
  if (confirm('Fang wirklich löschen?')) {
    form.delete(route('catched.destroy', props.catched.id))
  }
}

const updateLocation = ({ lat, lng, address }) => {
  form.latitude = lat
  form.longitude = lng
  form.address = address
}

const removeImage = item => {
  if (item.file instanceof File) {
    form.photos = form.photos.filter(file => file !== item.file)
    return
  }

  if (item.readonly && confirm('Bild wirklich löschen?')) {
    router.delete(route('catched.photo.delete', item.id), {
      onSuccess: () => {
        form.media = form.media.filter(m => m.id !== item.id)
      },
    })
  }
}
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
          Fischart<span class="text-red-500"> *</span>
        </label>

        <Multiselect v-model="selectedFish" :options="fish" label="name" track-by="id" placeholder="Fisch auswählen"
          :close-on-select="true" />

        <div v-if="errors?.fish_id" class="text-xs mt-1 text-red-500">
          {{ errors.fish_id }}
        </div>

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
