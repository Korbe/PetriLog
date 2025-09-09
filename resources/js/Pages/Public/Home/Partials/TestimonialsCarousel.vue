<script setup>
import Testimonial from './Testimonial.vue'

const testimonials = [
  {
    img: '/images/wappen/Oberoesterreich.png',
    name: 'Maximilian F., 34 – Oberösterreich',
    content: 'PetriLog hat mein Angeltagebuch revolutioniert! Endlich kann ich jeden Fang genau dokumentieren und die besten Spots speichern.',
  },
  {
    img: '/images/wappen/Kaernten.png',
    name: 'Laura S., 28 – Kärnten',
    content: 'Ich liebe es, meine Erfolge zu teilen und zu sehen, wie viele Fische ich dieses Jahr schon gefangen habe. Einfach genial!',
  },
  {
    img: '/images/wappen/Steiermark.png',
    name: 'Tobias K., 42 – Steiermark',
    content: 'Die App ist super intuitiv. Länge, Gewicht, Tiefe – alles ist auf einen Blick dokumentiert. Meine Angelausflüge sind jetzt viel organisierter.',
  },
  {
    img: '/images/wappen/Burgenland.png',
    name: 'Sophie H., 31 – Burgenland',
    content: 'Drei Bilder pro Fang hochladen und den perfekten Fangmoment festhalten – PetriLog macht Angeln noch spannender!',
  }
]
</script>

<template>
  <section
    class="relative before:absolute before:inset-0 before:h-[120%] before:pointer-events-none before:bg-linear-to-b before:from-gray-100 before:-z-10">
    <div class="pt-12 md:pt-20">
      <div class="max-w-6xl mx-auto px-4 sm:px-6">
        <div class="max-w-3xl mx-auto text-center">
          <h2 class="text-3xl md:text-4xl font-bold">Österreich's Angler lieben PetriLog</h2>
        </div>
      </div>
      <div class="relative flex justify-center max-w-[94rem] mx-auto">
        <div class="absolute bottom-20 -translate-x-36 -z-10" aria-hidden="true">
          <div class="w-80 h-80 rounded-full bg-linear-to-tr from-blue-500 to-gray-900 opacity-30 blur-[160px] will-change-[filter]"></div>
        </div>
        <div class="absolute -bottom-10 -z-10" aria-hidden="true">
          <div class="w-80 h-80 rounded-full bg-blue-500 opacity-40 blur-[160px] will-change-[filter]"></div>
        </div>
        <div class="absolute bottom-0 -z-10" aria-hidden="true">
          <div class="w-56 h-56 rounded-full border-[20px] border-white blur-[20px] will-change-[filter]"></div>
        </div>
        <!-- Row -->
        <div class="w-full inline-flex flex-nowrap [mask-image:_linear-gradient(to_right,transparent_0,_black_10%,_black_90%,transparent_100%)] py-12 md:py-20 group">
          <div class="flex items-start justify-center md:justify-start *:mx-3 animate-[infinite-scroll_60s_linear_infinite] group-hover:[animation-play-state:paused]">
            <!-- Items -->
            <Testimonial v-for="(testimonial, index) in testimonials" :key="index" :testimonial="testimonial" class="w-[22rem] group-hover:rotate-0 transition-transform duration-300">
              {{ testimonial.content }}
            </Testimonial>            
          </div>
          <!-- Duplicated element for infinite scroll -->
          <div class="flex items-start justify-center md:justify-start *:mx-3 animate-[infinite-scroll_60s_linear_infinite] group-hover:[animation-play-state:paused]" aria-hidden="true">
            <!-- Items -->
            <Testimonial v-for="(testimonial, index) in testimonials" :key="index" :testimonial="testimonial" class="w-[22rem] group-hover:rotate-0 transition-transform duration-300">
              {{ testimonial.content }}
            </Testimonial>            
          </div>          
        </div>
      </div>
    </div>
  </section>
</template>