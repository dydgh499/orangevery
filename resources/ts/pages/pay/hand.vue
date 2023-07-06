<script setup lang="ts">
import HandPayOverview from '@/views/pay/HandPayOverview.vue'
import SalesSlipDialog from '@/layouts/dialogs/SalesSlipDialog.vue'
import { pay } from '@/views/pay/pay'
import corp from '@corp'

const { 
    pmod_id, is_old_auth, installment, 
    merchandise, pgs, getSalesSlipInfo 
} = pay(1)
getSalesSlipInfo()

const salesslip = ref()
provide('salesslip', salesslip)

</script>
<template>
    <section>
        <VCard rounded>
            <VCardText>
                <VRow>
                    <VCol cols="12" md="12" style="padding: 0;">
                        <div id="pay-container">
                            <div style="text-align: center;">
                                <img :src="corp.logo_img || ''" width="100" height="100">
                                <br>
                                <b>
                                    환영합니다 !
                                </b>
                                <br>
                                결제하실 정보를 입력해주세요.
                            </div>
                            <HandPayOverview :pmod_id="pmod_id || 0" :installment="installment || 0"
                                :is_old_auth="is_old_auth || false" :merchandise="merchandise">
                                <template #explain>
                                </template>
                            </HandPayOverview>
                        </div>
                    </VCol>
                </VRow>
            </VCardText>
        </VCard>
        <SalesSlipDialog ref="salesslip" :pgs="pgs" />
    </section>
</template>
<style>
@media (min-width: 700px) {
  #pay-container {
    inline-size: 700px;
  }
}
</style>
