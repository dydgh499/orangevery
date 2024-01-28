
<script setup lang="ts">
import type { Tab } from '@/views/types'

import SalesforceRegister from '@/views/services/bulk-register/SalesforceRegister.vue'
import MerchandiseRegister from '@/views/services/bulk-register/MerchandiseRegister.vue'
import PayModuleRegister from '@/views/services/bulk-register/PayModuleRegister.vue'
import RegularCardRegister from '@/views/services/bulk-register/RegularCardRegister.vue'
import NotiUrlRegister from '@/views/services/bulk-register/NotiUrlRegister.vue'
import PayModulePGUpdater from '@/views/services/bulk-register/PayModulePGUpdater.vue'

import CreateForm from '@/layouts/utils/CreateForm.vue'
import corp from '@corp'

const tabs = <Tab[]>([
    { icon: 'tabler-user', title: '영업점 등록' },
    { icon: 'tabler-building-store', title: '가맹점 등록' },
    { icon: 'ic-outline-send-to-mobile', title: '결제모듈 등록' },
])

if(corp.use_different_settlement)
    tabs.push({ icon: 'mdi-vector-intersection', title: '가맹점 구간 일괄변경' })
if(corp.pv_options.paid.use_regular_card)
    tabs.push({ icon: 'emojione:credit-card', title: '단골고객 카드정보 등록' })
if(corp.pv_options.paid.use_noti)
    tabs.push({ icon: 'emojione:envelope', title: '노티주소 등록' })
</script>
<template>
    <section>
        <CreateForm :id="0" :path="`services/bulk-register`" :tabs="tabs" :item="[]">
            <template #view>
                <VWindowItem>
                    <SalesforceRegister>
                    </SalesforceRegister>
                </VWindowItem>
                <VWindowItem>
                    <MerchandiseRegister>
                    </MerchandiseRegister>
                </VWindowItem>
                <VWindowItem>
                    <Suspense>
                        <PayModuleRegister>
                        </PayModuleRegister>
                    </Suspense>
                </VWindowItem>
                <VWindowItem v-if="corp.use_different_settlement">
                    <Suspense>
                        <PayModulePGUpdater>
                        </PayModulePGUpdater>
                    </Suspense>
                </VWindowItem>
                <VWindowItem v-if="corp.pv_options.paid.use_regular_card">
                    <Suspense>
                        <RegularCardRegister>
                        </RegularCardRegister>
                    </Suspense>
                </VWindowItem>
                <VWindowItem v-if="corp.pv_options.paid.use_noti">
                    <Suspense>
                        <NotiUrlRegister>
                        </NotiUrlRegister>
                    </Suspense>
                </VWindowItem>
            </template>
        </CreateForm>        
    </section>
</template>
