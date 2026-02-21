<template>
  <PageWrapper title="Fang bearbeiten" :backTo="`/catched/${catched.id}`">
    <div class="bg-white dark:bg-gray-800 shadow-xl sm:rounded-lg p-5">
      <FullLoadingScreen v-if="loading" />

      <form @submit.prevent="submit" class="space-y-5">

        <!-- Vorschau + Sortierung -->
        <ImageAndUploadPreview v-model="images" @remove="removeImage" />

        <VDateTimePicker v-model="form.date" label="Datum" mandatory />

        <!-- Fisch -->
        <VMultiselect v-model="selectedFish" :options="fish" label="name" track-by="id" placeholder="Fisch auswählen" />

        <!-- Gewässer -->
        <VMultiselect v-model="selectedLake" :options="lakes" label="name" track-by="id" placeholder="See auswählen" />

        <VMultiselect v-model="selectedRiver" :options="rivers" label="name" track-by="id"
          placeholder="Fluss auswählen" />

        <!-- Felder -->
        <VInput label="Länge (cm)" type="number" mandatory v-model="form.length" />
        <VInput label="Gewicht (g)" type="number" v-model="form.weight" />
        <VInput label="Tiefe (cm)" type="number" v-model="form.depth" />
        <VInput label="Temperatur (°C)" type="number" v-model="form.temperature" />
        <VInput label="Luftdruck (hPa)" type="number" v-model="form.air_pressure" />
        <VInput label="Köder" v-model="form.bait" />
        <VEditor label="Bemerkungen" v-model="form.remark" />

        <GoogleMapPicker label="Position auswählen" :initialLat="form.latitude" :initialLng="form.longitude"
          @locationSelected="updateLocation" />

        <VInput label="Adresse" v-model="form.address" />
        <VInput label="Latitude" v-model="form.latitude" />
        <VInput label="Longitude" v-model="form.longitude" />

        <div class="flex justify-end gap-3">
          <VButton type="submit">Aktualisieren</VButton>
          <VButton type="button" variant="danger" @click="deleteCatched">
            Löschen
          </VButton>
        </div>

      </form>
    </div>
  </PageWrapper>
</template>
<script setup>
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue'
import VButton from '@/components/VButton.vue'
import VInput from '@/components/VInput.vue'
import VDateTimePicker from '@/components/VDateTimePicker.vue'
import { useForm, router, usePage } from '@inertiajs/vue3'
import { computed, onMounted, ref } from 'vue'
import GoogleMapPicker from '@/components/GoogleMapPicker.vue'
import FullLoadingScreen from '@/components/FullLoadingScreen.vue'
import VEditor from '@/components/VEditor.vue'
import VMultiselect from '@/components/VMultiselect.vue'
import ImageAndUploadPreview from '@/components/ImageAndUploadPreview.vue'

/* ------------------------------------------------------------------ */
/* Props */
/* ------------------------------------------------------------------ */
const props = defineProps({
  catched: Object,
  errors: Object,
  fish: Array,
  lakes: Array,
  rivers: Array,
})

const page = usePage()
/* ------------------------------------------------------------------ */
/* Form */
/* ------------------------------------------------------------------ */
const form = useForm({
  fish_id: props.catched?.fish_id ?? null,
  lake_id: props.catched?.lake_id ?? null,
  river_id: props.catched?.river_id ?? null,
  length: props.catched?.length ?? null,
  weight: props.catched?.weight ?? null,
  depth: props.catched?.depth ?? null,
  temperature: props.catched?.temperature ?? null,
  air_pressure: props.catched?.air_pressure ?? null,
  bait: props.catched?.bait ?? null,
  remark: props.catched?.remark ?? null,
  date: props.catched?.date ? new Date(props.catched.date) : new Date(),
  latitude: props.catched?.latitude ?? null,
  longitude: props.catched?.longitude ?? null,
  address: props.catched?.address ?? null,
})

/* ------------------------------------------------------------------ */
/* Bilder – SINGLE SOURCE OF TRUTH */
/* ------------------------------------------------------------------ */
const images = ref([
  ...(props.catched?.media ?? []),
])

const existingMedia = computed(() =>
  (props.catched?.media ?? [])
    .slice()
    .sort((a, b) => a.order_column - b.order_column)
    .map(m => ({
      id: m.id,
      file_name: m.file_name,
      size: m.size,
      url: m.original_url,
      key: `media-${m.id}`,
    }))
)

const newUploads = ref([]) // nur neue Files

/* ------------------------------------------------------------------ */
/* Multiselects */
/* ------------------------------------------------------------------ */
const selectedFish = computed({
  get: () => props.fish.find(f => f.id === form.fish_id) ?? null,
  set: v => (form.fish_id = v?.id ?? null),
})

const selectedLake = computed({
  get: () => props.lakes.find(l => l.id === form.lake_id) ?? null,
  set: v => {
    form.lake_id = v?.id ?? null
    if (v) form.river_id = null
  },
})

const selectedRiver = computed({
  get: () => props.rivers.find(r => r.id === form.river_id) ?? null,
  set: v => {
    form.river_id = v?.id ?? null
    if (v) form.lake_id = null
  },
})

/* ------------------------------------------------------------------ */
/* Image entfernen */
/* ------------------------------------------------------------------ */
const removeImage = (item) => {
  images.value = images.value.filter(i => i !== item)
  if (item.id) {
    router.delete(route('app.catched.photo.delete', item.id))
  }
}

/* ------------------------------------------------------------------ */
/* Submit */
/* ------------------------------------------------------------------ */
const loading = ref(false)

const submit = () => {
  loading.value = true

  form.transform(data => ({
    ...data,
    photos: images.value.filter(i => i.type === 'new').map(f => f.file),
    photo_order: images.value.map(i => i.key) // jetzt korrekt Reihenfolge
  })).post(route('app.catched.update', props.catched.id), {
    onFinish: () => loading.value = false
  })
}

/* ------------------------------------------------------------------ */
/* Delete */
/* ------------------------------------------------------------------ */
const deleteCatched = () => {
  if (!confirm('Fang wirklich löschen?')) return
  form.delete(route('app.catched.destroy', props.catched.id))
}

/* ------------------------------------------------------------------ */
/* Location */
/* ------------------------------------------------------------------ */
const updateLocation = ({ lat, lng, address }) => {
  form.latitude = lat
  form.longitude = lng
  form.address = address
}

onMounted(() => {
  // 1️⃣ Bestehende Medien sauber sortieren nach order_column
  const sortedExisting = (props.catched.media ?? [])
    .slice() // Kopie, Prop nicht mutieren
    .sort((a, b) => a.order_column - b.order_column)
    .map(m => ({
      ...m,
      type: 'existing',
      key: `media-${m.id}`,
      url: m.original_url,
      name: m.name,
      size: m.size,
    }));

  // 2️⃣ Neue Uploads hinzufügen (noch leer am Anfang)
  const sortedNewUploads = newUploads.value.map(f => ({
    file: f,
    type: 'new',
    name: f.name,
    size: f.size,
    url: URL.createObjectURL(f),
    key: f._uuid || (f._uuid = crypto.randomUUID()),
  }));

  // 3️⃣ Single source of truth
  images.value = [...sortedExisting, ...sortedNewUploads];
});
</script>