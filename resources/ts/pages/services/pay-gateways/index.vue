
<script setup lang="ts">
import PayGatewayOverview from '@/views/services/pay-gateways/PayGatewayOverview.vue';
import ClassificationOverview from '@/views/services/pay-gateways/ClassificationOverview.vue';
import CreateForm from '@/layouts/utils/CreateForm.vue'
import type { Tab } from '@/views/types'

const tabs = <Tab[]>([
    { icon: 'ph-buildings', title: 'PG사 정보' },
    { icon: 'carbon-classification', title: '구분 정보' },
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
                    <ClassificationOverview />
                </VWindowItem>
            </template>
        </CreateForm>
    </section>
</template>
