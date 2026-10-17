<script setup>
// Link for the public site. "/#section" links scroll smoothly when the section is on the
// current page instead of triggering an Inertia visit; from other pages they navigate to
// the home page and Inertia jumps to the anchor after loading.
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    href: { type: String, required: true },
});

const emit = defineEmits(['navigate']);

const hash = computed(() => props.href.match(/^\/?#(.+)$/)?.[1] ?? null);

function onHashClick(event) {
    // keep new-tab / modifier clicks native
    if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

    event.preventDefault();
    emit('navigate');

    const target = document.getElementById(hash.value);
    if (!target) {
        router.visit(props.href);
        return;
    }

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    // next frame: lets a closing mobile menu release its scroll lock first
    requestAnimationFrame(() => target.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' }));
    history.replaceState(history.state, '', `#${hash.value}`);
}
</script>

<template>
    <a v-if="hash" :href="href" @click="onHashClick">
        <slot />
    </a>
    <Link v-else :href="href" @click="emit('navigate')">
        <slot />
    </Link>
</template>
