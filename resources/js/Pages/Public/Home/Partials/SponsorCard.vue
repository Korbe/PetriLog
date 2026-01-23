<script setup>
defineProps({ company: Object })
</script>

<template>
  <component :is="company.link ? 'a' : 'div'"
             :href="company.link"
             target="_blank"
             rel="noopener noreferrer"
             class="block">
    <article class="w-[18rem] p-4 bg-white/80 dark:bg-gray-800 rounded-xl shadow-sm flex flex-col items-center">
      <img :src="company.img" :alt="company.name" class="w-32 h-32 object-contain mb-3" />
      <h3 class="text-center text-sm font-semibold text-gray-800 dark:text-gray-100">{{ company.name }}</h3>
    </article>
  </component>
</template>