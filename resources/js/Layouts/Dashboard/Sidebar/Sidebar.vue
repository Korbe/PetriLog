<template>
  <div class="min-w-fit">
    <!-- Sidebar backdrop (mobile only) -->
    <div class="fixed inset-0 bg-gray-900 z-40 lg:hidden lg:z-auto transition-opacity duration-200"
      :class="sidebarOpen ? 'opacity-100' : 'opacity-0 pointer-events-none'" aria-hidden="true"></div>

    <!-- Sidebar -->
    <div id="sidebar" ref="sidebar"
      class="flex lg:flex! flex-col absolute z-40 left-0 top-0 lg:static lg:left-auto lg:top-auto lg:translate-x-0 h-[100dvh] overflow-y-scroll lg:overflow-y-auto no-scrollbar w-64 lg:w-20 lg:sidebar-expanded:!w-64 2xl:w-64! shrink-0 bg-white dark:bg-gray-800 p-4 transition-all duration-200 ease-in-out"
      :class="['rounded-r-2xl lg:rounded-none shadow-xs', sidebarOpen ? 'translate-x-0' : '-translate-x-64']">

      <!-- Sidebar header -->
      <div class="flex justify-between mb-10 pr-3 sm:px-2">
        <!-- Close button -->
        <button ref="trigger" class="lg:hidden text-gray-500 hover:text-gray-400" @click.stop="$emit('close-sidebar')"
          aria-controls="sidebar" :aria-expanded="sidebarOpen">
          <span class="sr-only">Close sidebar</span>
          <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M10.7 18.7l1.4-1.4L7.8 13H20v-2H7.8l4.3-4.3-1.4-1.4L4 12z" />
          </svg>
        </button>
        <!-- Logo -->
        <Link class="block" href="/">
        <img src="/logo.png" alt="Logo" class="h-16 hidden 2xl:block" />
        <img src="/logo-short.png" alt="Logo" class="h-10 2xl:hidden" />
        </Link>
      </div>

      <!-- Links -->
      <div class="space-y-8">
        <!-- Pages group -->
        <div>
          <h3 class="text-xs uppercase text-gray-400 dark:text-gray-500 font-semibold pl-3">
            <span class="hidden lg:block lg:sidebar-expanded:hidden 2xl:hidden text-center w-6"
              aria-hidden="true">•••</span>
          </h3>

          <li v-if="!pwaInstalled" class="pl-4 pr-3 py-2 mt-5 rounded-lg mb-0.5 last:mb-0 bg-linear-to-r"
            :class="isActive('/pwa') && 'from-primary-500/[0.12] dark:from-primary-500/[0.24] to-primary-500/[0.04]'">
            <Link href="/pwa" class="block text-gray-800 dark:text-gray-100 truncate transition"
              :class="isActive('/pwa') ? '' : 'hover:text-gray-900 dark:hover:text-white'">
            <div class="flex items-center">
              <InboxArrowDownIcon class="shrink-0 w-5 h-5"
                :class="isActive('/pwa') ? 'text-primary-500' : 'text-gray-400 dark:text-gray-500'" />
              <span
                class="text-lg lg:text-sm font-medium ml-4 lg:opacity-0 lg:sidebar-expanded:opacity-100 2xl:opacity-100 duration-200">
                App installieren
              </span>
            </div>
            </Link>
          </li>

          <ul class="mt-3">
            <li class="pl-4 pr-3 py-2 rounded-lg mb-0.5 last:mb-0 bg-linear-to-r"
              :class="isActive('/dashboard') && 'from-primary-500/[0.12] dark:from-primary-500/[0.24] to-primary-500/[0.04]'">
              <Link href="/dashboard" class="block text-gray-800 dark:text-gray-100 truncate transition"
                :class="isActive('/dashboard') ? '' : 'hover:text-gray-900 dark:hover:text-white'">
              <div class="flex items-center">
                <HomeIcon class="shrink-0 w-5 h-5"
                  :class="isActive('/dashboard') ? 'text-primary-500' : 'text-gray-400 dark:text-gray-500'" />
                <span
                  class="text-lg lg:text-sm font-medium ml-4 lg:opacity-0 lg:sidebar-expanded:opacity-100 2xl:opacity-100 duration-200">Dashboard</span>
              </div>
              </Link>
            </li>

            <li class="pl-4 pr-3 py-2 rounded-lg mb-0.5 last:mb-0 bg-linear-to-r"
              :class="isActive('/catched') && 'from-primary-500/[0.12] dark:from-primary-500/[0.24] to-primary-500/[0.04]'">
              <Link href="/catched" class="block text-gray-800 dark:text-gray-100 truncate transition"
                :class="isActive('/catched') ? '' : 'hover:text-gray-900 dark:hover:text-white'">
              <div class="flex items-center">
                <BookmarkSquareIcon class="shrink-0 w-5 h-5"
                  :class="isActive('/catched') ? 'text-primary-500' : 'text-gray-400 dark:text-gray-500'" />
                <span
                  class="text-lg lg:text-sm font-medium ml-4 lg:opacity-0 lg:sidebar-expanded:opacity-100 2xl:opacity-100 duration-200">Fänge</span>
              </div>
              </Link>
            </li>

            <li class="pl-4 pr-3 py-2 rounded-lg mb-0.5 last:mb-0 bg-linear-to-r"
              :class="isActive('/gallery') && 'from-primary-500/[0.12] dark:from-primary-500/[0.24] to-primary-500/[0.04]'">
              <Link href="/gallery" class="block text-gray-800 dark:text-gray-100 truncate transition"
                :class="isActive('/gallery') ? '' : 'hover:text-gray-900 dark:hover:text-white'">
              <div class="flex items-center">
                <PhotoIcon class="shrink-0 w-5 h-5"
                  :class="isActive('/gallery') ? 'text-primary-500' : 'text-gray-400 dark:text-gray-500'" />
                <span
                  class="text-lg lg:text-sm font-medium ml-4 lg:opacity-0 lg:sidebar-expanded:opacity-100 2xl:opacity-100 duration-200">Galerie</span>
              </div>
              </Link>
            </li>

            <li class="pl-4 pr-3 py-2 rounded-lg mb-0.5 last:mb-0 bg-linear-to-r"
              :class="isActive('/states') && 'from-primary-500/[0.12] dark:from-primary-500/[0.24] to-primary-500/[0.04]'">
              <Link href="/states" class="block text-gray-800 dark:text-gray-100 truncate transition"
                :class="isActive('/states') ? '' : 'hover:text-gray-900 dark:hover:text-white'">
              <div class="flex items-center">
                <WalletIcon class="shrink-0 w-5 h-5"
                  :class="isActive('/states') ? 'text-primary-500' : 'text-gray-400 dark:text-gray-500'" />
                <span
                  class="text-lg lg:text-sm font-medium ml-4 lg:opacity-0 lg:sidebar-expanded:opacity-100 2xl:opacity-100 duration-200">Gewässer
                  Wiki</span>
              </div>
              </Link>
            </li>

            <li class="pl-4 pr-3 py-2 rounded-lg mb-0.5 last:mb-0 bg-linear-to-r"
              :class="isActive('/fish') && 'from-primary-500/[0.12] dark:from-primary-500/[0.24] to-primary-500/[0.04]'">
              <Link href="/fish" class="block text-gray-800 dark:text-gray-100 truncate transition"
                :class="isActive('/fish') ? '' : 'hover:text-gray-900 dark:hover:text-white'">
              <div class="flex items-center">
                <EyeIcon class="shrink-0 w-5 h-5"
                  :class="isActive('/fish') ? 'text-primary-500' : 'text-gray-400 dark:text-gray-500'" />
                <span
                  class="text-lg lg:text-sm font-medium ml-4 lg:opacity-0 lg:sidebar-expanded:opacity-100 2xl:opacity-100 duration-200">Fisch
                  Wiki</span>
              </div>
              </Link>
            </li>

            <li class="pl-4 pr-3 py-2 rounded-lg mb-0.5 last:mb-0 bg-linear-to-r"
              :class="isActive('/billing') && 'from-primary-500/[0.12] dark:from-primary-500/[0.24] to-primary-500/[0.04]'">
              <Link href="/billing" class="block text-gray-800 dark:text-gray-100 truncate transition"
                :class="isActive('/billing') ? '' : 'hover:text-gray-900 dark:hover:text-white'">
              <div class="flex items-center">
                <StarIcon class="shrink-0 w-5 h-5"
                  :class="isActive('/billing') ? 'text-primary-500' : 'text-gray-400 dark:text-gray-500'" />
                <span
                  class="text-lg lg:text-sm font-medium ml-4 lg:opacity-0 lg:sidebar-expanded:opacity-100 2xl:opacity-100 duration-200">PetriLog
                  Abo</span>
              </div>
              </Link>
            </li>

            <li class="pl-4 pr-3 py-2 mt-5 rounded-lg mb-0.5 last:mb-0 bg-linear-to-r"
              :class="isActive('/bug-report') && 'from-primary-500/[0.12] dark:from-primary-500/[0.24] to-primary-500/[0.04]'">
              <Link href="/bug-report" class="block text-gray-800 dark:text-gray-100 truncate transition"
                :class="isActive('/bug-report') ? '' : 'hover:text-gray-900 dark:hover:text-white'">
              <div class="flex items-center">
                <BugAntIcon class="shrink-0 w-5 h-5"
                  :class="isActive('/bug-report') ? 'text-primary-500' : 'text-gray-400 dark:text-gray-500'" />
                <span
                  class="text-lg lg:text-sm font-medium ml-4 lg:opacity-0 lg:sidebar-expanded:opacity-100 2xl:opacity-100 duration-200">Fehler
                  melden</span>
              </div>
              </Link>
            </li>

            <li v-if="$page.props.auth.user.isAdmin" class="pl-4 pr-3 py-2 rounded-lg mb-0.5 last:mb-0 bg-linear-to-r"
              :class="isActive('/admin') && 'from-primary-500/[0.12] dark:from-primary-500/[0.24] to-primary-500/[0.04]'">
              <Link href="/admin" class="block text-gray-800 dark:text-gray-100 truncate transition"
                :class="isActive('/admin') ? '' : 'hover:text-gray-900 dark:hover:text-white'">
              <div class="flex items-center">
                <AdjustmentsHorizontalIcon class="shrink-0 w-5 h-5"
                  :class="isActive('/admin') ? 'text-primary-500' : 'text-gray-400 dark:text-gray-500'" />
                <span
                  class="text-lg lg:text-sm font-medium ml-4 lg:opacity-0 lg:sidebar-expanded:opacity-100 2xl:opacity-100 duration-200">Admin</span>
              </div>
              </Link>
            </li>
          </ul>
        </div>
      </div>

      <!-- Expand / collapse button -->
      <div class="pt-3 hidden lg:inline-flex 2xl:hidden justify-end mt-auto">
        <div class="w-12 pl-4 pr-3 py-2">
          <button class="text-gray-400 hover:text-gray-500 dark:text-gray-500 dark:hover:text-gray-400"
            @click.prevent="sidebarExpanded = !sidebarExpanded">
            <span class="sr-only">Expand / collapse sidebar</span>
            <svg class="shrink-0 fill-current text-gray-400 dark:text-gray-500 sidebar-expanded:rotate-180"
              xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
              <path
                d="M15 16a1 1 0 0 1-1-1V1a1 1 0 1 1 2 0v14a1 1 0 0 1-1 1ZM8.586 7H1a1 1 0 1 0 0 2h7.586l-2.793 2.793a1 1 0 1 0 1.414 1.414l4.5-4.5A.997.997 0 0 0 12 8.01M11.924 7.617a.997.997 0 0 0-.217-.324l-4.5-4.5a1 1 0 0 0-1.414 1.414L8.586 7M12 7.99a.996.996 0 0 0-.076-.373Z" />
            </svg>
          </button>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>

import { ref, onMounted, onUnmounted, watch, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { HomeIcon, BookmarkSquareIcon, PhotoIcon, WalletIcon, EyeIcon, StarIcon, BugAntIcon, AdjustmentsHorizontalIcon, InboxArrowDownIcon } from '@heroicons/vue/24/solid'

// Props & Emits
const props = defineProps({
  sidebarOpen: Boolean
})
const emit = defineEmits(['close-sidebar'])

// Refs
const pwaInstalled = ref(false)
const trigger = ref(null)
const sidebar = ref(null)

// Sidebar expanded state (localStorage)
const storedSidebarExpanded = localStorage.getItem('sidebar-expanded')
const sidebarExpanded = ref(storedSidebarExpanded === null ? false : storedSidebarExpanded === 'true')

// Current URL (Inertia)
const currentUrl = computed(() => usePage().url)
const isActive = (href) => currentUrl.value.startsWith(href)

// Dummy currentRoute object
const currentRoute = {
  name: '',
  path: './',
  hash: '',
  query: {},
  params: {},
  fullPath: './',
  matched: [],
}

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


function checkPWAInstalled() {
  const ua = window.navigator.userAgent.toLowerCase()
  const isMobile = /iphone|ipad|ipod|android/.test(ua)
  const isStandalone =
    window.matchMedia('(display-mode: standalone)').matches ||
    window.navigator.standalone === true

  // Button nur anzeigen, wenn mobile UND App noch nicht installiert
  pwaInstalled.value = !(isMobile && !isStandalone)
}

// Lifecycle hooks
onMounted(() => {
  document.addEventListener('click', clickHandler)
  document.addEventListener('keydown', keyHandler)
  checkPWAInstalled()
})

onUnmounted(() => {
  document.removeEventListener('click', clickHandler)
  document.removeEventListener('keydown', keyHandler)
})

// Watch sidebarExpanded to update body class and localStorage
watch(sidebarExpanded, () => {
  localStorage.setItem('sidebar-expanded', sidebarExpanded.value)
  document.body.classList.toggle('sidebar-expanded', sidebarExpanded.value)
})
</script>