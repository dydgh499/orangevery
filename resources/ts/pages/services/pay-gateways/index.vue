
<script setup lang="ts">
import PayGatewayOverview from '@/views/services/pay-gateways/PayGatewayOverview.vue';
import ClassificationOverview from '@/views/services/pay-gateways/ClassificationOverview.vue';
import CreateForm from '@/layouts/utils/CreateForm.vue'
import corp from '@corp';
import type { Tab } from '@/views/types'

const tabs = <Tab[]>([
    { icon: 'ph-buildings', title: 'PG사 정보' },
    { icon: 'carbon-classification', title: '구분 정보' },
])
const path = 'pay-gateways'
const brand_id = ref<number>(corp.id) //brand_id
</script>
<template>
    <section>
        <CreateForm :id="0" :path="path" :tabs="tabs" :item="[]">
            <template #view>
                <VWindowItem>
                    <PayGatewayOverview :brand_id="brand_id" />
                </VWindowItem>
                <VWindowItem>
                    <ClassificationOverview :brand_id="brand_id" />
                </VWindowItem>
            </template>
        </CreateForm>
    </section>
</template>
