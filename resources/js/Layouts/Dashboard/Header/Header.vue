<template>
  <header class="sticky top-0 z-30 border-b border-(--app-border) bg-(--app-bg)/80 backdrop-blur-md">
    <div class="flex h-16 items-center justify-between gap-3 px-4 sm:px-6 lg:px-10">

      <!-- Header: Left side -->
      <div class="flex items-center gap-2">
        <!-- Hamburger button -->
        <button class="ui-icon-btn -ml-2 lg:hidden" @click.stop="$emit('toggle-sidebar')" aria-controls="sidebar"
          :aria-expanded="sidebarOpen">
          <span class="sr-only">Menü öffnen</span>
          <svg class="size-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
            <path d="M3 6h14M3 10h14M3 14h9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
          </svg>
        </button>

        <Link href="/dashboard" class="flex items-center gap-2 lg:hidden" aria-label="PetriLog Dashboard">
          <img src="/logo-short.png" alt="" class="size-7" />
          <span class="text-lg font-medium tracking-tight">PetriLog</span>
        </Link>
      </div>

      <!-- Header: Right side -->
      <div class="flex items-center gap-1.5 sm:gap-2">
        <VButton :href="route('app.catched.create')" size="sm" class="hidden sm:inline-flex">
          <PlusIcon class="size-4" />
          Fang eintragen
        </VButton>
        <ThemeToggle />
        <UserMenu align="right" />
      </div>

    </div>
  </header>
</template>

<script setup>
import { PlusIcon } from '@heroicons/vue/24/outline'
import VButton from '@/components/VButton.vue'
import ThemeToggle from './ThemeToggle.vue'
import UserMenu from './DropdownProfile.vue'

defineProps({
  sidebarOpen: Boolean,
})

defineEmits(['toggle-sidebar'])
</script>
