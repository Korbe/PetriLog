<script setup>
import { onMounted, ref } from 'vue';
import Sidebar from './Sidebar/Sidebar.vue';
import Header from './Header/Header.vue';
import Banner from '@/components/Banner.vue';
import { router } from '@inertiajs/vue3';

const sidebarOpen = ref(false);

function closeSidebarOnNavigate() {
  sidebarOpen.value = false;
}

onMounted(() => {
  router.on('navigate', closeSidebarOnNavigate);

  

  if (typeof window === 'undefined') return;

  alert("Ist deferredPrompt null " + deferredPrompt === undefined)

  if(window.deferredPrompt != undefined)
    return;
  
  window.deferredPrompt == undefined

  // Globales Objekt vorbereiten
  window.deferredPrompt = null;

  // Listener nur einmal hinzufügen
  const handleBeforeInstall = (e) => {
    e.preventDefault();
    window.deferredPrompt = e;
    alert('beforeinstallprompt event captured');
    window.removeEventListener('beforeinstallprompt', handleBeforeInstall);
  };

  window.addEventListener('beforeinstallprompt', handleBeforeInstall);
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

      <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-[96rem] mx-auto">
        <slot />
      </div>

    </div>

  </div>
</template>