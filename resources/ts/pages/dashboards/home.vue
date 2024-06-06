<script setup lang="ts">
import SkeletonBox from '@/layouts/utils/SkeletonBox.vue'
import CrmOperatorHistory from '@/views/dashboards/crm/CrmOperatorHistory.vue'
import CrmPayModuleGrowth from '@/views/dashboards/crm/CrmPayModuleGrowth.vue'
import CrmRecentDanagerTransaction from '@/views/dashboards/crm/CrmRecentDanagerTransaction.vue'
import CrmRecentLockUser from '@/views/dashboards/crm/CrmRecentLockUser.vue'
import CrmRevenueGrowth from '@/views/dashboards/crm/CrmRevenueGrowth.vue'

import CrmRevenueYearlyGrowth from '@/views/dashboards/crm/CrmRevenueYearlyGrowth.vue'
import CrmUserGrowth from '@/views/dashboards/crm/CrmUserGrowth.vue'
import { useCRMStore } from '@/views/dashboards/crm/crm'
import { getUserLevel } from '@axios'

const { upside_merchandises, upside_salesforces, monthly_transactions, getGraphData } = useCRMStore()
const is_skeleton = ref(true)
provide('is_skeleton', is_skeleton)

const simpleStatisticsDemoCards = ref([
    {
        icon: 'tabler-calculator',
        color: 'warning',
        title: '금월 수익금',
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
onMounted(async() => {
    await getGraphData()
    is_skeleton.value = false
    watchEffect(() => {
        if(Object.keys(monthly_transactions).length > 0) {
            simpleStatisticsDemoCards.value[0]['stat'] = monthly_transactions.cur_profit.toLocaleString()+' ￦'
            simpleStatisticsDemoCards.value[1]['stat'] = monthly_transactions.cur_amount.toLocaleString()+' ￦'
            simpleStatisticsDemoCards.value[0]['change'] = monthly_transactions.cur_profit_rate?.toLocaleString() +'%'
            simpleStatisticsDemoCards.value[1]['change'] = monthly_transactions.cur_amount_rate?.toLocaleString() +'%'
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
                        <template v-if="is_skeleton">
                            <SkeletonBox/>
                        </template>
                        <template v-else>
                            {{ demo.stat }}                            
                        </template>
                    </p>
                    <br>
                    <p class="text-sm mt-0 mb-1">
                        {{ demo.subTitle }}
                        <template v-if="is_skeleton">
                            <SkeletonBox/>
                        </template>
                        <template v-else>
                            <VChip :color="demo.color" label>
                                {{ demo.change }}
                            </VChip>                        
                        </template>
                    </p>
                </VCardText>
            </VCard>
        </VCol>
        <VCol cols="12" md="8" lg="4">
            <CrmRevenueGrowth />
        </VCol>

        <VCol cols="12" md="8">
            <CrmRevenueYearlyGrowth />
        </VCol>
        <!-- 👉 PayModule -->
        <VCol cols="12" md="4">
            <CrmPayModuleGrowth />
        </VCol>
        <!-- 👉 Recent Transaction -->
        <VCol cols="12" :md="getUserLevel() < 35 ? 6 : 4">
            <CrmRecentDanagerTransaction />
        </VCol>
        <VCol cols="12" md="4" v-if="getUserLevel() >= 35">
            <CrmOperatorHistory />
        </VCol>
        <VCol cols="12" md="4" v-if="getUserLevel() >= 35">
            <CrmRecentLockUser />
        </VCol>
    </VRow>
</template>
