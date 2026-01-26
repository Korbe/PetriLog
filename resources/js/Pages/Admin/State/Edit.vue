<template>
    <PageWrapper title="Bundesland bearbeiten" backTo="/admin/state">
        <div class="w-full bg-white flex flex-col justify-center dark:bg-gray-800 shadow-xs rounded-lg p-5">
            <div class="p-6">
                <form @submit.prevent="submit">

                    <VFileInput v-if="canUploadImage" type="file" v-model="form.photo" accept="image/*"
                        :multiple="false" class="block w-full focus:ring-brand-primary focus:border-brand-primary" />

                    <div v-if="errors?.['photos.0']" class="text-xs mt-1 text-red-500">{{ errors['photos.0'] }}</div>

                    <ImagePreview class="mt-5" :modelValue="form.photo" :existing="props.state.media"
                        @remove="removeImage" />

                    <VInput label="Name" v-model="form.name" :error="form.errors.name" />
                    <VEditor class="my-4" label="Beschreibung" v-model="form.desc" :error="form.errors.desc" />

                    <div class="flex items-center justify-between">
                        <VButton type="submit" :disabled="form.processing">
                            Speichern
                        </VButton>

                        <!-- <VButton type="button" variant="danger" @click="destroy">
                            Löschen
                        </VButton> -->
                    </div>

                </form>
            </div>
        </div>
    </PageWrapper>
</template>

<script setup>
import VButton from '@/components/VButton.vue';
import VInput from '@/components/VInput.vue';
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
import { useForm, router } from '@inertiajs/vue3';
import VFileInput from '@/components/VFileInput.vue';
import ImagePreview from '@/components/ImagePreview.vue';
import { computed } from 'vue'
import VEditor from '@/components/VEditor.vue';

const props = defineProps({
    state: Object,
});

// Form initialisieren mit bestehenden Daten
const form = useForm({
    name: props.state.name,
    desc: props.state.desc,
    photo: null,
});

const canUploadImage = computed(() => {
    // Zeige FileInput nur, wenn:
    // 1. Noch kein File im Form gesetzt ist
    // 2. Keine bestehende Media vorhanden
    return !form.photo && (!props.state.media || props.state.media.length === 0)
})

function submit() {
    form.post(route('admin.state.update', props.state.id));
}

// function destroy() {
//   if (confirm(`Möchtest du das Bundesland "${props.state.name}" wirklich löschen?`)) {
//     Inertia.delete(route('admin.state.destroy', props.state.id));
//   }
// }

const removeImage = (item) => {
    if (item.file instanceof File) {
        form.photo = null;
        return
    }

    if (item.readonly) {
        if (!confirm('Bild wirklich löschen?')) return

        router.delete(route('admin.state.photo.delete', item.id), {
            onSuccess: () => {
                form.media = null
            },
        })
    }
}
</script>