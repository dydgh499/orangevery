
<script setup lang="ts">
import PayGatewayOverview from '@/views/pay-gateways/PayGatewayOverview.vue';
import CreateForm from '@/views/utils/CreateForm.vue'
import corp from '@corp';
import type { Tab } from '@/views/types'

const tabs = <Tab[]>([
    { icon: 'ph-buildings', title: 'PG사 정보' },
])
const path = 'pay-gateways'
const id = ref<number>(corp.id) //brand_id
</script>
<template>
    <section>
        <CreateForm :id="id" :path="path" :tabs="tabs" :item="[]">
            <template #view>
                <VWindowItem>
                    <PayGatewayOverview :id="id" />
                </VWindowItem>
            </template>
        </CreateForm>
    </section>
</template>
