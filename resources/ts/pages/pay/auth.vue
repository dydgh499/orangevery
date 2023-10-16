<script setup lang="ts">
import AuthPayOverview from '@/views/pay/AuthPayOverview.vue'
import { pay } from '@/views/pay/pay'
import corp from '@corp'

const { 
    pmod_id, installment, return_url, pay_url, 
    getSalesSlipInfo 
} = pay(2)
getSalesSlipInfo()

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
                                <b>환영합니다 !</b>
                                <br>
                                결제하실 정보를 입력해주세요.
                            </div>
                            <AuthPayOverview :pmod_id="pmod_id || 0" :installment="installment || 0"
                                :return_url="return_url" :pay_url="pay_url" />
                        </div>
                    </VCol>
                </VRow>
            </VCardText>
        </VCard>
    </section>
</template>
<style scoped>
@media (min-width: 700px) {
  #pay-container {
    inline-size: 700px;
  }
}
</style>
