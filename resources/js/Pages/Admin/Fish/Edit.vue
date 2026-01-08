<template>
    <PageWrapper title="Fisch bearbeiten" backTo="/admin/fish">
        <div class="w-full bg-white flex flex-col justify-center dark:bg-gray-800 shadow-xs rounded-lg p-5">
            <div class="p-6">
                <form @submit.prevent="submit">

                    <VInput label="Name" v-model="form.name" :error="form.errors.name" />
                    <VTextarea class="my-4" label="Beschreibung" v-model="form.desc" :error="form.errors.desc" />

                    <div class="flex items-center justify-between">
                        <VButton type="submit" :disabled="form.processing">
                            Speichern
                        </VButton>

                        <VButton type="button" variant="danger" @click="destroy">
                            Löschen
                        </VButton>
                    </div>

                </form>
            </div>
        </div>
    </PageWrapper>
</template>

<script setup>
import VButton from '@/components/VButton.vue';
import VInput from '@/components/VInput.vue';
import VTextarea from '@/components/VTextarea.vue';
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
import { useForm } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';

const props = defineProps({
    fish: Object,
});

// Form initialisieren mit bestehenden Daten
const form = useForm({
    name: props.fish.name,
    desc: props.fish.desc,
});

function submit() {
    form.put(route('admin.fish.update', props.fish.id));
}

function destroy() {
  if (confirm(`Möchtest du den Fisch "${props.fish.name}" wirklich löschen?`)) {
    Inertia.delete(route('admin.fish.destroy', props.fish.id));
  }
}
</script>