<script setup lang="ts">
import CrmPayModuleGrowth from '@/views/dashboards/crm/CrmPayModuleGrowth.vue'
import CrmRevenueYearlyGrowth from '@/views/dashboards/crm/CrmRevenueYearlyGrowth.vue'
import CrmRevenueGrowth from '@/views/dashboards/crm/CrmRevenueGrowth.vue'
import CrmUserGrowth from '@/views/dashboards/crm/CrmUserGrowth.vue'
import CrmRecentDanagerTransaction from '@/views/dashboards/crm/CrmRecentDanagerTransaction.vue'
import CrmOperatorHistory from '@/views/dashboards/crm/CrmOperatorHistory.vue'
import { useCRMStore } from '@/views/dashboards/crm/crm'

const { upside_merchandises, upside_salesforces, monthly_transactions } = useCRMStore()
const simpleStatisticsDemoCards = ref([
    {
        icon: 'tabler-calculator',
        color: 'warning',
        title: '금월 정산액',
        stat: '0',
        subTitle: '작월 대비',
        change: '0%',
    },
    {
        icon: 'ic-outline-payments',
        color: 'success',
        title: '금월 매출액',
        stat: '0',
        subTitle: '작월 대비',
        change: '0%',
    },
])

onMounted(() => {
    watchEffect(() => {
        if(Object.keys(monthly_transactions).length > 0) {
            const curernt_month = new Date().toISOString().slice(0, 7);
            const current = monthly_transactions[curernt_month]
            if(current) {
                simpleStatisticsDemoCards.value[0]['stat'] = current.profit.toLocaleString()+' ￦'
                simpleStatisticsDemoCards.value[1]['stat'] = current.amount.toLocaleString()+' ￦'
                simpleStatisticsDemoCards.value[0]['change'] = current.profit_rate?.toLocaleString() +'%'
                simpleStatisticsDemoCards.value[1]['change'] = current.amount_rate?.toLocaleString() +'%'
            }
        }
    })
})
</script>

<template>
    <VRow>
        <VCol cols="12" md="4" sm="6" lg="2">
            <CrmUserGrowth :dest_user="'가맹점'" :datas="upside_merchandises"/>
        </VCol>

        <VCol cols="12" md="4" sm="6" lg="2">
            <CrmUserGrowth :dest_user="'영업점'" :datas="upside_salesforces"/>
        </VCol>

        <VCol v-for="demo in simpleStatisticsDemoCards" :key="demo.title" cols="12" sm="6" md="4" lg="2">
            <VCard>
                <VCardText>
                    <VAvatar :color="demo.color" variant="tonal" rounded size="42">
                        <VIcon :icon="demo.icon" />
                    </VAvatar>

                    <h6 class="text-h6 mt-3">
                        {{ demo.title }}
                    </h6>
                    <p class="my-2 font-weight-semibold text-h6">
                        {{ demo.stat }}
                    </p>
                    <br>
                    <p class="text-sm text-disabled mt-0 mb-1">
                        {{ demo.subTitle }}
                        <VChip :color="demo.color" label>
                            {{ demo.change }}
                        </VChip>
                    </p>
                </VCardText>
            </VCard>
        </VCol>

        <!-- 👉 Revenue Growth -->
        <VCol cols="12" md="8" lg="4">
            <CrmRevenueGrowth />
        </VCol>

        <!-- 👉 Earning Reports -->
        <VCol cols="12" md="8">
            <CrmRevenueYearlyGrowth />
        </VCol>

        <!-- 👉 PayModule -->
        <VCol cols="12" md="4">
            <CrmPayModuleGrowth />
        </VCol>

        <!-- 👉 Recent Transaction -->
        <VCol cols="12" md="6">
            <CrmRecentDanagerTransaction />
        </VCol>
        <!-- 👉 Active timeline -->

        <VCol cols="12" md="6">
            <CrmOperatorHistory />
        </VCol>
    </VRow>
</template>
