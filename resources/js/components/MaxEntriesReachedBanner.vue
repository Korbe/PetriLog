<template>
    <div class="bg-red-100 text-center border border-red-300 text-red-900 
              dark:bg-red-900 dark:border-red-700 dark:text-red-100 
              px-4 py-3 rounded-lg shadow-md">
        <p class="font-semibold">⛔ Deine Testphase ist leider abgelaufen!</p>
        <p class="text-sm mt-1">
            Wenn dir PetriLog gefällt, entscheide dich bitte für ein <strong>Jahresabo</strong>.
        </p>
        <div class="mt-3">
            <Link href="/billing" class="inline-block bg-primary-500 hover:bg-primary-600 
                text-white font-medium px-4 py-2 rounded-lg shadow">
            Jetzt Abo abschließen
            </Link>
        </div>
    </div>
</template>
<script setup></script>