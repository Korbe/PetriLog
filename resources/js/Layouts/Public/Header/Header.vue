<template>
  <header class="fixed inset-x-0 top-0 z-40 transition-[background-color,box-shadow] duration-300" :class="solid && !menuOpen
    ? 'bg-(--lake-950)/85 shadow-[0_1px_0_rgba(255,255,255,0.08),0_20px_40px_-30px_rgba(0,0,0,0.6)] backdrop-blur-md'
    : 'bg-transparent'">
    <!-- z-50: the bar stays on top of the mobile menu overlay -->
    <div class="relative z-50 max-w-6xl mx-auto px-4 sm:px-6">
      <div class="flex items-center justify-between gap-6 transition-[height] duration-300"
        :class="solid ? 'h-16' : 'h-20'">

        <!-- Brand -->
        <Link href="/" class="group flex items-center gap-2.5 text-(--paper)" aria-label="PetriLog – zur Startseite">
          <img src="/logo-short.png" alt="" width="34" height="34"
            class="size-8 transition-transform duration-500 group-hover:-rotate-12" />
          <span class="text-[1.35rem] font-medium tracking-tight">PetriLog</span>
        </Link>

        <!-- Desktop navigation -->
        <nav class="hidden md:block" aria-label="Hauptnavigation">
          <ul class="flex items-center gap-1 rounded-full border border-white/10 bg-white/[0.04] p-1">
            <li v-for="item in navigation" :key="item.href">
              <SiteLink :href="item.href"
                class="block rounded-full px-4 py-2 text-sm font-medium transition-colors"
                :class="isActive(item) ? 'bg-white/12 text-white' : 'text-(--paper)/70 hover:bg-white/8 hover:text-white'"
                :aria-current="isActive(item) ? 'page' : undefined">
                {{ item.label }}
              </SiteLink>
            </li>
          </ul>
        </nav>

        <!-- Desktop actions -->
        <div class="hidden items-center gap-2 md:flex">
          <template v-if="!user">
            <Link href="/login" class="px-3 py-2 text-sm font-medium text-(--paper)/80 transition hover:text-white">
              Anmelden
            </Link>
            <Link href="/register"
              class="rounded-full bg-(--paper) px-5 py-2.5 text-sm font-semibold text-(--ink) transition hover:bg-white">
              Kostenlos starten
            </Link>
          </template>
          <Link v-else href="/dashboard"
            class="group inline-flex items-center gap-2 rounded-full bg-(--paper) px-5 py-2.5 text-sm font-semibold text-(--ink) transition hover:bg-white">
            Zum Fangbuch
            <svg class="size-3.5 transition-transform group-hover:translate-x-0.5" viewBox="0 0 16 16" fill="none"
              aria-hidden="true">
              <path d="M1 8h13m-5-5 5 5-5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                stroke-linejoin="round" />
            </svg>
          </Link>
        </div>

        <!-- Mobile menu button -->
        <button type="button"
          class="relative z-50 flex size-11 items-center justify-center rounded-full border border-white/15 bg-white/[0.06] text-(--paper) md:hidden"
          aria-controls="mobile-nav" :aria-expanded="menuOpen" @click="menuOpen = !menuOpen">
          <span class="sr-only">{{ menuOpen ? 'Menü schließen' : 'Menü öffnen' }}</span>
          <span class="relative block h-3 w-5" aria-hidden="true">
            <span class="absolute left-0 h-[1.5px] w-5 rounded bg-current transition-all duration-300"
              :class="menuOpen ? 'top-1.5 rotate-45' : 'top-0'"></span>
            <span class="absolute left-0 h-[1.5px] rounded bg-current transition-all duration-300"
              :class="menuOpen ? 'top-1.5 w-5 -rotate-45' : 'top-3 w-3.5'"></span>
          </span>
        </button>
      </div>
    </div>

    <!-- Mobile navigation -->
    <Transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0"
      leave-active-class="transition duration-200 ease-in" leave-to-class="opacity-0">
      <div v-show="menuOpen" id="mobile-nav"
        class="ph-grain fixed inset-0 z-40 isolate flex flex-col overflow-y-auto bg-(--lake-950) px-6 pb-10 pt-28 text-(--paper) md:hidden">
        <DepthContours class="absolute inset-0 -z-10 h-full w-full opacity-60" :labels="false" :animated="false" />

        <nav aria-label="Mobile Navigation">
          <ul class="space-y-1">
            <li v-for="(item, i) in navigation" :key="item.href" :class="menuOpen ? 'ph-rise' : ''"
              :style="{ animationDelay: `${0.05 + i * 0.06}s` }">
              <SiteLink :href="item.href" class="font-display flex items-baseline justify-between border-b border-white/10 py-4 text-5xl"
                :class="isActive(item) ? 'text-primary-300' : ''" @navigate="menuOpen = false">
                {{ item.label }}
                <span class="font-log text-xs text-(--paper)/40">0{{ i + 1 }}</span>
              </SiteLink>
            </li>
          </ul>
        </nav>

        <div class="mt-auto flex flex-col gap-3 pt-12">
          <template v-if="!user">
            <Link href="/register"
              class="rounded-full bg-(--paper) px-6 py-4 text-center text-base font-semibold text-(--ink)">
              Kostenlos starten
            </Link>
            <Link href="/login"
              class="rounded-full border border-white/20 px-6 py-4 text-center text-base font-medium">
              Anmelden
            </Link>
          </template>
          <Link v-else href="/dashboard"
            class="rounded-full bg-(--paper) px-6 py-4 text-center text-base font-semibold text-(--ink)">
            Zum Fangbuch
          </Link>
        </div>
      </div>
    </Transition>
  </header>
</template>
<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import DepthContours from '@/components/public/DepthContours.vue'
import SiteLink from '@/components/public/SiteLink.vue'

const props = defineProps({
  // Transparent until the user scrolls (page starts with a dark hero).
  overlay: { type: Boolean, default: false },
})

const navigation = [
  { label: "So geht's", href: '/#so-gehts' },
  { label: 'Funktionen', href: '/#funktionen' },
  { label: 'Preise', href: '/pricing' },
  { label: 'Kontakt', href: '/contact' },
]

const page = usePage()
const user = computed(() => page.props.auth?.user)
const isActive = (item) => !item.href.startsWith('/#') && page.url.startsWith(item.href)

const menuOpen = ref(false)
const scrolled = ref(false)
const solid = computed(() => !props.overlay || scrolled.value)

const onScroll = () => { scrolled.value = window.scrollY > 24 }
const onKey = (e) => { if (e.key === 'Escape') menuOpen.value = false }
let removeNavigateListener = null

watch(menuOpen, (open) => {
  document.documentElement.style.overflow = open ? 'hidden' : ''
})

onMounted(() => {
  onScroll()
  window.addEventListener('scroll', onScroll, { passive: true })
  window.addEventListener('keydown', onKey)
  removeNavigateListener = router.on('navigate', () => { menuOpen.value = false })
})

onBeforeUnmount(() => {
  window.removeEventListener('scroll', onScroll)
  window.removeEventListener('keydown', onKey)
  removeNavigateListener?.()
  document.documentElement.style.overflow = ''
})
</script>
