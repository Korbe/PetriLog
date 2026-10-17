<template>
  <div class="relative inline-flex">
    <button ref="trigger"
      class="group flex items-center gap-2 rounded-full py-1 pl-1 pr-2 transition hover:bg-(--app-hover)"
      aria-haspopup="true" @click.prevent="dropdownOpen = !dropdownOpen" :aria-expanded="dropdownOpen">
      <span
        class="flex size-8 shrink-0 items-center justify-center rounded-full bg-linear-to-br from-primary-400 to-primary-700 text-xs font-semibold text-white">
        {{ initials }}
      </span>
      <span class="hidden max-w-[10rem] truncate text-sm font-medium md:block">{{ user.name }}</span>
      <ChevronDownIcon class="hidden size-4 text-(--app-subtle) transition md:block"
        :class="dropdownOpen ? 'rotate-180' : ''" />
    </button>

    <transition enter-active-class="transition ease-out duration-200 transform"
      enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition ease-out duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-show="dropdownOpen"
        class="absolute top-full z-30 mt-2 w-64 origin-top-right overflow-hidden rounded-2xl border border-(--app-border) bg-(--app-surface) shadow-(--app-shadow-lg)"
        :class="align === 'right' ? 'right-0' : 'left-0'">
        <div ref="dropdown" @focusin="dropdownOpen = true" @focusout="dropdownOpen = false">
          <div class="border-b border-(--app-border) px-4 py-3.5">
            <p class="truncate text-sm font-semibold">{{ user.name }}</p>
            <p class="truncate text-xs text-(--app-muted)">{{ user.email }}</p>
            <span class="ui-badge mt-2" :class="user.subscribed ? 'ui-badge-float' : ''">
              {{ user.subscribed ? 'PetriLog Plus' : 'Gratis-Plan' }}
            </span>
          </div>
          <ul class="p-1.5 text-sm">
            <li>
              <Link :href="route('app.profile.show')" class="flex items-center gap-3 rounded-xl px-3 py-2 hover:bg-(--app-hover)"
                @click="dropdownOpen = false">
                <UserCircleIcon class="size-5 text-(--app-subtle)" /> Profil
              </Link>
            </li>
            <li>
              <a href="/billing-portal" class="flex items-center gap-3 rounded-xl px-3 py-2 hover:bg-(--app-hover)">
                <StarIcon class="size-5 text-(--app-subtle)" /> Abo verwalten
              </a>
            </li>
            <li>
              <Link href="/ticket" class="flex items-center gap-3 rounded-xl px-3 py-2 hover:bg-(--app-hover)"
                @click="dropdownOpen = false">
                <LifebuoyIcon class="size-5 text-(--app-subtle)" /> Hilfe & Feedback
              </Link>
            </li>
          </ul>
          <div class="border-t border-(--app-border) p-1.5">
            <form @submit.prevent="logout">
              <button type="submit"
                class="flex w-full cursor-pointer items-center gap-3 rounded-xl px-3 py-2 text-sm text-(--app-danger) hover:bg-(--app-danger-soft)"
                @click="dropdownOpen = false">
                <ArrowRightStartOnRectangleIcon class="size-5" /> Abmelden
              </button>
            </form>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import {
  ChevronDownIcon, UserCircleIcon, StarIcon, LifebuoyIcon, ArrowRightStartOnRectangleIcon,
} from '@heroicons/vue/24/outline'

defineProps({
  align: String,
})

const page = usePage()
const user = computed(() => page.props.auth.user)
const initials = computed(() => (user.value?.name ?? '?')
  .split(/\s+/).filter(Boolean).slice(0, 2).map(part => part[0].toUpperCase()).join(''))

const dropdownOpen = ref(false)
const trigger = ref(null)
const dropdown = ref(null)

const logout = () => {
  router.post(route('logout'));
};

// close on click outside
const clickHandler = ({ target }) => {
  if (!dropdownOpen.value || dropdown.value.contains(target) || trigger.value.contains(target)) return
  dropdownOpen.value = false
}

// close if the esc key is pressed
const keyHandler = ({ keyCode }) => {
  if (!dropdownOpen.value || keyCode !== 27) return
  dropdownOpen.value = false
}

onMounted(() => {
  document.addEventListener('click', clickHandler)
  document.addEventListener('keydown', keyHandler)
})

onUnmounted(() => {
  document.removeEventListener('click', clickHandler)
  document.removeEventListener('keydown', keyHandler)
})
</script>
