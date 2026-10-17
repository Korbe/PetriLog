<script setup>
import Accordion from './Accordion.vue';

// FAQs come from the controller, which also renders them as FAQPage structured data.
defineProps({
    faqs: { type: Array, required: true },
});
</script>

<template>
    <section class="max-w-6xl mx-auto px-4 sm:px-6 pb-20 md:pb-28">
        <div class="grid gap-10 border-t border-(--ink)/10 pt-16 md:pt-24 lg:grid-cols-12">
            <div class="ph-reveal lg:col-span-4">
                <div class="lg:sticky lg:top-28">
                <p class="font-log text-xs uppercase tracking-[0.22em] text-(--ink)/55">FAQ</p>
                <h2 class="font-display mt-4 text-5xl leading-[1] md:text-6xl">Häufig gestellte <em
                        class="text-primary-700">Fragen.</em></h2>
                <p class="mt-6 text-(--ink)/65">
                    Noch etwas offen? <Link href="/contact" class="font-medium text-(--ink) underline underline-offset-4">
                        Schreib uns</Link>.
                </p>
                </div>
            </div>

            <div class="ph-reveal lg:col-span-8" style="transition-delay: 100ms">
                <Accordion v-for="(faq, index) in faqs" :key="index" :id="`faq-${index}`" :title="faq.question"
                    :active="index === 0">
                    {{ faq.answer }}
                </Accordion>
            </div>
        </div>
    </section>
</template>
