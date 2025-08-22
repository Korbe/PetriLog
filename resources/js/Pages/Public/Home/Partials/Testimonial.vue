<script setup>
const props = defineProps(['testimonial'])
</script>

<template>
  <article
    class="relative flex flex-col rounded-2xl h-full p-5 shadow-lg md:odd:-rotate-1 md:even:rotate-1 bg-white/70 shadow-black/[0.03] before:absolute before:inset-0 before:rounded-[inherit] before:border before:border-transparent before:[background:linear-gradient(var(--color-gray-100),var(--color-gray-200))_border-box] before:[mask:linear-gradient(white_0_0)_padding-box,_linear-gradient(white_0_0)] before:[mask-composite:exclude_!important] before:pointer-events-none">
    <header class="flex items-center gap-3 mb-4">
      <img class="shrink-0 rounded-full" :src="props.testimonial.img" width="44" height="44"
        :alt="props.testimonial.name" />
      <div>
        <div class="font-bold">{{ props.testimonial.name }}</div>
      </div>
    </header>
    <div v-if="props.testimonial.rating" class="inline-flex mb-3">
      <span class="sr-only">Rating is 5 out of 5</span>
      <div class="relative">
        <svg class="fill-gray-200" xmlns="http://www.w3.org/2000/svg" width="114" height="18">
          <path
            d="m105 .44 2.782 5.636 6.218.903-4.5 4.386 1.062 6.195L105 14.635l-5.562 2.925 1.062-6.195L96 6.98l6.218-.903L105 .44ZM81 .44l2.782 5.636L90 6.979l-4.5 4.386 1.062 6.195L81 14.635l-5.562 2.925 1.062-6.195L72 6.98l6.218-.903L81 .44ZM57 .44l2.782 5.636L66 6.979l-4.5 4.386 1.062 6.195L57 14.635l-5.562 2.925 1.062-6.195L48 6.98l6.218-.903L57 .44ZM33 .44l2.782 5.636L42 6.979l-4.5 4.386 1.062 6.195L33 14.635l-5.562 2.925 1.062-6.195L24 6.98l6.218-.903L33 .44ZM9 .44l2.782 5.636L18 6.979l-4.5 4.386 1.062 6.195L9 14.635 3.438 17.56 4.5 11.365 0 6.98l6.218-.903L9 .44Z" />
        </svg>
        <div class="absolute inset-0 overflow-hidden" style="width:100%">
          <svg class="fill-amber-400" xmlns="http://www.w3.org/2000/svg" width="114" height="18">
            <path
              d="m105 .44 2.782 5.636 6.218.903-4.5 4.386 1.062 6.195L105 14.635l-5.562 2.925 1.062-6.195L96 6.98l6.218-.903L105 .44ZM81 .44l2.782 5.636L90 6.979l-4.5 4.386 1.062 6.195L81 14.635l-5.562 2.925 1.062-6.195L72 6.98l6.218-.903L81 .44ZM57 .44l2.782 5.636L66 6.979l-4.5 4.386 1.062 6.195L57 14.635l-5.562 2.925 1.062-6.195L48 6.98l6.218-.903L57 .44ZM33 .44l2.782 5.636L42 6.979l-4.5 4.386 1.062 6.195L33 14.635l-5.562 2.925 1.062-6.195L24 6.98l6.218-.903L33 .44ZM9 .44l2.782 5.636L18 6.979l-4.5 4.386 1.062 6.195L9 14.635 3.438 17.56 4.5 11.365 0 6.98l6.218-.903L9 .44Z" />
          </svg>
        </div>
      </div>
    </div>
    <div v-if="props.testimonial.videoThumb" class="grow">
      <p class="font-semibold mb-4">
        <slot />
      </p>
      <a :href="props.testimonial.videoUrl">   
        <img class="w-full" :src="props.testimonial.videoThumb" width="312" height="180" alt="View on YouTuobe" />
      </a>
    </div>
    <div v-else class="grow text-sm text-gray-700">
      <slot />
    </div>
  </article>
</template>
