<script setup>
const props = defineProps({
    heading: String,
    buttonText: String,
    buttonLink: String
})
</script>

<template>
    <section class="relative">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-full -z-10" aria-hidden="true">
            <div
                class="w-80 h-80 rounded-full bg-linear-to-tr from-blue-500 to-gray-900 opacity-40 blur-[160px] will-change-[filter]">
            </div>
        </div>
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div
                class="text-center border-t [border-image:linear-gradient(to_right,transparent,--theme(--color-blue-500/.25),transparent)1]">
                <div class="py-12 md:py-20">
                    <h2 class="text-3xl md:text-4xl font-bold mb-6 md:mb-12">{{ props.heading }}</h2>
                    <div class="max-w-xs mx-auto sm:max-w-none sm:flex sm:justify-center">
                        <a class="btn text-white bg-linear-to-t from-blue-600 to-blue-500 bg-[length:100%_100%] hover:bg-[length:100%_150%] bg-[bottom] shadow-sm w-full mb-4 sm:w-auto sm:mb-0 group"
                            :href="props.buttonLink">
                            <span class="relative inline-flex items-center">
                                {{ props.buttonText }} <span
                                    class="tracking-normal text-blue-300 group-hover:translate-x-0.5 transition-transform ml-1"></span>
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>