<template>
    <PageWrapper title="Fänge" :backTo="route('dashboard')">
        <template v-slot:actions>
            <!-- <DropdownFilter :options="filters" @filtersChanged="handleFiltersChanged" /> -->
            <VDateRangePicker align="right" v-model="dateRange" />
            <ResetButton @click="resetDateRange" />
            <VButton :href="route('catched.create')">Eintragen</VButton>
        </template>

        <div class="md:w-1/2 m-auto">

            <div v-if="Object.keys(groupedCatcheds).length === 0"
                class="text-center bg-white rounded-lg shadow-lg p-5 flex flex-col my-2">
                <p class="pb-5">Für diesen Zeitraum wurden keine Einträge gefunden</p>
                <VButton :href="route('catched.create')">Jetzt eintragen</VButton>
            </div>

            <div v-for="(items, date) in groupedCatcheds" :key="date" class="py-2">
                <div>
                    <button @click="toggleOpen(date)"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-5 flex w-full items-start justify-between text-left text-brand-headline sm:text-3xl dark:text-brand-headline-dark">
                        <span class="cursor-pointer text-base/7 font-semibold">{{ date }} ({{ items.length
                            }})</span>
                        <span class="ml-6 flex h-7 items-center">
                            <PlusIcon v-if="!isOpen(date)" class="cursor-pointer size-6" />
                            <MinusIcon v-else class="cursor-pointer size-6" />
                        </span>
                    </button>
                </div>

                <transition name="fade">
                    <div v-if="isOpen(date)" class="mt-2 pl-2 pr-2">
                        <div v-for="catched in items" :key="catched.id">
                            <Link class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-5 flex justify-between my-2"
                                :href="route('catched.show', catched.id)">
                            <span>
                                <span class="text-primary-500"><b>{{ catched.name }}</b></span> - {{ catched.waters
                                }}
                            </span>
                            <ChevronRightIcon class="h-6" />
                            </Link>
                        </div>
                    </div>
                </transition>
            </div>
        </div>
    </PageWrapper>
</template>

<script setup lang="ts">
import { MinusIcon, PlusIcon } from '@heroicons/vue/24/outline'
import { router } from '@inertiajs/vue3';
import VButton from "@/components/VButton.vue";
import { ChevronRightIcon } from '@heroicons/vue/24/solid';
import PageWrapper from '@/Layouts/PageWrapper.vue';
import { onMounted, ref, watch } from 'vue';
import DropdownFilter from '@/components/DropdownFilter.vue';
import VDateRangePicker from '@/components/VDateRangePicker.vue';
import ResetButton from '@/components/pagination/ResetButton.vue';

interface CatchedData {
    id: number;
    user_id: number;
    name: string;
    length: number;
    weight: number;
    depth: number;
    temperature: number;
    waters: string;
    date: string;
    latitude: number;
    longitude: number;
    address: string;
    remark: string;
    created_at: string;
    updated_at: string;
}

interface Props {
    groupedCatcheds: Record<string, CatchedData[]>;
    dateRange: {
        startDate: string;
        endDate: string;
    };
}

const props = defineProps<Props>();

let isUpdating = false;

const originalDateRange = ref({
    startDate: new Date(props.dateRange?.startDate),
    endDate: new Date(props.dateRange?.endDate)
});


const dateRange = ref({
    startDate: new Date(props.dateRange?.startDate || new Date().setDate(new Date().getDate() - 7)),
    endDate: new Date(props.dateRange?.endDate || new Date())
});

const filters = ref([
    { label: 'Beschreibung', value: 'onlyWithDescription', selected: false },
])

watch(dateRange, (newRange) => {
    handleDateRangeUpdate(newRange);
}, { deep: true });



const getFilterParams = () => {
    return filters.value
        .filter(filter => filter.selected)
        .map(filter => ({ [filter.value]: 1 }))
        .reduce((acc, curr) => ({ ...acc, ...curr }), {});
};


const handleDateRangeUpdate = (newRange) => {
    // Check if both dates are present and valid
    if (!newRange?.startDate || !newRange?.endDate) {
        dateRange.value = newRange; // Update local state but don't trigger search
        return;
    }

    // Validate dates are actual Date objects
    if (!(newRange.startDate instanceof Date) || !(newRange.endDate instanceof Date))
        return;

    // Check if dates are valid (not NaN)
    if (isNaN(newRange.startDate.getTime()) || isNaN(newRange.endDate.getTime()))
        return;

    dateRange.value = newRange;

    search();
};

const handleFiltersChanged = (newFilters) => {
    filters.value = newFilters;

    search();
};

const resetDateRange = () => {
    dateRange.value = originalDateRange.value;
    search();
}

const search = () => {
    if (isUpdating) return;

    isUpdating = true;

    const filterParams = getFilterParams();

    const request = {
        startDate: dateRange.value.startDate.toLocaleString(),
        endDate: dateRange.value.endDate.toLocaleString(),
        ...filterParams
    };

    router.get(
        route('catched.index'), request,
        {
            preserveState: true,
            preserveScroll: true,
            onFinish: () => {
                setTimeout(() => {
                    isUpdating = false;
                }, 0);
            }
        }
    );
};

// State für geöffnete Accordion-Keys (z. B. Datum)
const openKeys = ref<string[]>([]);

const isOpen = (date: string) => openKeys.value.includes(date);

// Merke geöffnete beim Öffnen
const toggleOpen = (key: string) => {
    if (openKeys.value.includes(key))
        openKeys.value = openKeys.value.filter(k => k !== key);
    else
        openKeys.value.push(key);

    sessionStorage.setItem('catched-open', JSON.stringify(openKeys.value));
};

// Beim Mounten gespeicherten Zustand laden
onMounted(() => {
    sessionStorage.setItem('lastOrigin', window.location.href);

    const stored = sessionStorage.getItem('catched-open');
    if (stored) {
        try {
            openKeys.value = JSON.parse(stored);
        } catch (_) {
            openKeys.value = [];
        }
    }
});
</script>