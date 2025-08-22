<template>
    <PageWrapper title="Fische" :backTo="route('dashboard')">

        <template v-slot:actions>
            <VInput placeholder="Suche" v-model="search" />
        </template>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-5">
            <div v-for="(fish, name) in filteredFish" :key="name" class="pb-4 ">

                <Link :href="route('fish.fish', name)">
                    <div class="flex flex-col items-center pb-10">
                        <img :src="fish.link" :alt="name" class="w-72 h-44 object-cover rounded-lg" />
                        <h1 class="text-xl font-bold pt-3">{{ name }}</h1>
                    </div>
                </Link>

            </div>
        </div>

    </PageWrapper>
</template>
<script setup>
import VInput from '@/components/VInput.vue';
import PageWrapper from '@/Layouts/PageWrapper.vue';
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
    fish: Object
});

const search = ref("")

const filteredFish = computed(() => {
  if (!search.value) return props.fish;

  // wir filtern keys die den Suchstring enthalten
  return Object.fromEntries(
    Object.entries(props.fish).filter(([name]) =>
      name.toLowerCase().includes(search.value.toLowerCase())
    )
  )
})

onMounted(() => {
  sessionStorage.setItem('lastOrigin', window.location.href);
});



</script>