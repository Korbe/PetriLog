<template>
    <div
        class="relative my-5 overflow-hidden rounded-[1.25rem] bg-linear-to-br from-[#06212b] via-[#0a3140] to-[#104456] p-6 text-[#f3eee3] shadow-(--app-shadow)">
        <div class="absolute -right-12 -top-16 size-48 rounded-full bg-primary-500/30 blur-3xl" aria-hidden="true"></div>
        <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="font-log text-[11px] uppercase tracking-[0.2em] text-[#ff8a5c]">Gratis-Limit erreicht</p>
                <p class="mt-2 text-lg font-semibold">Du hast deine 5 kostenlosen Fänge eingetragen.</p>
                <p class="mt-1 text-sm text-white/70">Mit PetriLog Plus trägst du unbegrenzt viele Fänge ein – für 49,99 € im
                    Jahr.</p>
            </div>
            <Link href="/billing-portal"
                class="inline-flex shrink-0 items-center justify-center gap-2 rounded-full bg-white px-5 py-3 text-sm font-semibold text-[#0f1d22] transition hover:bg-[#f3eee3]">
                Jetzt Abo abschließen
            </Link>
        </div>
    </div>
</template>
<script setup></script>
