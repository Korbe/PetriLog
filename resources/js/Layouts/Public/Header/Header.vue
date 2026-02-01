<template>
  <header class="fixed top-2 md:top-6 w-full z-30">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
      <div
        class="relative flex items-center justify-between gap-3 h-14 rounded-2xl px-3 backdrop-blur-xs bg-white/90 shadow-lg shadow-black/[0.03] before:absolute before:inset-0 before:rounded-[inherit] before:border before:border-transparent before:[background:linear-gradient(var(--color-gray-100),var(--color-gray-200))_border-box] before:[mask:linear-gradient(white_0_0)_padding-box,_linear-gradient(white_0_0)] before:[mask-composite:exclude_!important] before:pointer-events-none">

        <!-- Site branding -->
        <div class="flex-1 flex items-center">
          <!-- Logo -->
          <Link class="inline-flex" href="/" aria-label="PetriLog">
          <img src="/logo.png" alt="Logo" class="w-32" />
          </Link>
        </div>

        <!-- Desktop navigation -->
        <nav class="hidden md:flex md:grow">

          <!-- Desktop menu links -->
          <ul class="text-sm flex grow justify-center flex-wrap items-center gap-4 lg:gap-8">
            <li class="px-3 py-1">
              <Link class="text-gray-700 hover:text-gray-900 flex items-center transition font-medium" href="/pricing">
              Preis</Link>
            </li>
            <li class="px-3 py-1">
              <Link class="text-gray-700 hover:text-gray-900 flex items-center transition font-medium" href="/contact">
              Kontakt</Link>
            </li>
          </ul>

        </nav>

        <!-- Desktop sign in links -->
        <ul class="flex-1 flex justify-end items-center gap-3">
          <li v-if="!$page.props.auth.user">
            <Link class="btn-sm text-gray-200 bg-primary-500 hover:bg-gray-50 shadow-sm" href="/login">Login</Link>
          </li>
          <li v-if="!$page.props.auth.user">
            <Link class="btn-sm text-gray-200 bg-gray-800 hover:bg-gray-900 shadow-sm" href="/register">Registrieren
            </Link>
          </li>
          <li v-if="$page.props.auth.user">
            <Link class="btn-sm text-gray-200 bg-gray-800 hover:bg-gray-900 shadow-sm" href="/dashboard">Dashboard
            </Link>
          </li>
        </ul>

        <!-- Mobile menu -->
        <div class="flex md:hidden">

          <!-- Hamburger button -->
          <button
            class="group inline-flex w-8 h-8 text-gray-800 bg-white text-center items-center justify-center transition"
            ref="hamburger" :class="{ active: mobileNavOpen }" aria-controls="mobile-nav" :aria-expanded="mobileNavOpen"
            @click="mobileNavOpen = !mobileNavOpen">
            <span class="sr-only">Menu</span>
            <svg class="fill-current pointer-events-none" width="16" height="16" viewBox="0 0 16 16"
              xmlns="http://www.w3.org/2000/svg">
              <rect
                class="origin-center transition-all duration-300 ease-[cubic-bezier(.5,.85,.25,1.1)] -translate-y-[5px] translate-x-[7px] group-aria-expanded:rotate-[315deg] group-aria-expanded:translate-y-0 group-aria-expanded:translate-x-0"
                y="7" x="7" width="9" height="2" rx="1"></rect>
              <rect
                class="origin-center group-aria-expanded:rotate-45 transition-all duration-300 ease-[cubic-bezier(.5,.85,.25,1.8)]"
                y="7" width="16" height="2" rx="1"></rect>
              <rect
                class="origin-center transition-all duration-300 ease-[cubic-bezier(.5,.85,.25,1.1)] translate-y-[5px] group-aria-expanded:rotate-[135deg] group-aria-expanded:translate-y-0"
                y="7" width="9" height="2" rx="1"></rect>
            </svg>
          </button>

          <!-- Mobile navigation -->
          <Transition enter-active-class="transition ease-out duration-200 transform"
            enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-out duration-200" leave-from-class="opacity-100"
            leave-to-class="opacity-0">
            <nav id="mobile-nav" ref="mobileNav" v-show="mobileNavOpen"
              class="absolute top-full z-20 left-0 w-full bg-white rounded-xl shadow-lg shadow-black/[0.03] before:absolute before:inset-0 before:rounded-[inherit] before:border before:border-transparent before:[background:linear-gradient(var(--color-gray-100),var(--color-gray-200))_border-box] before:[mask:linear-gradient(white_0_0)_padding-box,_linear-gradient(white_0_0)] before:[mask-composite:exclude_!important] before:pointer-events-none">
              <ul class="text-sm p-2">
                <li>
                  <Link class="flex text-gray-700 hover:bg-gray-100 rounded-lg py-1.5 px-2" href="/pricing">Preis
                  </Link>
                </li>
                <li>
                  <Link class="flex text-gray-700 hover:bg-gray-100 rounded-lg py-1.5 px-2" href="/contact">Kontakt
                  </Link>
                </li>

              </ul>
            </nav>
          </Transition>

        </div>

      </div>
    </div>
  </header>
</template>
<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import Dropdown from './Partials/Dropdown.vue'

const mobileNavOpen = ref(false)
const mobileNav = ref(null)
const hamburger = ref(null)

const clickOutside = (e) => {
  if (!mobileNavOpen.value || mobileNav.value.contains(e.target) || hamburger.value.contains(e.target)) return
  mobileNavOpen.value = false
}

const keyPress = ({ keyCode }) => {
  if (!mobileNavOpen.value || keyCode !== 27) return
  mobileNavOpen.value = false
}

onMounted(() => {
  document.addEventListener('click', clickOutside)
  document.addEventListener('keydown', keyPress)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', clickOutside)
  document.removeEventListener('keydown', keyPress)
})
</script>