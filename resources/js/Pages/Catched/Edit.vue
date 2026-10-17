<template>
  <PageWrapper title="Fang bearbeiten" :eyebrow="props.catched?.fish?.name ?? 'Fang'" :backTo="`/catched/${catched.id}`">
    <template v-slot:actions>
      <VButton type="button" variant="ghost" class="text-(--app-danger)!" @click="showDeleteDialog = true">
        <TrashIcon class="size-4" /> Löschen
      </VButton>
      <VButton type="submit" form="catched-edit-form" :disabled="form.processing">
        <CheckIcon class="size-4" /> Speichern
      </VButton>
    </template>

    <FullLoadingScreen v-if="loading" />

    <form id="catched-edit-form" @submit.prevent="submit" class="grid gap-5 md:gap-6 lg:grid-cols-12">

      <div class="space-y-5 md:space-y-6 lg:col-span-7">
        <!-- Fotos -->
        <section class="ui-card p-5 md:p-6">
          <p class="ui-kicker">Fotos</p>
          <p class="mb-4 mt-1 text-sm text-(--app-muted)">Bis zu 3 Fotos – das erste wird zum Titelbild. Reihenfolge per Ziehen ändern.</p>
          <ImageAndUploadPreview v-model="images" />
        </section>

        <!-- Fang -->
        <section class="ui-card space-y-5 p-5 md:p-6">
          <p class="ui-kicker">Fang</p>
          <VDateTimePicker v-model="form.date" label="Datum" mandatory />

          <CatchSpeciesWaterFields v-model:fish-id="form.fish_id" v-model:lake-id="form.lake_id" v-model:river-id="form.river_id"
            :fish="fish" :lakes="lakes" :rivers="rivers" :states="states" :errors="form.errors" />
        </section>

        <!-- Bemerkungen -->
        <section class="ui-card p-5 md:p-6">
          <VEditor label="Bemerkungen" v-model="form.remark" :error="form.errors.remark" />
        </section>
      </div>

      <div class="space-y-5 md:space-y-6 lg:col-span-5">
        <!-- Messdaten -->
        <section class="ui-card p-5 md:p-6">
          <p class="ui-kicker mb-4">Messdaten</p>
          <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
            <VInput label="Länge (cm)" type="number" mandatory v-model="form.length" :error="form.errors.length" />
            <VInput label="Gewicht (g)" type="number" v-model="form.weight" :error="form.errors.weight" />
            <VInput label="Tiefe (cm)" type="number" v-model="form.depth" :error="form.errors.depth" />
            <VInput label="Temperatur (°C)" type="number" v-model="form.temperature" :error="form.errors.temperature" />
            <VInput label="Luftdruck gemessen (hPa)" type="number" v-model="form.air_pressure" :error="form.errors.air_pressure" />
            <VInput label="Köder" v-model="form.bait" :error="form.errors.bait" />
          </div>
          <ReleaseToggle v-model="form.released" label="Verbleib" block class="mt-5" />
        </section>
      </div>

      <!-- Position -->
      <section class="ui-card space-y-4 p-5 md:p-6 lg:col-span-12">
        <div>
          <p class="ui-kicker">Position</p>
          <p class="mt-1 text-sm text-(--app-muted)">Tippe in die Karte, um den Fangort zu ändern.</p>
        </div>
        <GoogleMapPicker label="Position auswählen" :initialLat="form.latitude" :initialLng="form.longitude"
          @locationSelected="updateLocation" />
        <div class="grid gap-4 md:grid-cols-[2fr_1fr_1fr]">
          <VInput label="Adresse" v-model="form.address" :error="form.errors.address" />
          <VInput label="Breitengrad" v-model="form.latitude" :error="form.errors.latitude" />
          <VInput label="Längengrad" v-model="form.longitude" :error="form.errors.longitude" />
        </div>
      </section>

      <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-between lg:col-span-12">
        <VButton type="button" variant="ghost" class="text-(--app-danger)!" @click="showDeleteDialog = true">
          <TrashIcon class="size-4" /> Fang löschen
        </VButton>
        <VButton type="submit" size="lg" :disabled="form.processing">
          <CheckIcon class="size-5" /> Änderungen speichern
        </VButton>
      </div>
    </form>

    <transition name="fade">
      <div v-if="showDeleteDialog" class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-[#021015]/60 backdrop-blur-sm" @click="showDeleteDialog = false" />

        <!-- Dialog -->
        <div class="relative z-10 w-full max-w-md rounded-3xl border border-(--app-border) bg-(--app-surface) p-6 shadow-(--app-shadow-lg)">
          <span class="flex size-12 items-center justify-center rounded-2xl bg-(--app-danger-soft) text-(--app-danger)">
            <TrashIcon class="size-6" />
          </span>
          <h2 class="font-display mt-4 text-3xl text-(--app-ink)">Fang löschen?</h2>
          <p class="mt-2 text-sm text-(--app-muted)">
            Dieser Fang wird mit allen Fotos <strong class="text-(--app-ink)">dauerhaft gelöscht</strong>.
            Das kann nicht rückgängig gemacht werden.
          </p>

          <div class="mt-6 flex justify-end gap-2">
            <VButton variant="secondary" @click="showDeleteDialog = false">Abbrechen</VButton>
            <VButton variant="danger" @click="deleteCatched">Ja, löschen</VButton>
          </div>
        </div>
      </div>
    </transition>
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
import CatchSpeciesWaterFields from '@/components/CatchSpeciesWaterFields.vue'
import ImageAndUploadPreview from '@/components/ImageAndUploadPreview.vue'
import ReleaseToggle from '@/components/ReleaseToggle.vue'
import { TrashIcon, CheckIcon } from '@heroicons/vue/24/outline'

/* ------------------------------------------------------------------ */
/* Props */
/* ------------------------------------------------------------------ */
const props = defineProps({
  catched: Object,
  errors: Object,
  fish: Array,
  lakes: Array,
  rivers: Array,
  states: Array,
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
  released: props.catched?.released ?? null,
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
const showDeleteDialog = ref(false)

const deleteCatched = () => {
  form.delete(route('app.catched.destroy', props.catched.id), {
    onFinish: () => {
      showDeleteDialog.value = false
    },
  })
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
<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>