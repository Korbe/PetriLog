<template>
  <div>
    <input type="checkbox" name="light-switch" id="light-switch" v-model="isDark" class="light-switch sr-only" />
    <label class="ui-icon-btn cursor-pointer" for="light-switch" :title="isDark ? 'Hellen Modus aktivieren' : 'Dunklen Modus aktivieren'">
      <SunIcon class="size-5 hidden dark:block" />
      <MoonIcon class="size-5 dark:hidden" />
      <span class="sr-only">Zwischen hellem und dunklem Modus wechseln</span>
    </label>
  </div>
</template>

<script setup>
import { useDark } from "@vueuse/core";
import { SunIcon, MoonIcon } from '@heroicons/vue/24/outline'

const isDark = useDark({
  selector: 'html',
})
</script>
