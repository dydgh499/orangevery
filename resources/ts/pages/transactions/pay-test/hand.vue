
<script setup lang="ts">
import SalesSlipDialog from '@/layouts/dialogs/transactions/SalesSlipDialog.vue'
import HandPayOverview from '@/views/pay/HandPayOverview.vue'
import MerchandiseSelectOverview from '@/views/pay/MerchandiseSelectOverview.vue'
import { payTest } from '@/views/transactions/pay-test/payTest'

const {
    mcht_id, pmod_id, pgs, merchandise, pay_modules, pay_module
} = payTest(1)

const salesslip = ref()

provide('mcht_id', mcht_id)
provide('pmod_id', pmod_id)
provide('pay_modules', pay_modules)
provide('salesslip', salesslip)

</script>
<template>
    <section>
        <VRow style="justify-content: center;">
            <VCard rounded>
                <VCardText>
                    <VRow class="match-height">
                        <VCol cols="12" md="12" class="d-flex justify-center align-center">
                            <div style="width: 700px;">
                                <HandPayOverview :pay_module="pay_module" :merchandise="merchandise">
                                    <template #explain>
                                        <MerchandiseSelectOverview/>
                                    </template>
                                </HandPayOverview>
                            </div>
                        </VCol>
                    </VRow>
                </VCardText>
            </VCard>
        </VRow>
        <SalesSlipDialog ref="salesslip" :pgs="pgs" />
    </section>
</template>
