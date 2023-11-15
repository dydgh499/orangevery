<script setup lang="ts">
import HandPayOverview from '@/views/pay/HandPayOverview.vue'
import SalesSlipDialog from '@/layouts/dialogs/SalesSlipDialog.vue'
import Footer from '@/layouts/components/Footer.vue'
import { pay } from '@/views/pay/pay'
import corp from '@corp'
import { useRequestStore } from '@/views/request'

const { post } = useRequestStore()
const { pay_module, merchandise, updatePayModule } = pay(1)
const salesslip = ref()
const pgs = ref([])

updatePayModule()
provide('salesslip', salesslip)

onMounted(async () => {
    const res = await post('/api/v1/pay-gateways/' + pay_module.value.pg_id + '/sale-slip', {}, false)
    pgs.value = res.data
})
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
                            <HandPayOverview :pay_module="pay_module" :merchandise="merchandise">
                                <template #explain>
                                </template>
                            </HandPayOverview>
                        </div>
                    </VCol>
                </VRow>
            </VCardText>
        </VCard>
        <br>
        <VCard rounded>
            <VCardText>
                <footer class="layout-footer">
                    <div class="footer-content-container">
                        <Footer />
                    </div>
                </footer>
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
