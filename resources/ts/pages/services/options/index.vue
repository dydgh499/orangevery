
<script setup lang="ts">
import CreateForm from '@/layouts/utils/CreateForm.vue'
import PayGatewayOverview from '@/views/services/options/PayGatewayOverview.vue'
import FinanceVanOverview from '@/views/services/options/FinanceVanOverview.vue'
import PayModuleOverview from '@/views/services/options/PayModuleOverview.vue'
import BillKeyOverview from '@/views/services/options/BillKeyOverview.vue'
import type { Tab } from '@/views/types'
import corp from '@/plugins/corp'
import { getUserLevel } from '@/plugins/axios'

const tab = ref(0)
const tabs = <Tab[]>([])

if(getUserLevel() >= 40) {
    if(corp.ov_options.paid.yn_delivery_mode) {
        tabs.push(
            { icon: 'ph-buildings', title: '결제대행사' },
            { icon: 'tabler:cash', title: '결제모듈' },
            { icon: 'tabler:cards', title: '빌키' },
            { icon: 'streamline:money-atm-card-2-deposit-money-payment-finance-atm-withdraw', title: '이체모듈' },
        )
    }
    else {
        tabs.push(
            { icon: 'streamline:money-atm-card-2-deposit-money-payment-finance-atm-withdraw', title: '이체모듈' },
        )
    }
}
else {
    if(corp.ov_options.paid.yn_delivery_mode) {
        tabs.push(
            { icon: 'tabler:cash', title: '결제모듈' },
            { icon: 'tabler:cards', title: '빌키' },
        )
    }
    else {
        tabs.push(
            { icon: 'streamline:money-atm-card-2-deposit-money-payment-finance-atm-withdraw', title: '이체모듈' },
        )
    }
}

</script>
<template>
    <section>
        <VTabs v-model="tab" class="v-tabs-pill">
            <VTab v-for="(t, index) in tabs" :key="index">
                <VIcon :size="18" :icon="t.icon" class="me-1" />
                <span>{{ t.title }}</span>
            </VTab>
        </VTabs>
        <VForm ref="vForm" class="mt-5">
            <VWindow v-model="tab" :touch="false">
                <VWindowItem v-if="tabs.find(obj => obj.title === '결제대행사')">
                    <PayGatewayOverview />
                </VWindowItem>
                <VWindowItem v-if="tabs.find(obj => obj.title === '결제모듈')">
                    <PayModuleOverview />
                </VWindowItem>
                <VWindowItem v-if="tabs.find(obj => obj.title === '빌키')">
                    <BillKeyOverview />
                </VWindowItem>
                <VWindowItem v-if="tabs.find(obj => obj.title === '이체모듈')">
                    <FinanceVanOverview />
                </VWindowItem>
            </VWindow>
        </VForm>
    </section>
</template>
