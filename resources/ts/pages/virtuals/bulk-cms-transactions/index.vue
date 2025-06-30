
<script setup lang="ts">
import type { Tab } from '@/views/types'
import BankAccountRegister from '@/views/virtuals/bulk-cms-transactions/BankAccountRegister.vue'
import corp from '@corp'

const tab = ref(0)
const tabs = <Tab[]>([
    { icon: 'marketeq:wallet-money', title: '대량출금' },
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
                        <BankAccountRegister/>
                    </Suspense>
                </VWindowItem>
            </VWindow>
        </VForm>
    </section>
</template>
