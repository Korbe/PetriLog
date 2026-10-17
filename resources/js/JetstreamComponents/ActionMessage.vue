<script setup>
defineProps({
    on: Boolean,
});
</script>

<template>
    <div>
        <transition leave-active-class="transition ease-in duration-1000" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-show="on" class="inline-flex items-center gap-1.5 text-sm font-medium text-(--app-success)">
                <slot />
            </div>
        </transition>
    </div>
</template>
