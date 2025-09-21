<script setup>
const props = defineProps({
  border: Boolean
})
</script>

<template>
  <footer>
    <div class="max-w-6xl mx-auto px-4 sm:px-6">

      <!-- Top area: Blocks -->
      <div class="grid sm:grid-cols-12 gap-10 py-8 md:py-12"
        :class="props.border ? 'border-t [border-image:linear-gradient(to_right,transparent,var(--color-slate-200),transparent)1]' : ''">

        <!-- 1st block -->
        <div class="sm:col-span-12 lg:col-span-4">
          <div>
            <!-- Logo -->
            <Link class="inline-flex" href="/" aria-label="PetriLog">
            <img src="/images/icons/logo-152.png" alt="logo" class="w-32" />
            </Link>
          </div>
          <div class="text-sm text-gray-600">
            &copy; PetriLog.com - All rights reserved.
          </div>
        </div>

        <!-- 2nd block -->
        <div class="sm:col-span-6 md:col-span-3 lg:col-span-2 space-y-2">
          <h3 class="text-sm font-medium">Seiten</h3>
          <ul class="text-sm space-y-2">
            <li>
              <Link class="text-gray-600 hover:text-gray-900 transition" href="/pricing">Preis</Link>
            </li>
            <li>
              <Link class="text-gray-600 hover:text-gray-900 transition" href="/contact">Kontakt</Link>
            </li>
          </ul>
        </div>

        <div class="sm:col-span-6 md:col-span-3 lg:col-span-2 space-y-2">
          <h3 class="text-sm font-medium">Rechtliches</h3>
          <ul class="text-sm space-y-2">
            <li>
              <Link class="text-gray-600 hover:text-gray-900 transition" href="/impressum">Impressum</Link>
            </li>
            <li>
              <Link class="text-gray-600 hover:text-gray-900 transition" href="/terms-of-service">Nutzungsbedingungen
              </Link>
            </li>
            <li>
              <Link class="text-gray-600 hover:text-gray-900 transition" href="/privacy-policy">
              Datenschutzerklärung</Link>
            </li>
          </ul>
        </div>

      </div>

    </div>

    <!-- Big text -->
    <div class="hidden md:block relative -mt-32 h-60 w-full -z-10" aria-hidden="true">
      <div
        class="absolute left-1/2 pointer-events-none -z-10 -translate-x-1/2 text-[348px] leading-none text-center font-bold before:content-['PetriLog'] before:bg-linear-to-b before:from-gray-200 before:to-gray-100/30 before:to-80% before:text-transparent before:bg-clip-text after:content-['PetriLog'] after:absolute after:inset-0 after:bg-gray-300/70 after:text-transparent after:bg-clip-text after:[text-shadow:0_1px_0_white] after:mix-blend-darken">
      </div>
      <!-- Glow -->
      <div class="absolute bottom-0 left-1/2 -translate-x-1/2 translate-y-2/3" aria-hidden="true">
        <div class="w-56 h-56 rounded-full border-[20px] border-blue-700 blur-[80px]"></div>
      </div>
    </div>
  </footer>
</template>
