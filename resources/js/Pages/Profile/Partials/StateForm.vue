<script setup>
import { useForm } from '@inertiajs/vue3'
import ActionMessage from '@/JetstreamComponents/ActionMessage.vue'
import PrimaryButton from '@/JetstreamComponents/PrimaryButton.vue'
import VCombobox from '@/components/VCombobox.vue';

const props = defineProps({
    states: {
        type: Array,
        required: true,
    },
    user: {
        type: Object,
        required: true,
    },
})

const form = useForm({
    state: props.user.state ? props.user.state : null,
    state_id: props.user.state ? props.user.state.id : null,
})

const submit = () => {
    form.state_id = form.state.id;
    form.patch(route('app.profile.state.update'), {
        preserveScroll: true,
    })
}
</script>

<template>
    <div class="md:grid md:grid-cols-2 md:gap-6">
        <div class="ui-card overflow-hidden md:col-span-2">
            <form @submit.prevent="submit">
                <!-- CONTENT -->
                <div class="p-5 sm:p-7">

                    <!-- HEADER -->
                    <h1 class="font-display text-3xl text-(--app-ink)">
                        Bundesland
                    </h1>
                    <p class="mt-1 mb-6 text-sm text-(--app-muted)">
                        Verwalte deine Bundesland-Einstellung.
                    </p>

                    <!-- FORM -->
                    <div class="mt-4">
                        <label class="block text-sm font-medium mb-1">
                            Bundesland
                        </label>

                        <VCombobox v-model="form.state" :options="states" label="name" track-by="id"
                            placeholder="Bundesland auswählen" search-placeholder="Bundesland suchen …" aria-label="Bundesland" />

                        <p v-if="form.errors.state_id" class="text-red-500 text-sm mt-1">
                            {{ form.errors.state_id }}
                        </p>
                    </div>
                </div>

                <!-- ACTIONS -->
                <div class="flex items-center justify-end gap-3 border-t border-(--app-border) bg-(--app-surface-2) px-5 py-4 sm:px-7">
                    <ActionMessage :on="form.recentlySuccessful">
                        Gespeichert.
                    </ActionMessage>

                    <PrimaryButton type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Speichern
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </div>
</template>
