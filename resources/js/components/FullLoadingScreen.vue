<template>
    <div class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-(--app-bg)/90 backdrop-blur-sm"
        role="status" aria-live="polite">
        <div class="relative size-20">
            <div class="absolute inset-0 rounded-full border-4 border-(--app-border)"></div>
            <div class="absolute inset-0 animate-spin rounded-full border-4 border-primary-500 border-t-transparent"></div>
            <img src="/logo-short.png" alt="" class="absolute inset-0 m-auto size-8" />
        </div>
        <p class="font-display mt-6 text-3xl text-(--app-ink)">Wird gespeichert …</p>
        <p class="ui-muted mt-1 text-sm">Fotos werden hochgeladen, das kann einen Moment dauern.</p>
    </div>
</template>
