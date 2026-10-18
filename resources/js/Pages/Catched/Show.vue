<template>
  <PageWrapper :title="catched.fish?.name ?? 'Unbekannter Fisch'" :eyebrow="`${water} · ${formatDate(catched.date)}`"
    :backTo="route('app.catched.index')">
    <template v-slot:actions>
      <VButton v-if="catched != null" variant="secondary" :href="editUrl">
        <PencilSquareIcon class="size-4" /> Bearbeiten
      </VButton>
      <VButton @click="openShare">
        <ShareIcon class="size-4" /> {{ shareUrl ? 'Geteilt' : 'Teilen' }}
      </VButton>
    </template>

    <div class="grid gap-5 md:gap-6 lg:grid-cols-12">

      <!-- Photos -->
      <section class="lg:col-span-7" aria-label="Fotos">
        <div v-if="media.length" class="space-y-3">
          <button type="button" class="group block w-full overflow-hidden rounded-[1.25rem] bg-(--app-surface-2)"
            @click="openLightbox(selected)">
            <img :src="media[selected].original_url" :alt="`Foto ${selected + 1} vom Fang`"
              class="aspect-[4/3] w-full object-cover transition duration-500 group-hover:scale-[1.02]" />
          </button>
          <div v-if="media.length > 1" class="grid grid-cols-3 gap-3">
            <button v-for="(m, i) in media" :key="m.id ?? i" type="button" @click="selected = i"
              class="overflow-hidden rounded-xl ring-2 ring-offset-2 ring-offset-(--app-bg) transition"
              :class="i === selected ? 'ring-primary-500' : 'ring-transparent opacity-70 hover:opacity-100'">
              <img :src="m.original_url" :alt="`Foto ${i + 1}`" class="aspect-[4/3] w-full object-cover" loading="lazy" />
            </button>
          </div>
        </div>
        <div v-else class="ui-card flex aspect-[4/3] flex-col items-center justify-center p-8 text-center">
          <PhotoIcon class="size-10 text-(--app-subtle)" />
          <p class="ui-muted mt-3 text-sm">Zu diesem Fang gibt es noch keine Fotos.</p>
          <VButton class="mt-4" size="sm" variant="secondary" :href="editUrl">Fotos hinzufügen</VButton>
        </div>
      </section>

      <!-- Measurements -->
      <section class="space-y-5 lg:col-span-5" aria-label="Fangdaten">
        <div class="ui-card p-5 md:p-6">
          <div class="flex items-center justify-between gap-3">
            <p class="ui-kicker">Fangdaten</p>
            <span v-if="catched.released === true" class="ui-badge ui-badge-success">Zurückgesetzt</span>
            <span v-else-if="catched.released === false" class="ui-badge ui-badge-accent">Entnommen</span>
          </div>
          <dl class="mt-4 grid grid-cols-2 gap-3">
            <div v-for="m in measurements" :key="m.label" class="ui-panel p-4">
              <dt class="text-xs text-(--app-subtle)">{{ m.label }}</dt>
              <dd class="mt-1 truncate" :class="m.value ? 'font-log text-lg text-(--app-ink)' : 'text-(--app-subtle)'">
                {{ m.value ?? '–' }}
              </dd>
            </div>
          </dl>
          <div class="mt-4 flex items-center gap-2 text-sm text-(--app-muted)">
            <CalendarDaysIcon class="size-4" />
            {{ formatDate(catched.date) }} Uhr
          </div>
        </div>

        <!-- Wetter, Mond & Sonne -->
        <div class="ui-card p-5 md:p-6">
          <p class="ui-kicker">Bedingungen beim Fang</p>

          <dl v-if="catched.weather_status === 'ok'" class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-2 xl:grid-cols-3">
            <div v-for="w in weather" :key="w.label" class="ui-panel p-3.5">
              <dt class="flex items-center gap-1.5 text-xs text-(--app-subtle)">
                <component :is="w.icon" class="size-3.5" /> {{ w.label }}
              </dt>
              <dd class="font-log mt-1 truncate text-base" :class="w.value ? 'text-(--app-ink)' : 'text-(--app-subtle)'">{{ w.value ?? '–' }}</dd>
              <dd v-if="w.sub" class="text-xs leading-snug" :class="w.subTone ?? 'text-(--app-muted)'">{{ w.sub }}</dd>
            </div>
          </dl>
          <p v-else class="ui-panel mt-4 flex items-start gap-2.5 px-4 py-3 text-sm text-(--app-muted)">
            <CloudIcon class="mt-0.5 size-4 shrink-0" />
            {{ weatherMessage }}
          </p>

          <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-1 2xl:grid-cols-2">
            <div class="ui-panel flex items-center gap-3 p-3.5">
              <svg viewBox="0 0 24 24" class="size-9 shrink-0" aria-hidden="true">
                <circle cx="12" cy="12" r="10" class="fill-[#0b2a36]" />
                <path :d="moonPath" class="fill-[#f4efdc]" />
              </svg>
              <div class="min-w-0">
                <p class="truncate text-sm font-semibold">{{ astro.moon.name }}</p>
                <p class="font-log text-xs text-(--app-muted)">{{ astro.moon.illumination }} % beleuchtet</p>
              </div>
            </div>
            <div class="ui-panel flex items-center gap-3 p-3.5">
              <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-[color-mix(in_srgb,var(--app-float)_14%,transparent)] text-(--app-float)">
                <SunIcon class="size-5" />
              </span>
              <div class="min-w-0">
                <p class="truncate text-sm font-semibold">{{ astro.sun.daytime }}</p>
                <p v-if="astro.sun.sunrise" class="font-log truncate text-xs text-(--app-muted)">↑ {{ astro.sun.sunrise }} · ↓ {{ astro.sun.sunset }}</p>
              </div>
            </div>
          </div>

          <p v-if="catched.weather_status === 'ok'" class="mt-3 text-[11px] text-(--app-subtle)">
            Wetterdaten: <a href="https://data.hub.geosphere.at" target="_blank" rel="noopener" class="underline">GeoSphere Austria</a>
            (INCA-Analyse, CC BY 4.0) · Luftdruck auf Meereshöhe
          </p>
        </div>

        <!-- Bemerkungen -->
        <div v-if="catched.remark" class="ui-card p-5 md:p-6">
          <p class="ui-kicker">Bemerkungen</p>
          <div class="ui-prose mt-3" v-html="catched.remark"></div>
        </div>
      </section>

      <!-- Karte -->
      <section class="ui-card p-5 md:p-6 lg:col-span-12" aria-label="Fangort">
        <div class="mb-4 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
          <div>
            <p class="ui-kicker">Hier gefangen</p>
            <p class="mt-1 font-semibold">{{ catched.address || water }}</p>
            <p class="font-log mt-0.5 text-xs text-(--app-subtle)">{{ coords }}</p>
          </div>
          <div class="flex flex-wrap gap-2">
            <a :href="mapsLink" target="_blank" rel="noopener"
              class="inline-flex items-center gap-2 rounded-full border border-(--app-border-strong) px-4 py-2 text-sm font-semibold transition hover:bg-(--app-hover)">
              <MapPinIcon class="size-4" /> Google Maps
            </a>
            <a :href="appleMapsLink" target="_blank" rel="noopener"
              class="inline-flex items-center gap-2 rounded-full border border-(--app-border-strong) px-4 py-2 text-sm font-semibold transition hover:bg-(--app-hover)">
              <MapPinIcon class="size-4" /> Apple Karten
            </a>
          </div>
        </div>

        <MapView base-layer="Luftbild" :latitude="catched.latitude" :longitude="catched.longitude" :title="catched.fish?.name ?? 'Fang'" />
      </section>
    </div>

    <!-- 🔍 Lightbox -->
    <transition name="fade">
      <div v-if="activeIndex !== null" class="fixed inset-0 z-50 flex items-center justify-center bg-[#021015]/90 p-4 backdrop-blur-sm"
        @click.self="closeLightbox" role="dialog" aria-modal="true" aria-label="Foto-Ansicht">
        <img :src="media[activeIndex].original_url" alt="" class="max-h-[85vh] max-w-[92vw] rounded-2xl shadow-2xl" />

        <button class="absolute right-4 top-4 flex size-11 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20"
          @click="closeLightbox" aria-label="Schließen">
          <XMarkIcon class="size-6" />
        </button>

        <template v-if="media.length > 1">
          <button class="absolute left-3 top-1/2 flex size-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20"
            @click.stop="prevImage" aria-label="Vorheriges Foto">
            <ChevronLeftIcon class="size-6" />
          </button>
          <button class="absolute right-3 top-1/2 flex size-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20"
            @click.stop="nextImage" aria-label="Nächstes Foto">
            <ChevronRightIcon class="size-6" />
          </button>
          <span class="font-log absolute bottom-5 left-1/2 -translate-x-1/2 rounded-full bg-white/10 px-3 py-1 text-xs text-white">
            {{ activeIndex + 1 }} / {{ media.length }}
          </span>
        </template>
      </div>
    </transition>

    <ShareDialog v-model="isShareOpen" :share-url="shareUrl" :catched-id="catched.id" />
  </PageWrapper>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import VButton from '@/components/VButton.vue';
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
import ShareDialog from "@/components/ShareDialog.vue";
import MapView from '@/components/MapView.vue';
import {
  ShareIcon, PencilSquareIcon, PhotoIcon, MapPinIcon, CalendarDaysIcon, XMarkIcon, ChevronLeftIcon, ChevronRightIcon,
  CloudIcon, SunIcon, ArrowTrendingUpIcon, ArrowTrendingDownIcon, ArrowLongRightIcon, BeakerIcon, FlagIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  catched: Object,
  astro: Object,
  shareUrl: { type: String, default: null },
  sharedAt: { type: String, default: null },
  editUrl: String,
});

/* Wetter */
const num = (value, digits = 1) => Number(value).toLocaleString('de-AT', { minimumFractionDigits: digits, maximumFractionDigits: digits });
const compass = (deg) => ['N', 'NO', 'O', 'SO', 'S', 'SW', 'W', 'NW'][Math.round(deg / 45) % 8];

const pressureTrend = computed(() => {
  const t = props.catched.weather_pressure_trend;
  if (t === null || t === undefined) return null;
  if (t >= 1) return { label: `steigend · +${num(t)} in 3 Std.`, icon: ArrowTrendingUpIcon, tone: 'text-(--app-success)' };
  if (t <= -1) return { label: `fallend · ${num(t)} in 3 Std.`, icon: ArrowTrendingDownIcon, tone: 'text-(--app-float)' };
  return { label: 'stabil', icon: ArrowLongRightIcon, tone: 'text-(--app-muted)' };
});

const weather = computed(() => {
  const c = props.catched;
  const has = (v) => v !== null && v !== undefined;
  return [
    { label: 'Luft', icon: SunIcon, value: has(c.weather_temperature) ? `${num(c.weather_temperature)} °C` : null },
    {
      label: 'Luftdruck', icon: pressureTrend.value?.icon ?? ArrowLongRightIcon,
      value: has(c.weather_pressure) ? `${num(c.weather_pressure, 0)} hPa` : null,
      sub: pressureTrend.value?.label, subTone: pressureTrend.value?.tone,
    },
    {
      label: 'Wind', icon: FlagIcon,
      value: has(c.weather_wind_speed) ? `${num(c.weather_wind_speed, 0)} km/h` : null,
      sub: has(c.weather_wind_direction) ? `aus ${compass(c.weather_wind_direction)}` : null,
    },
    { label: 'Niederschlag', icon: CloudIcon, value: has(c.weather_precipitation) ? `${num(c.weather_precipitation)} mm` : null, sub: 'letzte 3 Std.' },
    { label: 'Luftfeuchte', icon: BeakerIcon, value: has(c.weather_humidity) ? `${c.weather_humidity} %` : null },
  ];
});

const weatherMessage = computed(() => ({
  unavailable: 'Für diesen Ort oder Zeitpunkt gibt es keine Wetterdaten (verfügbar für Österreich und Grenzgebiete ab 2011).',
  failed: 'Die Wetterdaten konnten leider nicht abgerufen werden.',
}[props.catched.weather_status] ?? 'Wetterdaten werden automatisch ergänzt – sie stehen etwa ein bis zwei Stunden nach dem Fang bereit.'));

// Beleuchteter Teil des Mondes: Außenbogen + Terminator-Ellipse
const moonPath = computed(() => {
  const { phase, waxing } = props.astro.moon;
  const r = 10;
  const k = Math.cos(2 * Math.PI * phase);
  const rx = Math.abs(k) * r;
  const outerSweep = waxing ? 1 : 0;
  const termSweep = (k > 0) === waxing ? 0 : 1;
  return `M12 2 A${r} ${r} 0 0 ${outerSweep} 12 22 A${rx.toFixed(2)} ${r} 0 0 ${termSweep} 12 2 Z`;
});

const mapsLink = `https://www.google.com/maps/search/?api=1&query=${props.catched.latitude},${props.catched.longitude}`;
const appleMapsLink = `https://maps.apple.com/?ll=${props.catched.latitude},${props.catched.longitude}&q=${encodeURIComponent(props.catched.fish?.name ?? 'Fang')}`;

const water = computed(() => props.catched.lake?.name ?? props.catched.river?.name ?? 'Unbekanntes Gewässer');
const coords = computed(() => `${Number(props.catched.latitude).toFixed(5)}° N · ${Number(props.catched.longitude).toFixed(5)}° E`);

const withUnit = (value, unit) => (value !== null && value !== undefined && value !== '' ? `${value} ${unit}` : null);
const measurements = computed(() => [
  { label: 'Länge', value: withUnit(props.catched.length, 'cm') },
  { label: 'Gewicht', value: withUnit(props.catched.weight, 'g') },
  { label: 'Tiefe', value: withUnit(props.catched.depth, 'cm') },
  { label: 'Wassertemperatur', value: withUnit(props.catched.temperature, '°C') },
  // Selbst gemessener Luftdruck – ansonsten steht er automatisch bei den Bedingungen
  ...(props.catched.air_pressure ? [{ label: 'Luftdruck (gemessen)', value: withUnit(props.catched.air_pressure, 'hPa') }] : []),
  { label: 'Köder', value: props.catched.bait || null },
]);

const isShareOpen = ref(false);

const openShare = () => { isShareOpen.value = true; };

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return `${String(date.getDate()).padStart(2, '0')}.${String(date.getMonth() + 1).padStart(2, '0')}.${date.getFullYear()} ${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`;
};

const media = computed(() => {
  return (props.catched?.media ?? []).slice() // Kopie, damit wir das Original nicht mutieren
    .sort((a, b) => a.order_column - b.order_column)
})

const selected = ref(0)
const activeIndex = ref(null)

const openLightbox = index => {
  activeIndex.value = index
  document.body.style.overflow = 'hidden'
}

const closeLightbox = () => {
  activeIndex.value = null
  document.body.style.overflow = ''
}

const prevImage = () => {
  if (activeIndex.value === null) return
  activeIndex.value = (activeIndex.value - 1 + media.value.length) % media.value.length
}

const nextImage = () => {
  if (activeIndex.value === null) return
  activeIndex.value = (activeIndex.value + 1) % media.value.length
}

const onKey = e => {
  if (activeIndex.value === null) return
  if (e.key === 'Escape') closeLightbox()
  if (e.key === 'ArrowLeft') prevImage()
  if (e.key === 'ArrowRight') nextImage()
}

onMounted(() => window.addEventListener('keydown', onKey))
onUnmounted(() => {
  window.removeEventListener('keydown', onKey)
  document.body.style.overflow = ''
})

const catched = props.catched;
</script>
