<template>
    <div class="flex h-full flex-col">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="ui-kicker">Verlauf · {{ series.byWeek ? 'pro Woche' : 'pro Tag' }}</p>
                <h2 class="mt-1 text-base font-semibold">{{ current.title }}</h2>
            </div>
            <div class="flex gap-1 overflow-x-auto rounded-full bg-(--app-surface-2) p-1" role="tablist" aria-label="Kennzahl">
                <button v-for="metric in metrics" :key="metric.key" type="button" role="tab" :aria-selected="selected === metric.key"
                    class="shrink-0 rounded-full px-3 py-1.5 text-xs font-semibold transition"
                    :class="selected === metric.key ? 'bg-(--app-surface) text-(--app-ink) shadow-(--app-shadow)' : 'text-(--app-muted) hover:text-(--app-ink)'"
                    @click="selected = metric.key">
                    {{ metric.label }}
                </button>
            </div>
        </header>
        <div class="relative mt-4 h-64 grow">
            <canvas ref="canvas" :aria-label="current.title" role="img"></canvas>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import {
    Chart, BarController, BarElement, LineController, LineElement, PointElement, CategoryScale, LinearScale, Tooltip, Filler, Legend,
} from 'chart.js'
import { useAppDark } from '@/composables/useTheme'
import { appChartTheme } from '@/services/ChartjsConfig'

Chart.register(BarController, BarElement, LineController, LineElement, PointElement, CategoryScale, LinearScale, Tooltip, Filler, Legend)

const props = defineProps({
    series: { type: Object, required: true },
})

const metrics = [
    { key: 'traffic', label: 'Traffic', title: 'Seitenaufrufe & Besucher' },
    { key: 'catches', label: 'Fänge', title: 'Neu eingetragene Fänge' },
    { key: 'users', label: 'User', title: 'Neue Registrierungen' },
    { key: 'mails', label: 'Mails', title: 'Verschickte E-Mails' },
]
const selected = ref('traffic')
const current = computed(() => metrics.find(m => m.key === selected.value))

const canvas = ref(null)
let chart = null
const darkMode = useAppDark()

const formatLabel = (date) => {
    const d = new Date(`${date}T12:00:00`)
    const label = d.toLocaleDateString('de-AT', { day: '2-digit', month: '2-digit' })
    return props.series.byWeek ? `KW ab ${label}` : label
}

function datasets(theme) {
    const bar = (label, data, color) => ({ type: 'bar', label, data, backgroundColor: color, borderRadius: 5, maxBarThickness: 22 })

    if (selected.value === 'traffic') {
        return [
            {
                type: 'line', label: 'Seitenaufrufe', data: props.series.views, borderColor: theme.accent, borderWidth: 2,
                backgroundColor: theme.accent + '22', fill: true, tension: 0.35, pointRadius: 0, pointHoverRadius: 4,
            },
            {
                type: 'line', label: 'Besucher', data: props.series.visitors, borderColor: theme.float, borderWidth: 2,
                tension: 0.35, pointRadius: 0, pointHoverRadius: 4,
            },
        ]
    }

    return [bar(current.value.label, props.series[selected.value], selected.value === 'mails' ? theme.float : theme.accent)]
}

function initChart() {
    const theme = appChartTheme()

    chart = new Chart(canvas.value, {
        data: { labels: props.series.labels.map(formatLabel), datasets: datasets(theme) },
        options: {
            scales: {
                y: {
                    beginAtZero: true, border: { display: false }, grid: { color: theme.grid },
                    ticks: { color: theme.text, precision: 0, maxTicksLimit: 5 },
                },
                x: {
                    border: { display: false }, grid: { display: false },
                    ticks: { color: theme.text, maxRotation: 0, autoSkip: true, maxTicksLimit: 8 },
                },
            },
            plugins: {
                legend: {
                    display: selected.value === 'traffic', position: 'bottom', align: 'start',
                    labels: { color: theme.text, boxWidth: 10, boxHeight: 10, usePointStyle: true, pointStyle: 'circle' },
                },
                tooltip: { ...theme.tooltip },
            },
            interaction: { intersect: false, mode: 'index' },
            maintainAspectRatio: false,
            resizeDelay: 200,
        },
    })
}

const rebuild = () => {
    chart?.destroy()
    initChart()
}

onMounted(initChart)
onUnmounted(() => chart?.destroy())
watch([() => props.series, selected], rebuild, { deep: true })
watch(darkMode, () => requestAnimationFrame(rebuild))
</script>
