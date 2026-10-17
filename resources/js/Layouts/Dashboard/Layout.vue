<script setup>
import { onMounted, onBeforeUnmount, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import Sidebar from './Sidebar/Sidebar.vue';
import Header from './Header/Header.vue';
import Banner from '@/components/Banner.vue';
import BottomActionBar from './BottomActionBar.vue'
import AppDialog from '@/components/AppDialog.vue'

const sidebarOpen = ref(false);
let removeNavigateListener = null;

function closeSidebarOnNavigate() {
  sidebarOpen.value = false;
}

onMounted(() => {
  removeNavigateListener = router.on('navigate', closeSidebarOnNavigate);
});

onBeforeUnmount(() => removeNavigateListener?.());
</script>
<template>
  <div class="petri-app flex h-[100dvh] overflow-hidden antialiased">
    <Head>
      <link rel="preconnect" href="https://fonts.bunny.net" head-key="site-fonts-preconnect" />
      <link rel="stylesheet" head-key="site-fonts"
        href="https://fonts.bunny.net/css?family=manrope:500,600,700|schibsted-grotesk:400,500,600,700|jetbrains-mono:400,500&display=swap" />
    </Head>

    <!-- Sidebar -->
    <Sidebar :sidebarOpen="sidebarOpen" @close-sidebar="sidebarOpen = false" />

    <!-- Content area -->
    <div scroll-region class="relative flex flex-1 flex-col overflow-y-auto overflow-x-hidden">

      <!-- Impersonation Banner -->
      <div v-if="$page.props.isImpersonated"
        class="flex items-center justify-between gap-4 bg-(--app-float) px-4 py-2.5 text-sm font-medium text-white sm:px-6 lg:px-10">
        <span>Du siehst die App gerade als anderer User.</span>
        <Link :href="route('admin.impersonate.leave')" as="button"
          class="rounded-full bg-white px-4 py-1.5 text-xs font-semibold text-[#0f1d22] transition hover:bg-white/90">
          Zurück zu meinem Konto
        </Link>
      </div>

      <!-- Site header -->
      <Header :sidebarOpen="sidebarOpen" @toggle-sidebar="sidebarOpen = !sidebarOpen" />

      <Banner />

      <main class="mx-auto w-full max-w-[88rem] px-4 pb-32 pt-6 sm:px-6 sm:pb-14 lg:px-10 lg:pt-8">
        <slot />
      </main>

    </div>

    <BottomActionBar />

    <!-- Ersatz für confirm()/alert() – siehe composables/useDialog.js -->
    <AppDialog />

  </div>
</template>
