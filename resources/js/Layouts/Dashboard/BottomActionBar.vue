<script setup>
import { Link } from '@inertiajs/vue3'
import {
  HomeIcon,
  PlusIcon,
  UserIcon,
  Cog6ToothIcon
} from '@heroicons/vue/24/outline'
</script>

<template>
  <!-- Mobile Bottom Bar -->
  <nav
    class="fixed bottom-0 inset-x-0 z-50 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 sm:hidden"
  >
    <div class="flex justify-around items-center h-14">

      <Link
        :href="route('app.dashboard')"
        class="flex flex-col items-center text-xs"
      >
        <HomeIcon class="h-6 w-6 mb-0.5" />
        Home
      </Link>

      <Link
        :href="route('app.catched.create')"
        class="flex flex-col items-center text-xs text-primary-600"
      >
        <PlusIcon class="h-7 w-7 mb-0.5" />
        Neu
      </Link>

      <Link
        :href="route('app.profile.show')"
        class="flex flex-col items-center text-xs"
      >
        <UserIcon class="h-6 w-6 mb-0.5" />
        Profil
      </Link>

    </div>
  </nav>
</template>
