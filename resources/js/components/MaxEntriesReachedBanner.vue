<template>
    <div
        class="bg-red-50 dark:bg-red-900 border-l-4 border-red-400 dark:border-red-700 p-4 rounded-lg shadow-md text-center mb-5">
        <p class="text-red-800 dark:text-red-100 text-sm font-medium">
            Du hast das Gratis-Limit von 5 Einträgen erreicht. <br />
            Mit einem Abo kannst du alle Funktionen ohne Limit nutzen!
        </p>
        <div class="mt-4">
            <Link href="/billing-portal"
                class="inline-block bg-red-600 hover:bg-red-700 text-white font-semibold px-5 py-2 rounded-lg shadow-md transition-colors duration-200">
            Jetzt Abo abschließen
            </Link>
        </div>
    </div>
</template>
<script setup></script>