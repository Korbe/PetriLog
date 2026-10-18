<template>
  <div class="flex h-full flex-col">
    <header class="flex items-start justify-between gap-3">
      <div>
        <p class="ui-kicker">Dieser Monat</p>
        <h2 class="mt-1 text-base font-semibold">Fänge pro Tag</h2>
      </div>
      <span class="ui-badge ui-badge-accent">{{ data.total }} {{ data.total === 1 ? 'Fang' : 'Fänge' }}</span>
    </header>
    <div class="relative mt-4 h-44 grow">
      <canvas ref="canvas"></canvas>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import { Chart, BarController, BarElement, LinearScale, TimeScale, Tooltip } from 'chart.js'
import 'chartjs-adapter-moment'
import { useAppDark } from '@/composables/useTheme'
import { appChartTheme } from '@/services/ChartjsConfig'

Chart.register(BarController, BarElement, LinearScale, TimeScale, Tooltip)

const props = defineProps({
  data: {
    type: Object,
    required: true,
    validator(value) {
      return 'total' in value && 'timestamps' in value && 'scores' in value
    }
  },
})

const canvas = ref(null)
let chart = null
const darkMode = useAppDark()

const initChart = () => {
  const theme = appChartTheme()

  chart = new Chart(canvas.value, {
    type: 'bar',
    data: {
      labels: props.data.timestamps.map(ts => new Date(ts)),
      datasets: [
        {
          label: 'Fänge',
          data: props.data.scores,
          backgroundColor: theme.accent,
          hoverBackgroundColor: theme.float,
          borderRadius: 4,
          barPercentage: 0.9,
          categoryPercentage: 1,
          maxBarThickness: 14,
        },
      ],
    },
    options: {
      layout: { padding: { top: 4 } },
      scales: {
        y: {
          beginAtZero: true,
          border: { display: false },
          grid: { color: theme.grid },
          ticks: { color: theme.text, precision: 0, maxTicksLimit: 4 },
        },
        x: {
          type: 'time',
          time: {
            parser: 'YYYY-MM-DD',
            unit: 'day',
            tooltipFormat: 'DD.MM.YYYY',
            displayFormats: { day: 'DD.' },
          },
          border: { display: false },
          grid: { display: false },
          ticks: { color: theme.text, autoSkip: true, maxTicksLimit: 8, maxRotation: 0 },
        },
      },
      plugins: {
        tooltip: {
          ...theme.tooltip,
          callbacks: {
            label: (context) => `${context.parsed.y} ${context.parsed.y === 1 ? 'Fang' : 'Fänge'}`,
          },
        },
        legend: { display: false },
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
watch(() => props.data, rebuild, { deep: true })
// wait a frame so the html.dark class (and with it the CSS tokens) is already switched
watch(darkMode, () => requestAnimationFrame(rebuild))
</script>
