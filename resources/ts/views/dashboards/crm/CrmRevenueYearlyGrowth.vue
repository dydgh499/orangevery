<script setup lang="ts">
import VueApexCharts from 'vue3-apexcharts'
import { useTheme } from 'vuetify'
import { hexToRgb } from '@layouts/utils'
import { useCRMStore } from '@/views/dashboards/crm/crm'
import type { Series } from '@/views/types'

const vuetifyTheme = useTheme()
const { monthly_transactions, getColors, getMonths } = useCRMStore()
const currentTab = ref<number>(0)
const refVueApexChart = ref()

const months = ref<string[]>([])
const appr_colors = ref(<string[]>([]))
const cxl_colors = ref(<string[]>([]))
const amount_colors = ref(<string[]>([]))
const profit_colors = ref(<string[]>([]))

const serieses = ref(<Series[][]>([
    [
        {
            name: '승인',
            data: [],
        }
    ],
    [
        {
            name: '취소',
            data: [],
        }
    ],
    [
        {
            name: '매출',
            data: [],
        }
    ],
    [
        {
            name: '정산',
            data: [],
        }
    ],
]))


const getSeries = (dates: string[], col: string, sec_col?: string) => {
    const amount: number[] = []; // 결과를 저장할 배열
    for (let i = 0; i < dates.length; i++) {
        const data = monthly_transactions.monthly[dates[i]][col]
        amount.unshift((sec_col ? data[sec_col] : data) / 100000000)
    }
    return amount
}

watchEffect(() => {
    const currentTheme = vuetifyTheme.current.value.colors
    if(Object.keys(monthly_transactions).length) {
        const keys = Object.keys(monthly_transactions.monthly).reverse()
        if (keys.length > 0) {
            serieses.value[0][0].data = getSeries(keys, 'appr', 'amount')
            serieses.value[1][0].data = getSeries(keys, 'cxl', 'amount')
            serieses.value[2][0].data = getSeries(keys, 'amount')
            serieses.value[3][0].data = getSeries(keys, 'profit')

            months.value = getMonths(keys)
            appr_colors.value = getColors(keys, `rgba(${hexToRgb(currentTheme.primary)},0.16)`, `rgba(${hexToRgb(currentTheme.primary)},1)`)
            cxl_colors.value = getColors(keys, `rgba(${hexToRgb(currentTheme.error)},0.16)`, `rgba(${hexToRgb(currentTheme.error)},1)`)
            amount_colors.value = getColors(keys, `rgba(${hexToRgb(currentTheme.success)},0.16)`, `rgba(${hexToRgb(currentTheme.success)},1)`)
            profit_colors.value = getColors(keys, `rgba(${hexToRgb(currentTheme.warning)},0.16)`, `rgba(${hexToRgb(currentTheme.warning)},1)`)
        }
    }

})
const getChartData = (title: string, icon:string, color:string[], datas:Series[]) => {
    const currentTheme = vuetifyTheme.current.value.colors
    const variableTheme = vuetifyTheme.current.value.variables
    const legendColor = `rgba(${hexToRgb(currentTheme['on-background'])},${variableTheme['high-emphasis-opacity']})`
    const borderColor = `rgba(${hexToRgb(String(variableTheme['border-color']))},${variableTheme['border-opacity']})`
    const labelColor = `rgba(${hexToRgb(currentTheme['on-surface'])},${variableTheme['disabled-opacity']})`

    return {
            title: title,
            icon: icon,
            chartOptions: {
                chart: {
                    parentHeightOffset: 0,
                    type: 'bar',
                    toolbar: {
                        show: true,
                    },
                },
                plotOptions: {
                    bar: {
                        columnWidth: '32%',
                        startingShape: 'rounded',
                        borderRadius: 4,
                        distributed: true,
                        dataLabels: {
                            position: 'top',
                        },
                    },
                },
                grid: {
                    show: false,
                    padding: {
                        top: 0,
                        bottom: 0,
                        left: -10,
                        right: -10,
                    },
                },
                colors: color,
                dataLabels: {
                    enabled: true,
                    formatter(val: number) {
                        return `${val.toFixed(2)}억`
                    },
                    offsetY: -25,
                    style: {
                        fontSize: '15px',
                        colors: [legendColor],
                        fontWeight: '600',
                        fontFamily: 'Public Sans',
                    },
                },
                legend: {
                    show: false,
                },
                tooltip: {
                    enabled: false,
                },
                xaxis: {
                    categories: months.value,
                    axisBorder: {
                        show: true,
                        color: borderColor,
                    },
                    axisTicks: {
                        show: false,
                    },
                    labels: {
                        style: {
                            colors: labelColor,
                            fontSize: '14px',
                            fontFamily: 'Public Sans',
                        },
                    },
                },
                yaxis: {
                    labels: {
                        offsetX: -15,
                        formatter(val: number) {
                            return `${val.toFixed(2)}억`
                        },
                        style: {
                            fontSize: '14px',
                            colors: labelColor,
                            fontFamily: 'Public Sans',
                        },
                        min: 0,
                        max: 60000,
                        tickAmount: 6,
                    },
                },
                responsive: [
                    {
                        breakpoint: 1441,
                        options: {
                            plotOptions: {
                                bar: {
                                    columnWidth: '41%',
                                },
                            },
                        },
                    },
                    {
                        breakpoint: 590,
                        options: {
                            plotOptions: {
                                bar: {
                                    columnWidth: '61%',
                                },
                            },
                            yaxis: {
                                labels: {
                                    show: false,
                                },
                            },
                            grid: {
                                padding: {
                                    right: 0,
                                    left: -20,
                                },
                            },
                            dataLabels: {
                                style: {
                                    fontSize: '12px',
                                    fontWeight: '400',
                                },
                            },
                        },
                    },
                ],
            },
            series: datas,
        }
}
const chartConfigs = computed(() => {
    return [
        getChartData('승인', 'fluent-payment-32-regular', appr_colors.value, serieses.value[0]),
        getChartData('취소', 'tabler-chart-bar', cxl_colors.value, serieses.value[1]),
        getChartData('매출', 'ic-outline-payments', amount_colors.value, serieses.value[2]),
        getChartData('정산', 'tabler-calculator', profit_colors.value, serieses.value[3]),
    ]
})
</script>

<template>
    <VCard title="월별 거래량" subtitle="10개월간 거래금 개요">
        <VCardText>
            <VSlideGroup v-model="currentTab" show-arrows mandatory style="padding: 0.1em 0;">
                <VSlideGroupItem v-for="(report, index) in chartConfigs" :key="report.title" v-slot="{ isSelected, toggle }"
                    :value="index">
                    <div style=" width: 110px;height: 89px;"
                        :style="isSelected ? 'border-color:rgb(var(--v-theme-primary)) !important' : ''"
                        :class="isSelected ? 'border' : 'border border-dashed'"
                        class="d-flex flex-column justify-center align-center cursor-pointer rounded px-5 py-2 me-6"
                        @click="toggle">
                        <VAvatar rounded size="38" :color="isSelected ? 'primary' : 'secondary'" variant="tonal"
                            class="mb-2">
                            <VIcon :icon="report.icon" />
                        </VAvatar>
                        <p class="mb-0">
                            {{ report.title }}
                        </p>
                    </div>
                </VSlideGroupItem>
                <!-- 👉 slider more -->
            </VSlideGroup>

            <VueApexCharts ref="refVueApexChart" :key="currentTab" :options="chartConfigs[Number(currentTab)].chartOptions"
                :series="chartConfigs[Number(currentTab)].series" height="240" class="mt-3" />
        </VCardText>
    </VCard>
</template>
