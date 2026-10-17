<script setup>
import { ref, computed, onMounted } from 'vue'
import { pwa, installPwa } from '@/pwa'

const DISMISS_KEY = 'petrilog-install-dismissed-until'
const DISMISS_DAYS = 30

const dismissed = ref(true) // stays hidden until we know (avoids a flash on load)
const justInstalled = ref(false)
const showIosHint = ref(false)

const visible = computed(() =>
  justInstalled.value || (!dismissed.value && !pwa.installed && (pwa.canPrompt || pwa.isIos)))

onMounted(() => {
  try {
    dismissed.value = Number(localStorage.getItem(DISMISS_KEY) || 0) > Date.now()
  } catch {
    dismissed.value = false
  }
})

function dismiss() {
  dismissed.value = true
  justInstalled.value = false
  try {
    localStorage.setItem(DISMISS_KEY, String(Date.now() + DISMISS_DAYS * 24 * 60 * 60 * 1000))
  } catch { /* private mode – just hide for this visit */ }
}

async function install() {
  if (pwa.isIos && !pwa.canPrompt) {
    showIosHint.value = !showIosHint.value
    return
  }
  if (await installPwa() === 'accepted') justInstalled.value = true
}
</script>

<template>
  <Transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0 -translate-y-2"
    leave-active-class="transition duration-200 ease-in" leave-to-class="opacity-0">
    <div v-if="visible"
      class="relative overflow-hidden rounded-lg bg-linear-to-r from-[#06212b] via-[#0a3140] to-[#104456] p-5 text-[#f3eee3] shadow-md sm:p-6">
      <div class="absolute -right-10 -top-16 size-56 rounded-full bg-primary-500/30 blur-3xl" aria-hidden="true"></div>

      <button type="button" class="absolute right-3 top-3 rounded-full p-2 text-white/60 transition hover:bg-white/10 hover:text-white"
        aria-label="Hinweis ausblenden" @click="dismiss">
        <svg class="size-4" viewBox="0 0 16 16" fill="none" aria-hidden="true">
          <path d="m4 4 8 8M12 4l-8 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
        </svg>
      </button>

      <div class="relative flex flex-col gap-5 pr-8 sm:flex-row sm:items-center">
        <span class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-white shadow-lg">
          <img src="/logo-short.png" alt="" class="size-9" />
        </span>

        <div class="grow">
          <template v-if="justInstalled">
            <p class="text-lg font-semibold">PetriLog ist installiert 🎣</p>
            <p class="mt-1 text-sm text-white/70">Du findest die App jetzt auf deinem Startbildschirm bzw. in deinen Programmen.</p>
          </template>
          <template v-else>
            <p class="text-lg font-semibold">PetriLog als App herunterladen</p>
            <p class="mt-1 text-sm text-white/70">
              Startet direkt vom Startbildschirm im Vollbild – wie eine echte App, ganz ohne App-Store.
            </p>
            <p v-if="showIosHint" class="mt-3 rounded-lg bg-white/10 px-3 py-2 text-sm">
              Tippe in Safari unten auf
              <svg class="inline size-4 -translate-y-0.5" viewBox="0 0 24 24" fill="none" aria-label="Teilen">
                <path d="M12 3v12m0-12-4 4m4-4 4 4M6 11H5a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-8a1 1 0 0 0-1-1h-1"
                  stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
              <strong>Teilen</strong> und dann auf <strong>„Zum Home-Bildschirm“</strong>.
            </p>
          </template>
        </div>

        <button v-if="!justInstalled" type="button"
          class="inline-flex shrink-0 items-center justify-center gap-2 rounded-full bg-white px-5 py-3 text-sm font-semibold text-[#0f1d22] shadow transition hover:bg-[#f3eee3]"
          @click="install">
          <svg class="size-4" viewBox="0 0 16 16" fill="none" aria-hidden="true">
            <path d="M8 1.5v9m0 0L4.5 7M8 10.5 11.5 7M2 13.5h12" stroke="currentColor" stroke-width="1.8"
              stroke-linecap="round" stroke-linejoin="round" />
          </svg>
          {{ pwa.isIos && !pwa.canPrompt ? 'So geht’s' : 'App herunterladen' }}
        </button>
      </div>
    </div>
  </Transition>
</template>
