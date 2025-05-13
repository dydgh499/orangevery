
<script setup lang="ts">
import CreateForm from '@/layouts/utils/CreateForm.vue'
import FinanceVanOverview from '@/views/services/pay-gateways/FinanceVanOverview.vue'
import type { Tab } from '@/views/types'

const tabs = <Tab[]>([
    { icon: 'streamline:money-atm-card-2-deposit-money-payment-finance-atm-withdraw', title: '금융 VAN' },
])
const path = 'services/pay-gateways'
</script>
<template>
    <section>
        <CreateForm :id="0" :path="path" :tabs="tabs" :item="[]">
            <template #view>
                <VWindowItem>
                    <FinanceVanOverview />
                </VWindowItem>
            </template>
        </CreateForm>
    </section>
</template>
