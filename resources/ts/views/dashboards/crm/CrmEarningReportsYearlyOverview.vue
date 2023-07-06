<script setup lang="ts">
import VueApexCharts from 'vue3-apexcharts'
import { useTheme } from 'vuetify'
import { hexToRgb } from '@layouts/utils'

const vuetifyTheme = useTheme()

const currentTab = ref<number>(0)
const refVueApexChart = ref()

const getPreviousMonths = () => {
    const currentDate = new Date(); // 현재 날짜 가져오기
    const months: string[] = []; // 결과를 저장할 배열

    for (let i = 0; i < 10; i++) {
        const targetDate = new Date(currentDate.getFullYear(), currentDate.getMonth() - i, 1);
        const targetMonth = targetDate.toLocaleString('default', { month: 'long' }); // 월 이름 가져오기
        months.unshift(targetMonth); // 배열의 맨 앞에 추가
    }
    return months;
}
const categories = getPreviousMonths()
const chartConfigs = computed(() => {
  const currentTheme = vuetifyTheme.current.value.colors
  const variableTheme = vuetifyTheme.current.value.variables

  const labelPrimaryColor = `rgba(${hexToRgb(currentTheme.primary)},${variableTheme['dragged-opacity']})`
  const legendColor = `rgba(${hexToRgb(currentTheme['on-background'])},${variableTheme['high-emphasis-opacity']})`
  const borderColor = `rgba(${hexToRgb(String(variableTheme['border-color']))},${variableTheme['border-opacity']})`
  const labelColor = `rgba(${hexToRgb(currentTheme['on-surface'])},${variableTheme['disabled-opacity']})`

  return [
    {
      title: '승인',
      icon: 'tabler-shopping-cart',
      chartOptions: {
        chart: {
          parentHeightOffset: 0,
          type: 'bar',
          toolbar: {
            show: false,
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
        colors: [
          labelPrimaryColor,
          labelPrimaryColor,
          labelPrimaryColor,
          labelPrimaryColor,
          labelPrimaryColor,
          labelPrimaryColor,
          labelPrimaryColor,
          labelPrimaryColor,
          currentTheme.primary,
        ],
        dataLabels: {
          enabled: true,
          formatter(val: unknown) {
            return `${val}k`
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
          categories: categories,
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
              return `${parseInt(val / 1)}k`
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
      series: [
        {
          data: [28, 10, 45, 38, 15, 30, 35, 30, 8],
        },
      ],
    },
    {
      title: '취소',
      icon: 'tabler-chart-bar',
      chartOptions: {
        chart: {
          parentHeightOffset: 0,
          type: 'bar',
          toolbar: {
            show: false,
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
        colors: [
          labelPrimaryColor,
          labelPrimaryColor,
          labelPrimaryColor,
          labelPrimaryColor,
          labelPrimaryColor,
          labelPrimaryColor,
          labelPrimaryColor,
          labelPrimaryColor,
          currentTheme.primary,
        ],
        dataLabels: {
          enabled: true,
          formatter(val: any) {
            return `${val}k`
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
          categories: categories,
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
              return `${parseInt(val / 1)}k`
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
              grid: {
                padding: {
                  right: 0,
                },
              },
              dataLabels: {
                style: {
                  fontSize: '12px',
                  fontWeight: '400',
                },
              },
              yaxis: {
                labels: {
                  show: false,
                },
              },
            },
          },
        ],
      },
      series: [
        {
          data: [35, 25, 15, 40, 42, 25, 48, 8, 30],
        },
      ],
    },
    {
      title: '매출',
      icon: 'tabler-currency-dollar',
      chartOptions: {
        chart: {
          parentHeightOffset: 0,
          type: 'bar',
          toolbar: {
            show: false,
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
        colors: [
          labelPrimaryColor,
          labelPrimaryColor,
          labelPrimaryColor,
          labelPrimaryColor,
          labelPrimaryColor,
          labelPrimaryColor,
          labelPrimaryColor,
          labelPrimaryColor,
          currentTheme.primary,
        ],
        dataLabels: {
          enabled: true,
          formatter(val: any) {
            return `${val}k`
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
          categories: categories,
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
            formatter(val: unknown) {
              return `${parseInt(val / 1)}k`
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
              grid: {
                padding: {
                  right: 0,
                },
              },
              dataLabels: {
                style: {
                  fontSize: '12px',
                  fontWeight: '400',
                },
              },
              yaxis: {
                labels: {
                  show: false,
                },
              },
            },
          },
        ],
      },
      series: [
        {
          data: [10, 22, 27, 33, 42, 32, 27, 22, 8],
        },
      ],
    },
    {
      title: '정산',
      icon: 'tabler-chart-pie-2',
      chartOptions: {
        chart: {
          parentHeightOffset: 0,
          type: 'bar',
          toolbar: {
            show: false,
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
        colors: [
          labelPrimaryColor,
          labelPrimaryColor,
          labelPrimaryColor,
          labelPrimaryColor,
          labelPrimaryColor,
          labelPrimaryColor,
          labelPrimaryColor,
          labelPrimaryColor,
          currentTheme.primary,
        ],
        dataLabels: {
          enabled: true,
          formatter(val: any) {
            return `${val}k`
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
          categories: categories,
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
              return `${parseInt(val / 1)}k`
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
                  columnWidth: '50%',
                },
              },
              dataLabels: {
                style: {
                  fontSize: '12px',
                  fontWeight: '400',
                },
              },
              grid: {
                padding: {
                  right: 0,
                },
              },
              yaxis: {
                labels: {
                  show: false,
                },
              },
            },
          },
        ],
      },
      series: [
        {
          data: [5, 9, 12, 18, 20, 25, 30, 36, 48],
        },
      ],
    },
  ]
})
</script>

<template>
  <VCard
    title="월별 거래량"
    subtitle="10개월간 거래금 개요"
  >
    <template #append>
      <div class="mt-n4 me-n2">
        <VBtn
          icon
          size="x-small"
          variant="plain"
          color="default"
        >
          <VIcon
            size="22"
            icon="tabler-dots-vertical"
          />

          <VMenu activator="parent">
            <VList>
              <VListItem
                v-for="(item, index) in ['View More', 'Delete']"
                :key="index"
                :value="index"
              >
                <VListItemTitle>{{ item }}</VListItemTitle>
              </VListItem>
            </VList>
          </VMenu>
        </VBtn>
      </div>
    </template>

    <VCardText>
      <VSlideGroup
        v-model="currentTab"
        show-arrows
        mandatory
      >
        <VSlideGroupItem
          v-for="(report, index) in chartConfigs"
          :key="report.title"
          v-slot="{ isSelected, toggle }"
          :value="index"
        >
          <div
            style=" width: 110px;height: 94px;"
            :style="isSelected ? 'border-color:rgb(var(--v-theme-primary)) !important' : ''"
            :class="isSelected ? 'border' : 'border border-dashed'"
            class="d-flex flex-column justify-center align-center cursor-pointer rounded px-5 py-2 me-6"
            @click="toggle"
          >
            <VAvatar
              rounded
              size="38"
              :color="isSelected ? 'primary' : 'secondary'"
              variant="tonal"
              class="mb-2"
            >
              <VIcon :icon="report.icon" />
            </VAvatar>
            <p class="mb-0">
              {{ report.title }}
            </p>
          </div>
        </VSlideGroupItem>
        <!-- 👉 slider more -->
      </VSlideGroup>

      <VueApexCharts
        ref="refVueApexChart"
        :key="currentTab"
        :options="chartConfigs[Number(currentTab)].chartOptions"
        :series="chartConfigs[Number(currentTab)].series"
        height="240"
        class="mt-3"
      />
    </VCardText>
  </VCard>
</template>
