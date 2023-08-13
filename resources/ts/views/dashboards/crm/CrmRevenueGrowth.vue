<script setup lang="ts">
import VueApexCharts from 'vue3-apexcharts'
import { useTheme } from 'vuetify'
import { hexToRgb } from '@layouts/utils'
import { useCRMStore } from '@/views/dashboards/crm/crm'
import type { TransWeekChart, TransChart, Series } from '@/views/types'
import SkeletonBox from '@/layouts/utils/SkeletonBox.vue'

const first_loading = <any>(inject('first_loading'))

const vuetifyTheme = useTheme()
const { monthly_transactions, getColors, getDayOfWeeks } = useCRMStore()

const week_amount = ref(<number>(0))
const current = ref(<TransChart>({}))
const colors = ref(<string[]>([]))
const dayofweeks = ref(<string[]>([]))
const series = ref(<Series[]>([
    {
        name: '매출',
        data: [],
    }
]))

const getSeries = (idays: string[], week: TransWeekChart) => {
    let amount = 0
    const datas: number[] = []
    
    for (let i = 0; i < idays.length; i++) {
        datas.push(week[idays[i]].amount)
        amount += week[idays[i]].amount
    }
    series.value[0].data = datas
    week_amount.value = amount
}

const chartOptions = computed(() => {
    const currentTheme = vuetifyTheme.current.value.colors
    const variableTheme = vuetifyTheme.current.value.variables
    const labelColor = `rgba(${hexToRgb(currentTheme['on-surface'])},${variableTheme['disabled-opacity']})`

    if (Object.keys(monthly_transactions).length > 0) {
        const curernt_month = new Date().toISOString().slice(0, 7);
        current.value = monthly_transactions[curernt_month]
        if (current) {
            const week = current.value['week']
            if (week) {
                const idays = Object.keys(week)
                getSeries(idays, week)
                dayofweeks.value = getDayOfWeeks(idays)
                colors.value = getColors(idays, `rgba(${hexToRgb(currentTheme.success)},0.16)`, `rgba(${hexToRgb(currentTheme.success)},1)`)
            }
        }
    }
    return {
        chart: {
            type: 'bar',
            parentHeightOffset: 0,
            toolbar: {
                show: false,
            },
        },
        plotOptions: {
            bar: {
                barHeight: '80%',
                columnWidth: '30%',
                startingShape: 'rounded',
                endingShape: 'rounded',
                borderRadius: 6,
                distributed: true,
            },
        },
        tooltip: {
            enabled: false,
        },
        grid: {
            show: false,
            padding: {
                top: -20,
                bottom: -12,
                left: -10,
                right: 0,
            },
        },
        colors: colors.value,
        dataLabels: {
            enabled: false,
        },
        legend: {
            show: false,
        },
        xaxis: {
            categories: dayofweeks.value,
            axisBorder: {
                show: false,
            },
            axisTicks: {
                show: false,
            },
            labels: {
                style: {
                    colors: labelColor,
                    fontSize: '14px',
                    fontFamily: 'Public sans',
                },
            },
        },
        yaxis: {
            labels: {
                show: false,
            },
        },
        states: {
            hover: {
                filter: {
                    type: 'none',
                },
            },
        },
        responsive: [
            {
                breakpoint: 1471,
                options: {
                    plotOptions: {
                        bar: {
                            columnWidth: '50%',
                        },
                    },
                },
            },
            {
                breakpoint: 1350,
                options: {
                    plotOptions: {
                        bar: {
                            columnWidth: '57%',
                        },
                    },
                },
            },
            {
                breakpoint: 1032,
                options: {
                    plotOptions: {
                        bar: {
                            columnWidth: '60%',
                        },
                    },
                },
            },
            {
                breakpoint: 992,
                options: {
                    plotOptions: {
                        bar: {
                            columnWidth: '40%',
                            borderRadius: 8,
                        },
                    },
                },
            },
            {
                breakpoint: 855,
                options: {
                    plotOptions: {
                        bar: {
                            columnWidth: '50%',
                            borderRadius: 6,
                        },
                    },
                },
            },
            {
                breakpoint: 440,
                options: {
                    plotOptions: {
                        bar: {
                            columnWidth: '40%',
                        },
                    },
                },
            },
            {
                breakpoint: 381,
                options: {
                    plotOptions: {
                        bar: {
                            columnWidth: '45%',
                        },
                    },
                },
            },
        ],
    }
})
</script>

<template>
    <VCard>
        <VCardText class="d-flex justify-space-between">
            <div class="d-flex flex-column">
                <div class="mb-auto">
                    <h6 class="text-h6 text-no-wrap">
                        7일간 매출액
                    </h6>
                    <span class="text-sm">7일간 거래금 개요</span>
                    <h5 class="text-h5 my-2 text-h6 font-weight-semibold">
                        <template v-if="first_loading">
                            <SkeletonBox />
                        </template>
                        <template v-else>
                            {{ week_amount.toLocaleString() }} ￦
                        </template>
                    </h5>
                </div>
                <div>
                    <template v-if="first_loading">
                        <SkeletonBox />
                    </template>
                    <template v-else>
                        <p class="text-sm mt-0 mb-1">
                            이전 대비
                            <VChip label color="success">
                                {{ current.week_amount_rate?.toFixed(3) }}%
                            </VChip>
                        </p>
                    </template>

                </div>
            </div>
            <div>
                <VueApexCharts :options="chartOptions" :series="series" :height="165" />
            </div>
        </VCardText>
    </VCard>
</template>
