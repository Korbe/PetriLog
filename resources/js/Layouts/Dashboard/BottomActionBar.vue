<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { HomeIcon, BookOpenIcon, MapPinIcon, MapIcon, PlusIcon } from '@heroicons/vue/24/outline'

const page = usePage()
const isActive = (href) => page.url.startsWith(href)

const items = computed(() => [
  { label: 'Start', href: route('app.dashboard'), match: '/dashboard', icon: HomeIcon },
  { label: 'Fänge', href: route('app.catched.index'), match: '/catched', icon: BookOpenIcon },
  null, // center action
  // Am Wasser wichtiger als die Galerie (die bleibt über das Menü erreichbar)
  { label: 'Karte', href: '/spots', match: '/spots', icon: MapPinIcon },
  { label: 'Wiki', href: '/states', match: '/states', icon: MapIcon },
])
</script>

<template>
  <!-- Mobile Bottom Bar -->
  <nav aria-label="Schnellnavigation"
    class="fixed inset-x-3 bottom-[calc(0.75rem+env(safe-area-inset-bottom))] z-40 rounded-[1.4rem] border border-(--app-border) bg-(--app-surface)/90 shadow-(--app-shadow-lg) backdrop-blur-md sm:hidden">
    <ul class="grid h-16 grid-cols-5 items-center">
      <li v-for="(item, i) in items" :key="i" class="flex justify-center">
        <Link v-if="!item" :href="route('app.catched.create')" aria-label="Fang eintragen"
          class="-mt-7 flex size-14 items-center justify-center rounded-full bg-primary-500 text-white shadow-lg shadow-primary-500/40 ring-4 ring-(--app-bg) transition active:scale-95">
          <PlusIcon class="size-7" />
        </Link>
        <Link v-else :href="item.href"
          class="flex flex-col items-center gap-0.5 px-2 py-1 text-[11px] font-medium transition"
          :class="isActive(item.match) && !$page.url.startsWith('/catched/create') ? 'text-(--app-accent-ink)' : 'text-(--app-subtle)'"
          :aria-current="isActive(item.match) ? 'page' : undefined">
          <component :is="item.icon" class="size-6" />
          {{ item.label }}
        </Link>
      </li>
    </ul>
  </nav>
</template>
