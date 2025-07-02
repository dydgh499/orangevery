
<script setup lang="ts">
import CreateForm from '@/layouts/utils/CreateForm.vue'
import PayGatewayOverview from '@/views/services/options/PayGatewayOverview.vue'
import FinanceVanOverview from '@/views/services/options/FinanceVanOverview.vue'
import PayModuleOverview from '@/views/services/options/PayModuleOverview.vue'
import BillKeyOverview from '@/views/services/options/BillKeyOverview.vue'
import type { Tab } from '@/views/types'

const tabs = <Tab[]>([
    { icon: 'ph-buildings', title: '결제대행사' },
    { icon: 'tabler:cash', title: '결제모듈' },
    { icon: 'tabler:cards', title: '빌키' },
    { icon: 'streamline:money-atm-card-2-deposit-money-payment-finance-atm-withdraw', title: '이체모듈' },
])
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
                    <PayModuleOverview />
                </VWindowItem>
                <VWindowItem>
                    <BillKeyOverview />
                </VWindowItem>
                <VWindowItem>
                    <FinanceVanOverview />
                </VWindowItem>
            </template>
        </CreateForm>
    </section>
</template>
