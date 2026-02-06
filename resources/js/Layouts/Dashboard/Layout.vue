<script setup>
import { onMounted, ref } from 'vue';
import Sidebar from './Sidebar/Sidebar.vue';
import Header from './Header/Header.vue';
import Banner from '@/components/Banner.vue';
import { router } from '@inertiajs/vue3';
import BottomActionBar from './BottomActionBar.vue'

const sidebarOpen = ref(false);

function closeSidebarOnNavigate() {
  sidebarOpen.value = false;
}

onMounted(() => {
  router.on('navigate', closeSidebarOnNavigate);
});

</script>
<template>

  <div
    class="flex h-[100dvh] overflow-hidden font-inter antialiased bg-gray-100 dark:bg-gray-900 text-gray-600 dark:text-gray-400">

    <!-- Sidebar -->
    <Sidebar :sidebarOpen="sidebarOpen" @close-sidebar="sidebarOpen = false" />

    <!-- Content area -->
    <div scroll-region class="relative flex flex-col flex-1 overflow-y-auto overflow-x-hidden">

      <!-- Site header -->
      <Header :sidebarOpen="sidebarOpen" @toggle-sidebar="sidebarOpen = !sidebarOpen" />

      <Banner />

      <!-- Impersonation Banner -->
      <div v-if="$page.props.isImpersonated" class="bg-red-500 text-white p-4 flex items-center justify-between">
        <span class="font-medium">
          You are currently viewing the application as another user
        </span>

        <Link :href="route('admin.impersonate.leave')" as="button"
          class="px-3 py-1 rounded bg-gray-100 text-gray-800 hover:bg-gray-200">
        Zurück
        </Link>
      </div>

      <div class="px-4 sm:px-6 lg:px-8 py-4 pb-20 w-full max-w-[96rem] mx-auto">
        <slot />
      </div>

    </div>

    <BottomActionBar />

  </div>
</template>