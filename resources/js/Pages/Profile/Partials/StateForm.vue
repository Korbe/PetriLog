<script setup>
import { useForm } from '@inertiajs/vue3'
import ActionMessage from '@/JetstreamComponents/ActionMessage.vue'
import PrimaryButton from '@/JetstreamComponents/PrimaryButton.vue'
import VMultiselect from '@/components/VMultiselect.vue'

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
    form.patch(route('profile.state.update'), {
        preserveScroll: true,
    })
}
</script>

<template>
    <div class="md:grid md:grid-cols-2 md:gap-6">
        <div class="mt-5 md:mt-0 md:col-span-2 rounded-lg bg-white dark:bg-gray-800">
            <form @submit.prevent="submit">
                <!-- CONTENT -->
                <div class="px-4 py-5 sm:p-6">

                    <!-- HEADER -->
                    <h1 class="text-lg font-medium text-gray-800 dark:text-gray-100">
                        Bundesland
                    </h1>
                    <p class="mt-1 mb-5 text-sm text-gray-600 dark:text-gray-300">
                        Verwalte deine Bundesland-Einstellung.
                    </p>

                    <!-- FORM -->
                    <div class="mt-4">
                        <label class="block text-sm font-medium mb-1">
                            Bundesland
                        </label>

                        <VMultiselect v-model="form.state" :options="states" label="name" track-by="id"
                            :multiple="false" placeholder="Bundesland auswählen" :close-on-select="true"
                            :clear-on-select="true" :preserve-search="true" />

                        <p v-if="form.errors.state_id" class="text-red-500 text-sm mt-1">
                            {{ form.errors.state_id }}
                        </p>
                    </div>
                </div>

                <!-- ACTIONS -->
                <div class="rounded-lg flex items-center px-4 py-3 shadow">
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
