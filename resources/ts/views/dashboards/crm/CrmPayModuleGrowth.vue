<script setup lang="ts">
import VueApexCharts from 'vue3-apexcharts'
import { useTheme } from 'vuetify'
import { hexToRgb } from '@layouts/utils'
import { useCRMStore } from '@/views/dashboards/crm/crm'

const vuetifyTheme = useTheme()
const { monthly_transactions } = useCRMStore()

const months = ref<string[]>([])
const series = ref([
    {
        name: '장비',
        data: [] as number [],
    },
    {
        name: '수기결제',
        data: [] as number [],
    },
    {
        name: '인증결제',
        data: [] as number [],
    },
    {
        name: '간편결제',
        data: [] as number [],
    },
])

const getPreviousMonths = (dates: string[]) => {
    const _months: string[] = []
    for (let i = 0; i < dates.length; i++) {
        const month = new Date(dates[i]).toLocaleString('default', { month: 'long' });
        _months.unshift(month)
    }
    months.value = _months
}
const getPreviousAmount = (dates: string[]) => {
    const terminal_counts = []
    const hand_counts = []
    const auth_counts = []
    const simple_counts = []
    for (let i = 0; i < dates.length; i++) {
        const tmn_cnt = monthly_transactions[dates[i]].modules.terminal_count
        const hand_cnt = monthly_transactions[dates[i]].modules.hand_count
        const auth_cnt = monthly_transactions[dates[i]].modules.auth_count
        const simple_cnt = monthly_transactions[dates[i]].modules.simple_count

        terminal_counts.unshift(tmn_cnt)
        hand_counts.unshift(hand_cnt)
        auth_counts.unshift(auth_cnt)
        simple_counts.unshift(simple_cnt)
    }
    series.value[0].data = terminal_counts
    series.value[1].data = hand_counts
    series.value[2].data = auth_counts
    series.value[3].data = simple_counts
}

const chartOptions = computed(() => {
    const currentTheme = vuetifyTheme.current.value.colors
    const variableTheme = vuetifyTheme.current.value.variables

    const borderColor = `rgba(${hexToRgb(String(variableTheme['border-color']))},${variableTheme['border-opacity']})`
    const labelColor = `rgba(${hexToRgb(currentTheme['on-surface'])},${variableTheme['disabled-opacity']})`
    const legendColor = `rgba(${hexToRgb(currentTheme['on-background'])},${variableTheme['high-emphasis-opacity']})`

    if (Object.keys(monthly_transactions).length > 0) {
        getPreviousMonths(Object.keys(monthly_transactions))
        getPreviousAmount(Object.keys(monthly_transactions))
    }
    return {
        chart: {
            type: 'radar',
            toolbar: {
                show: false,
            },
        },
        plotOptions: {
            radar: {
                polygons: {
                    strokeColors: borderColor,
                    connectorColors: borderColor,
                },
            },
        },
        stroke: {
            show: false,
            width: 0,
        },
        legend: {
            show: true,
            fontSize: '14px',
            position: 'bottom',
            labels: {
                colors: legendColor,
                useSeriesColors: false,
            },
            markers: {
                height: 10,
                width: 10,
                offsetX: -3,
            },
            itemMargin: {
                horizontal: 10,
            },
            onItemHover: {
                highlightDataSeries: false,
            },
        },
        colors: [currentTheme.secondary, currentTheme.primary, currentTheme.success, currentTheme.info],
        fill: {
            opacity: [0.7, 0.7, 0.7, 0.7],
        },
        markers: {
            size: 0,
        },
        grid: {
            show: false,
            padding: {
                top: 0,
                bottom: -5,
            },
        },
        xaxis: {
            categories: months.value,
            labels: {
                show: true,
                style: {
                    fontSize: '14px',
                    fontFamily: 'Public Sans',
                },
            },
        },
        yaxis: {
            show: false,
            min: 0,
            max: 100,
            tickAmount: 4,
        },
        responsive: [
            {
                breakpoint: 769,
                options: {
                    chart: {
                        height: 372,
                    },
                },
            },
        ],
    }
})
</script>

<template>
    <VCard>
        <VCardItem class="pb-0">
            <VCardTitle>결제모듈 사용량</VCardTitle>
            <VCardSubtitle>10개월간 결제모듈 사용량 개요</VCardSubtitle>
        </VCardItem>

        <VCardText>
            <VueApexCharts :options="chartOptions" :series="series" height="355" />
        </VCardText>
    </VCard>
</template>
