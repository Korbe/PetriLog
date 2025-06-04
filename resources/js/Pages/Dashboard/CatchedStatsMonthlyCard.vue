<template>
    <header class="flex justify-between items-start mb-2">
      <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Dieser Monat</h2>
    </header>
    <div class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase mb-1">
      Insgesamt gefangen
    </div>
    <div class="flex items-start mb-4">
      <div class="text-3xl font-bold text-gray-800 dark:text-gray-100 mr-2">{{ data.total }}</div>
    </div>
    <div class="grow max-sm:max-h-[128px] xl:max-h-[128px]">
      <canvas ref="canvas" :width="389" :height="200"></canvas>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import { Chart, LineController, LineElement, Filler, PointElement, LinearScale, TimeScale, Tooltip } from 'chart.js'
import 'chartjs-adapter-moment'
import { useDark } from '@vueuse/core'
import { getChartColors, chartAreaGradient } from '@/services/ChartjsConfig'
import { getCssVariable, adjustColorOpacity } from '@/utils/Utils'

Chart.register(LineController, LineElement, Filler, PointElement, LinearScale, TimeScale, Tooltip)

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
const darkMode = useDark()
const { tooltipBodyColor, tooltipBgColor, tooltipBorderColor } = getChartColors()

const resolveCssVariable = (variableName) => getCssVariable(`--color-${variableName}`)

const initChart = () => {
  const primaryColor = resolveCssVariable('primary-500')
  const ctx = canvas.value

  chart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: props.data.timestamps,
      datasets: [
        {
          label: 'Anzahl',
          data: props.data.scores,
          fill: true,
          backgroundColor: (context) => {
            const { ctx, chartArea } = context.chart
            return chartAreaGradient(ctx, chartArea, [
              { stop: 0, color: adjustColorOpacity(primaryColor, 0) },
              { stop: 1, color: adjustColorOpacity(primaryColor, 0.2) },
            ])
          },
          borderColor: primaryColor,
          borderWidth: 2,
          pointRadius: 0,
          pointHoverRadius: 3,
          pointBackgroundColor: primaryColor,
          pointHoverBackgroundColor: primaryColor,
          pointBorderWidth: 0,
          pointHoverBorderWidth: 0,
          clip: 20,
          tension: 0.2,
        },
      ],
    },
    options: {
      layout: { padding: 20 },
      scales: {
        y: {
          display: false,
          beginAtZero: true,
        },
        x: {
          type: 'time',
          time: {
            unit: 'day',
            stepSize: 3,
            tooltipFormat: 'DD-MM-YYYY',
            displayFormats: {
              day: 'DD-MM',
              week: 'DD-MM',
            },
          },
          ticks: {
            autoSkip: true,
            maxTicksLimit: 5,
          },
        },
      },
      plugins: {
        tooltip: {
          callbacks: {
            title: () => false,
            label: (context) => `${context.label} - ${context.parsed.y}`,
          },
          bodyColor: darkMode.value ? tooltipBodyColor.dark : tooltipBodyColor.light,
          backgroundColor: darkMode.value ? tooltipBgColor.dark : tooltipBgColor.light,
          borderColor: darkMode.value ? tooltipBorderColor.dark : tooltipBorderColor.light,
        },
        legend: {
          display: false,
        },
      },
      interaction: {
        intersect: false,
        mode: 'nearest',
      },
      maintainAspectRatio: false,
      resizeDelay: 200,
    },
  })
}

onMounted(initChart)
onUnmounted(() => chart?.destroy())
watch(() => [props.data, props.color], () => {
  chart?.destroy()
  initChart()
}, { deep: true })

</script>