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
import Multiselect from 'vue-multiselect'

const props = defineProps({
  catched: Object,
  errors: Object,
  fish: Array,
  lakes: Array,
  rivers: Array,
})

const page = usePage()
const user = computed(() => page.props.auth.user)

// Form-Setup
const form = useForm({
  fish_id: props.catched?.fish_id ?? null,
  lake_id: props.catched?.lake_id ?? null,
  river_id: props.catched?.river_id ?? null,
  length: props.catched?.length ?? null,
  weight: props.catched?.weight ?? null,
  depth: props.catched?.depth ?? null,
  temperature: props.catched?.temperature ?? null,
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

// Upload-Limit
watch(() => form.photos, newPhotos => {
  const allowed = 3 - (form.media?.length ?? 0)
  if (newPhotos.length > allowed) {
    form.photos = newPhotos.slice(0, allowed)
  }
})

const canUploadMore = computed(() => (form.media?.length ?? 0) < 3)
const allImages = computed(() => [...form.media, ...form.photos])
const loading = ref(false)

// Multiselect Computed Properties
const selectedFish = computed({
  get() { return props.fish.find(f => f.id === form.fish_id) ?? null },
  set(value) { form.fish_id = value?.id ?? null },
})

const selectedLake = computed({
  get() { return props.lakes.find(l => l.id === form.lake_id) ?? null },
  set(value) {
    form.lake_id = value?.id ?? null
    if (value) form.river_id = null // Nur Lake oder River erlaubt
  },
})

const selectedRiver = computed({
  get() { return props.rivers.find(r => r.id === form.river_id) ?? null },
  set(value) {
    form.river_id = value?.id ?? null
    if (value) form.lake_id = null // Nur River oder Lake erlaubt
  },
})

// Submit & Delete
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
      onSuccess: () => { form.media = form.media.filter(m => m.id !== item.id) },
    })
  }
}

</script>

<template>
  <PageWrapper title="Fang bearbeiten" :backTo="`/catched/${catched.id}`">

    <div class="bg-white dark:bg-gray-800 shadow-xl sm:rounded-lg p-5">
      <FullLoadingScreen v-if="loading" />

      <form @submit.prevent="submit" class="space-y-5">

        <!-- File Upload -->
        <VFileInput v-if="canUploadMore" type="file" v-model="form.photos" :multiple="true" :max="3" accept="image/*"
          class="block w-full focus:ring-brand-primary focus:border-brand-primary" />
        <ImagePreview :modelValue="allImages" @remove="removeImage" />

        <VDateTimePicker v-model="form.date" label="Datum" mandatory />

        <!-- Fisch -->
        <label class="block text-md md:text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">
          Fischart <span class="text-red-500">*</span>
        </label>
        <Multiselect v-model="selectedFish" :options="fish" label="name" track-by="id" placeholder="Fisch auswählen" />
        <div v-if="errors?.fish_id" class="text-xs mt-1 text-red-500">{{ errors.fish_id }}</div>

        <!-- Gewässer -->
        <label class="block text-md md:text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">
          See auswählen
        </label>
        <Multiselect v-model="selectedLake" :options="lakes" label="name" track-by="id" placeholder="See auswählen" />
        <div v-if="errors?.lake_id" class="text-xs mt-1 text-red-500">{{ errors.lake_id }}</div>

        <label class="block text-md md:text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">
          Fluss auswählen
        </label>
        <Multiselect v-model="selectedRiver" :options="rivers" label="name" track-by="id"
          placeholder="Fluss auswählen" />
        <div v-if="errors?.river_id" class="text-xs mt-1 text-red-500">{{ errors.river_id }}</div>

        <p>Ihr Gewässer ist nicht dabei? Schreiben Sie uns eine Mail an <a class="underline"
            href="mailto:info@petrilog.com">info@petrilog.com</a></p>

        <!-- Weitere Felder -->
        <VInput label="Länge (cm)" type="number" mandatory v-model="form.length" :error="errors?.length" />
        <VInput label="Gewicht (g)" type="number" v-model="form.weight" :error="errors?.weight" />
        <VInput label="Tiefe (cm)" type="number" v-model="form.depth" :error="errors?.depth" />
        <VInput label="Temperatur (°C)" type="number" v-model="form.temperature" :error="errors?.temperature" />
        <VInput label="Luftdruck (hPa)" type="number" v-model="form.air_pressure" :error="errors?.air_pressure" />
        <VInput label="Köder" type="text" v-model="form.bait" :error="errors?.bait" />
        <VTextarea v-model="form.remark" label="Bemerkungen" />

        <GoogleMapPicker label="Position auswählen" :initialLat="form.latitude" :initialLng="form.longitude"
          @locationSelected="updateLocation" />
        <VInput label="Adresse" v-model="form.address" :error="errors?.address" />
        <VInput label="Latitude" v-model="form.latitude" :error="errors?.latitude" />
        <VInput label="Longitude" v-model="form.longitude" :error="errors?.longitude" />

        <div class="flex justify-end">
          <VButton type="submit">Aktualisieren</VButton>
          <VButton v-if="!user.subscribed" href="/billing" variant="danger" class="ml-3">Löschen</VButton>
          <VButton v-else :disabled="!user.subscribed" type="button" @click="deleteCatched" variant="danger"
            class="ml-3">Löschen</VButton>
        </div>

      </form>
    </div>
  </PageWrapper>
</template>
