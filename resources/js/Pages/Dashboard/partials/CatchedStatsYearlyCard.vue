<template>
  <div class="flex h-full flex-col">
    <header class="flex items-start justify-between gap-3">
      <div>
        <p class="ui-kicker">Saison {{ year }}</p>
        <h2 class="mt-1 text-base font-semibold">Fänge pro Monat</h2>
      </div>
      <span class="ui-badge ui-badge-accent">{{ data.total }} {{ data.total === 1 ? 'Fang' : 'Fänge' }}</span>
    </header>
    <div class="relative mt-4 h-56 grow">
      <canvas ref="canvas"></canvas>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import { Chart, BarController, BarElement, CategoryScale, LinearScale, Tooltip } from 'chart.js'
import { useDark } from '@vueuse/core'
import { appChartTheme } from '@/services/ChartjsConfig'

Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip)

const props = defineProps({
  data: {
    type: Object,
    required: true,
    validator(value) {
      return 'total' in value && 'timestamps' in value && 'scores' in value
    }
  },
})

const MONTHS = ['Jän', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez']
const year = new Date().getFullYear()

const canvas = ref(null)
let chart = null
const darkMode = useDark()

const initChart = () => {
  const theme = appChartTheme()
  const peak = Math.max(...props.data.scores, 0)

  chart = new Chart(canvas.value, {
    type: 'bar',
    data: {
      // timestamps are "01".."12"
      labels: props.data.timestamps.map(m => MONTHS[Number(m) - 1] ?? m),
      datasets: [
        {
          label: 'Fänge',
          data: props.data.scores,
          backgroundColor: props.data.scores.map(v => (v > 0 && v === peak ? theme.float : theme.accent)),
          borderRadius: 6,
          barPercentage: 0.65,
          minBarLength: 2,
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
          ticks: { color: theme.text, precision: 0, maxTicksLimit: 5 },
        },
        x: {
          border: { display: false },
          grid: { display: false },
          ticks: { color: theme.text, maxRotation: 0, autoSkip: true },
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
