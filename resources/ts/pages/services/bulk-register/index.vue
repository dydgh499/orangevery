
<script setup lang="ts">
/*
import NotiUrlRegister from '@/views/services/bulk-register/NotiUrlRegister.vue'
import MchtBlacklistRegister from '@/views/services/bulk-register/MchtBlacklistRegister.vue'
import PayModuleRegister from '@/views/services/bulk-register/PayModuleRegister.vue'
import MerchandiseRegister from '@/views/services/bulk-register/MerchandiseRegister.vue'
import SalesforceRegister from '@/views/services/bulk-register/SalesforceRegister.vue'
import RegularCardRegister from '@/views/services/bulk-register/RegularCardRegister.vue'
*/
import type { Tab } from '@/views/types'
import WithdrawRegister from '@/views/services/bulk-cms-transactions/WithdrawRegister.vue'
import corp from '@corp'

const tab = ref(0)
const tabs = <Tab[]>([
    /*
    { icon: 'tabler-user', title: '영업라인 등록' },
    { icon: 'tabler-building-store', title: '가맹점 등록' },
     */
])
if(corp.pv_options.paid.use_realtime_deposit)
    tabs.push({ icon: 'marketeq:wallet-money', title: '정산지갑 등록' })
/*
tabs.push({ icon: 'ic-outline-send-to-mobile', title: '결제모듈 등록' })
if(corp.pv_options.paid.use_regular_card)
    tabs.push({ icon: 'emojione:credit-card', title: '단골고객 카드정보 등록' })
if(corp.pv_options.paid.use_mcht_blacklist)
    tabs.push({ icon: 'arcticons:blacklistblocker', title: '가맹점 블랙리스트 등록' })
if(corp.pv_options.paid.use_noti)
    tabs.push({ icon: 'emojione:envelope', title: '노티주소 등록' })
    */
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
                <!--
                <VWindowItem>
                    <SalesforceRegister/>
                </VWindowItem>
                <VWindowItem>
                    <MerchandiseRegister/>
                </VWindowItem>
                <VWindowItem>
                    <Suspense>
                        <PayModuleRegister/>
                    </Suspense>
                </VWindowItem>
                <VWindowItem v-if="corp.pv_options.paid.use_mcht_blacklist">
                    <Suspense>
                        <MchtBlacklistRegister/> 
                    </Suspense>
                </VWindowItem>
                <VWindowItem v-if="corp.pv_options.paid.use_noti">
                    <Suspense>
                        <NotiUrlRegister/>
                    </Suspense>
                </VWindowItem>
                <VWindowItem>
                    <Suspense>
                        <RegularCardRegister/>
                    </Suspense>
                </VWindowItem>
                -->
                <VWindowItem v-if="corp.pv_options.paid.use_realtime_deposit">
                    <Suspense>
                        <WithdrawRegister/>
                    </Suspense>
                </VWindowItem>
            </VWindow>
        </VForm>
    </section>
</template>
