
<script setup lang="ts">
import PayGatewayOverview from '@/views/services/pay-gateways/PayGatewayOverview.vue'
import ClassificationOverview from '@/views/services/pay-gateways/ClassificationOverview.vue'
import FinanceVanOverview from '@/views/services/pay-gateways/FinanceVanOverview.vue'
import CreateForm from '@/layouts/utils/CreateForm.vue'
import type { Tab } from '@/views/types'
import corp from '@corp'
import { getUserLevel } from '@axios'

const tabs = <Tab[]>([
    { icon: 'ph-buildings', title: 'PG사 정보' },
    { icon: 'carbon-classification', title: '구분 정보' },
])
if((corp.pv_options.paid.use_realtime_deposit || corp.pv_options.paid.use_finance_van_deposit) && getUserLevel() >= 35)
    tabs.push({icon: 'streamline:money-atm-card-2-deposit-money-payment-finance-atm-withdraw', title: '실시간 이체 모듈'})
const path = 'services/pay-gateways'
</script>
<template>
    <section>
        <CreateForm :id="0" :path="path" :tabs="tabs" :item="[]">
            <template #view>
                <VWindowItem>
                    <PayGatewayOverview />
                </VWindowItem>
                <VWindowItem>
                    <ClassificationOverview />
                </VWindowItem>
                <VWindowItem v-if="corp.pv_options.paid.use_realtime_deposit && getUserLevel() >= 35">
                    <FinanceVanOverview />
                </VWindowItem>

            </template>
        </CreateForm>
    </section>
</template>
