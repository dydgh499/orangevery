<script setup lang="ts">
import VueApexCharts from 'vue3-apexcharts'
import { useTheme } from 'vuetify'
import type { UpSideChart, Series } from '@/views/types'
import SkeletonBox from '@/layouts/utils/SkeletonBox.vue'

interface Props {
    dest_user: string,
    datas: UpSideChart,
}
const props = defineProps<Props>()
const is_skeleton = <any>(inject('is_skeleton'))

const vuetifyTheme = useTheme()
const series = ref(<Series[]>([
    {
        name: '감소',
        data: [] as number[],
    },
    {
        name: '추가',
        data: [] as number[],
    },
]))
const rate = ref(0)
const getChartData = (col: string): number[] => {
    const datas = []
    const keys = Object.keys(props.datas.graph)
    for (let i = 0; i < keys.length; i++) 
    {
        let data = props.datas.graph[keys[i]][col]
        if (col == 'del_rate')
            data *= 1
        datas.push(data)        
    }
    return datas
}
watchEffect(() => {
    if (Object.keys(props.datas).length > 0) {
        series.value[0].data = getChartData('del_count')
        series.value[1].data = getChartData('add_count')
        rate.value = props.datas['cur_increase_rate'] as number
    }    
})
const chartOptions = computed(() => {
    const currentTheme = vuetifyTheme.current.value.colors
    return {
        chart: {
            type: 'bar',
            height: 90,
            parentHeightOffset: 0,
            stacked: true,
            toolbar: {
                show: false,
            },
        },
        series: series.value,
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '30%',
                barHeight: '100%',
                borderRadius: 5,
                startingShape: 'rounded',
                endingShape: 'rounded',
            },
        },
        dataLabels: {
            enabled: false,
        },
        tooltip: {
            enabled: false,
        },
        stroke: {
            curve: 'smooth',
            width: 1,
            lineCap: 'round',
            colors: [currentTheme.surface],
        },
        legend: {
            show: false,
        },
        colors: [currentTheme.error, currentTheme.success],
        grid: {
            show: false,
            padding: {
                top: -41,
                right: -5,
                left: -3,
                bottom: -22,
            },
        },
        xaxis: {
            categories: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
            labels: {
                show: false,
            },
            axisBorder: {
                show: false,
            },
            axisTicks: {
                show: false,
            },
        },
        yaxis: {
            show: false,
        },
        responsive: [
            {
                breakpoint: 1441,
                options: {
                    plotOptions: {
                        bar: {
                            columnWidth: '40%',
                        },
                    },
                },
            },
            {
                breakpoint: 1300,
                options: {
                    plotOptions: {
                        bar: {
                            columnWidth: '50%',
                        },
                    },
                },
            },
            {
                breakpoint: 1279,
                options: {
                    plotOptions: {
                        bar: {
                            borderRadius: 6,
                            columnWidth: '20%',
                        },
                    },
                },
            },
            {
                breakpoint: 1025,
                options: {
                    plotOptions: {
                        bar: {
                            borderRadius: 7,
                            columnWidth: '25%',
                        },
                    },
                    chart: {
                        height: 110,
                    },
                },
            },
            {
                breakpoint: 960,
                options: {
                    plotOptions: {
                        bar: {
                            borderRadius: 6,
                        },
                    },
                },
            },
            {
                breakpoint: 782,
                options: {
                    plotOptions: {
                        bar: {
                            columnWidth: '30%',
                        },
                    },
                },
            },
            {
                breakpoint: 600,
                options: {
                    plotOptions: {
                        bar: {
                            borderRadius: 12,
                            columnWidth: '25%',
                        },
                    },
                    chart: {
                        height: 160,
                    },
                },
            },
            {
                breakpoint: 426,
                options: {
                    plotOptions: {
                        bar: {
                            borderRadius: 8,
                        },
                    },
                },
            },
            {
                breakpoint: 376,
                options: {
                    plotOptions: {
                        bar: {
                            borderRadius: 6,
                        },
                    },
                },
            },
        ],
        states: {
            hover: {
                filter: {
                    type: 'none',
                },
            },
            active: {
                filter: {
                    type: 'none',
                },
            },
        },
    }
})
</script>
<template>
    <VCard>
        <VCardText>
            <div>
                <h6 class="text-h6">
                    {{ props.dest_user }}
                </h6>
                <span class="text-sm">5개월간 등록량 개요</span>
            </div>

            <VueApexCharts :options="chartOptions" :series="series" :height="82" />

            <div class="d-flex align-center justify-space-between mt-4">
                <h6 class="text-h6 text-center font-weight-semibold">
                    <template v-if="is_skeleton">
                        <SkeletonBox :width="'2em'" />
                    </template>
                    <template v-else>
                        <span class="font-weight-light" style="font-size: 0.8em;">총 </span>
                        <span>{{ props.datas.total?.toLocaleString() || 0 }}</span>
                        <span class="font-weight-light" style="font-size: 0.8em;">개</span>
                    </template>
                </h6>
                <span class="text-sm text-success">
                    <span class="text-body-2">작월 대비</span>
                    <template v-if="is_skeleton">
                        <SkeletonBox />
                    </template>
                    <template v-else>
                        {{ rate.toFixed(1) }}%
                    </template>
                </span>
            </div>
        </VCardText>
    </VCard>
</template>
