
<script setup lang="ts">
import corp from '@corp'
import type { Tab } from '@/views/types'

import SalesforceRegister from '@/views/services/bulk-register/SalesforceRegister.vue'
import MerchandiseRegister from '@/views/services/bulk-register/MerchandiseRegister.vue'
import PayModuleRegister from '@/views/services/bulk-register/PayModuleRegister.vue'

import CreateForm from '@/layouts/utils/CreateForm.vue'

const tabs = <Tab[]>([
    { icon: 'tabler-user', title: '영업점 등록' },
    { icon: 'tabler-building-store', title: '가맹점 등록' },
    { icon: 'ic-outline-send-to-mobile', title: '결제모듈 등록' },
])
const brand_id = ref<number>(corp.id) //brand_id
const formatDownload = () => {

}
const formatUpload = () => {

}
const validate = () => {

}

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
                    <PayModuleRegister>
                    </PayModuleRegister>
                </VWindowItem>
            </template>
        </CreateForm>        
    </section>
</template>
<style lang="scss">
.list-square {
  padding-block: 0;
  padding-inline: 6px !important;
  text-align: center !important;
}
</style>
