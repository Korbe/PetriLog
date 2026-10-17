<template>
  <!-- Inertia Link, falls href gesetzt (external: normaler Link, z. B. für Downloads) -->
  <component :is="external ? 'a' : Link" v-if="href" :href="href" class="btn" :class="buttonClasses" :aria-disabled="disabled || undefined">
    <svg v-if="loading" class="animate-spin fill-current shrink-0" width="16" height="16" viewBox="0 0 16 16">
      <path
        d="M8 16a7.928 7.928 0 01-3.428-.77l.857-1.807A6.006 6.006 0 0014 8c0-3.309-2.691-6-6-6a6.006 6.006 0 00-5.422 8.572l-1.806.859A7.929 7.929 0 010 8c0-4.411 3.589-8 8-8s8 3.589 8 8-3.589 8-8 8z" />
    </svg>
    <component v-if="icon" :is="iconComponent" class="w-4 h-4 shrink-0" />
    <slot />
  </component>

  <!-- Standard Button -->
  <button v-else :type="type" class="btn" :class="buttonClasses" :disabled="disabled" @click="emitClick">
    <svg v-if="loading" class="animate-spin fill-current shrink-0" width="16" height="16" viewBox="0 0 16 16">
      <path
        d="M8 16a7.928 7.928 0 01-3.428-.77l.857-1.807A6.006 6.006 0 0014 8c0-3.309-2.691-6-6-6a6.006 6.006 0 00-5.422 8.572l-1.806.859A7.929 7.929 0 010 8c0-4.411 3.589-8 8-8s8 3.589 8 8-3.589 8-8 8z" />
    </svg>
    <component v-if="icon" :is="iconComponent" class="w-4 h-4 shrink-0" />
    <slot />
  </button>
</template>

<script setup>
import { computed } from 'vue';
import * as HeroIcons from '@heroicons/vue/24/solid';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  href: { type: String, default: "" },
  external: { type: Boolean, default: false },
  type: { type: String, default: "button" },
  // primary | secondary | danger | ghost
  variant: { type: String, default: "primary" },
  // sm | md | lg
  size: { type: String, default: "md" },
  disabled: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
  icon: { type: String, default: "" },
});

const emit = defineEmits(['click'])

const emitClick = () => {
  emit('click');
}

const sizes = {
  sm: 'gap-1.5 px-3 py-1.5 text-xs',
  md: 'gap-2 px-4 py-2.5 text-sm',
  lg: 'gap-2 px-6 py-3.5 text-base',
};

const variants = {
  primary: 'bg-primary-500 text-white shadow-sm shadow-primary-500/25 hover:bg-primary-600 active:bg-primary-700 focus-visible:ring-primary-500/40',
  secondary: 'bg-(--app-surface) text-(--app-ink) border-(--app-border-strong)! hover:bg-(--app-hover) hover:border-(--app-subtle)! focus-visible:ring-(--app-accent-soft)',
  danger: 'bg-red-600 text-white shadow-sm shadow-red-600/25 hover:bg-red-700 active:bg-red-800 focus-visible:ring-red-500/40',
  ghost: 'text-(--app-muted) shadow-none! hover:bg-(--app-hover) hover:text-(--app-ink) focus-visible:ring-(--app-accent-soft)',
};

const buttonClasses = computed(() => [
  'cursor-pointer rounded-full font-semibold leading-5 transition-all duration-150 focus:outline-none focus-visible:ring-4 active:scale-[0.97]',
  sizes[props.size] ?? sizes.md,
  variants[props.variant] ?? variants.primary,
  props.disabled ? 'pointer-events-none opacity-50 shadow-none' : '',
]);

const iconComponent = computed(() => {
  return HeroIcons[props.icon] || null;
});
</script>
