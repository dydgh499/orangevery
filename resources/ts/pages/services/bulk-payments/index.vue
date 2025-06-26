
<script setup lang="ts">
import type { Tab } from '@/views/types'
import PaymentRegister from '@/views/services/bulk-payments/PaymentRegister.vue'
import corp from '@corp'

const tab = ref(0)
const tabs = <Tab[]>([
    { icon: 'tabler-building-bank', title: '결제요청' },
])
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
                <VWindowItem>
                    <Suspense>
                        <PaymentRegister/>
                    </Suspense>
                </VWindowItem>
            </VWindow>
        </VForm>
    </section>
</template>
