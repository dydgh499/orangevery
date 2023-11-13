
<script setup lang="ts">
import type { Tab } from '@/views/types'

import SalesforceRegister from '@/views/services/bulk-register/SalesforceRegister.vue'
import MerchandiseRegister from '@/views/services/bulk-register/MerchandiseRegister.vue'
import PayModuleRegister from '@/views/services/bulk-register/PayModuleRegister.vue'
import RegularCardRegister from '@/views/services/bulk-register/RegularCardRegister.vue'

import CreateForm from '@/layouts/utils/CreateForm.vue'
import corp from '@corp'

const tabs = <Tab[]>([
    { icon: 'tabler-user', title: '영업점 등록' },
    { icon: 'tabler-building-store', title: '가맹점 등록' },
    { icon: 'ic-outline-send-to-mobile', title: '결제모듈 등록' },
])
if(corp.pv_options.paid.use_regular_card)
    tabs.push({ icon: 'emojione:credit-card', title: '단골고객 카드정보 등록' })

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
                <VWindowItem>
                    <Suspense>
                        <RegularCardRegister>
                        </RegularCardRegister>
                    </Suspense>
                </VWindowItem>
            </template>
        </CreateForm>        
    </section>
</template>
