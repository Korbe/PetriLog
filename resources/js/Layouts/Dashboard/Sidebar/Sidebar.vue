<template>
  <div class="min-w-fit">
    <!-- Sidebar backdrop (mobile only) -->
    <div class="fixed inset-0 z-40 bg-[#021015]/60 backdrop-blur-sm transition-opacity duration-200 lg:hidden lg:z-auto"
      :class="sidebarOpen ? 'opacity-100' : 'opacity-0 pointer-events-none'" aria-hidden="true"></div>

    <!-- Sidebar -->
    <div id="sidebar" ref="sidebar"
      class="no-scrollbar absolute left-0 top-0 z-40 flex h-[100dvh] w-72 shrink-0 flex-col overflow-y-auto bg-(--app-sidebar) px-4 pb-4 pt-5 text-white transition-all duration-200 ease-in-out lg:static lg:left-auto lg:top-auto lg:flex! lg:w-20 lg:translate-x-0 lg:overflow-y-auto lg:sidebar-expanded:!w-72 2xl:w-72!"
      :class="sidebarOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-72'">

      <!-- Water depth lines -->
      <DepthContours class="pointer-events-none absolute inset-x-0 bottom-0 h-72 w-full opacity-30" :labels="false"
        :animated="false" />

      <!-- Sidebar header -->
      <div class="relative mb-7 flex items-center justify-between pl-2 pr-1">
        <Link href="/dashboard" class="flex items-center gap-2.5" aria-label="PetriLog Dashboard">
          <img src="/logo-short.png" alt="" class="size-9 shrink-0" />
          <span
            class="text-xl font-medium tracking-tight lg:opacity-0 lg:sidebar-expanded:opacity-100 2xl:opacity-100 duration-200">PetriLog</span>
        </Link>

        <!-- Close button (mobile) -->
        <button ref="trigger" class="flex size-9 items-center justify-center rounded-full text-white/60 hover:bg-white/10 hover:text-white lg:hidden"
          @click.stop="$emit('close-sidebar')" aria-controls="sidebar" :aria-expanded="sidebarOpen">
          <span class="sr-only">Menü schließen</span>
          <XMarkIcon class="size-5" />
        </button>
      </div>

      <!-- New catch -->
      <Link :href="route('app.catched.create')"
        class="relative mb-6 flex items-center justify-center gap-2 rounded-full bg-primary-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-primary-900/40 transition hover:bg-primary-400"
        title="Fang eintragen">
        <PlusIcon class="size-5 shrink-0" />
        <span class="lg:hidden lg:sidebar-expanded:inline 2xl:inline">Fang eintragen</span>
      </Link>

      <!-- Links -->
      <nav class="relative space-y-6" aria-label="App Navigation">
        <div v-for="group in visibleGroups" :key="group.title">
          <h3 class="font-log mb-2 px-3 text-[10px] uppercase tracking-[0.2em] text-white/35">
            <span class="hidden text-center lg:block lg:sidebar-expanded:hidden 2xl:hidden" aria-hidden="true">•••</span>
            <span class="lg:hidden lg:sidebar-expanded:block 2xl:block">{{ group.title }}</span>
          </h3>

          <ul class="space-y-0.5">
            <li v-for="item in group.items" :key="item.href">
              <component :is="item.external ? 'a' : Link" :href="item.href" :title="item.label"
                class="group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-[15px] font-medium transition lg:text-sm"
                :class="isActive(item) ? 'bg-white/10 text-white' : 'text-white/65 hover:bg-white/5 hover:text-white'"
                :aria-current="isActive(item) ? 'page' : undefined">
                <span v-if="isActive(item)" class="absolute -left-4 top-1/2 h-6 w-1 -translate-y-1/2 rounded-r-full bg-(--app-float)"
                  aria-hidden="true"></span>
                <component :is="item.icon" class="size-5 shrink-0 transition"
                  :class="isActive(item) ? 'text-primary-300' : 'text-white/45 group-hover:text-white/80'" />
                <span class="truncate lg:opacity-0 lg:sidebar-expanded:opacity-100 2xl:opacity-100 duration-200">{{ item.label }}</span>
              </component>
            </li>

            <li v-if="group.title === 'Fangbuch' && pwa.canPrompt && !pwa.installed">
              <button type="button" title="App herunterladen"
                class="group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-[15px] font-medium text-white/65 transition hover:bg-white/5 hover:text-white lg:text-sm"
                @click="installPwa">
                <ArrowDownTrayIcon class="size-5 shrink-0 text-(--app-float)" />
                <span class="truncate lg:opacity-0 lg:sidebar-expanded:opacity-100 2xl:opacity-100 duration-200">App herunterladen</span>
              </button>
            </li>
          </ul>
        </div>
      </nav>

      <div class="relative mt-auto space-y-3 pt-8">
      <!-- Plan -->
      <div class="lg:hidden lg:sidebar-expanded:block 2xl:block">
        <a v-if="!user.subscribed" href="/billing-portal"
          class="block rounded-2xl border border-white/10 bg-white/[0.06] p-4 transition hover:bg-white/10">
          <p class="font-log text-[10px] uppercase tracking-[0.2em] text-(--app-float)">Gratis-Plan</p>
          <p class="mt-1.5 text-sm font-semibold">Unbegrenzt Fänge mit PetriLog Plus</p>
          <p class="mt-1 text-xs text-white/55">49,99 € im Jahr · jetzt upgraden →</p>
        </a>
        <div v-else class="flex items-center gap-2 rounded-2xl border border-white/10 bg-white/[0.06] px-4 py-3 text-sm">
          <StarIcon class="size-4 text-(--app-float)" />
          <span class="font-semibold">PetriLog Plus</span>
          <span class="ml-auto text-xs text-white/50">aktiv</span>
        </div>
      </div>

      <!-- Expand / collapse button -->
      <div class="hidden justify-end lg:flex 2xl:hidden">
        <button class="flex size-9 items-center justify-center rounded-full text-white/50 hover:bg-white/10 hover:text-white"
          @click.prevent="sidebarExpanded = !sidebarExpanded">
          <span class="sr-only">Seitenleiste ein-/ausklappen</span>
          <ChevronDoubleRightIcon class="size-4 transition-transform sidebar-expanded:rotate-180" />
        </button>
      </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { h, ref, onMounted, onUnmounted, watch, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import {
  HomeIcon, BookOpenIcon, PhotoIcon, MapIcon, StarIcon, Cog6ToothIcon, LifebuoyIcon, PlusIcon,
  ArrowDownTrayIcon, XMarkIcon, ChevronDoubleRightIcon, MapPinIcon, DocumentChartBarIcon, CommandLineIcon,
} from '@heroicons/vue/24/outline'
import { pwa, installPwa } from '@/pwa'
import DepthContours from '@/components/public/DepthContours.vue'

// Outline fish in the heroicons style
const FishIcon = (props) => h('svg', { ...props, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': 1.5, 'aria-hidden': 'true' }, [
  h('path', { d: 'M2.5 12c2.6-3.6 6.1-5.5 10-5.5 3.1 0 5.6 1.4 7.5 4L22 8.5v7l-2 -2c-1.9 2.6-4.4 4-7.5 4-3.9 0-7.4-1.9-10-5.5Z', 'stroke-linejoin': 'round' }),
  h('circle', { cx: 7.5, cy: 11, r: 0.9, fill: 'currentColor', stroke: 'none' }),
])

// Props & Emits
const props = defineProps({
  sidebarOpen: Boolean
})
const emit = defineEmits(['close-sidebar'])

// Refs
const trigger = ref(null)
const sidebar = ref(null)

const page = usePage()
const user = computed(() => page.props.auth.user)

const groups = computed(() => [
  {
    title: 'Fangbuch',
    items: [
      { label: 'Dashboard', href: '/dashboard', icon: HomeIcon },
      { label: 'Fänge', href: '/catched', icon: BookOpenIcon },
      { label: 'Galerie', href: '/gallery', icon: PhotoIcon },
      { label: 'Spot-Karte', href: '/spots', icon: MapPinIcon },
      { label: 'Fangmeldung', href: '/report', icon: DocumentChartBarIcon },
    ],
  },
  {
    title: 'Wissen',
    items: [
      { label: 'Gewässer-Wiki', href: '/states', icon: MapIcon },
      { label: 'Fisch-Wiki', href: '/fish', icon: FishIcon },
    ],
  },
  {
    title: 'Konto',
    items: [
      { label: 'PetriLog Abo', href: '/billing-portal', icon: StarIcon, external: true },
      { label: 'Ticket erstellen', href: '/ticket', icon: LifebuoyIcon },
      ...(user.value?.is_admin ? [
        { label: 'Admin', href: '/admin', icon: Cog6ToothIcon, exclude: '/admin/development' },
        { label: 'Development', href: '/admin/development', icon: CommandLineIcon },
      ] : []),
    ],
  },
])
const visibleGroups = computed(() => groups.value.filter(g => g.items.length))

// Sidebar expanded state (localStorage)
const storedSidebarExpanded = localStorage.getItem('sidebar-expanded')
const sidebarExpanded = ref(storedSidebarExpanded === null ? false : storedSidebarExpanded === 'true')

// Current URL (Inertia)
const currentUrl = computed(() => page.url)
const isActive = (item) => (currentUrl.value.startsWith(item.href) && !(item.exclude && currentUrl.value.startsWith(item.exclude)))
  // lakes, rivers and associations live below /states
  || (item.href === '/states' && /^\/(lakes|rivers)\//.test(currentUrl.value))

// Click outside handler
const clickHandler = ({ target }) => {
  if (!sidebar.value || !trigger.value) return
  if (
    !props.sidebarOpen ||
    sidebar.value.contains(target) ||
    trigger.value.contains(target)
  ) return
  emit('close-sidebar')
}

// Escape key handler
const keyHandler = ({ keyCode }) => {
  if (!props.sidebarOpen || keyCode !== 27) return
  emit('close-sidebar')
}

// Lifecycle hooks
onMounted(() => {
  document.addEventListener('click', clickHandler)
  document.addEventListener('keydown', keyHandler)
})

onUnmounted(() => {
  document.removeEventListener('click', clickHandler)
  document.removeEventListener('keydown', keyHandler)
})

// Watch sidebarExpanded to update body class and localStorage
watch(sidebarExpanded, () => {
  localStorage.setItem('sidebar-expanded', sidebarExpanded.value)
  document.body.classList.toggle('sidebar-expanded', sidebarExpanded.value)
}, { immediate: true })
</script>
