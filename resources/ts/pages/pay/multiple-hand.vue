<script setup lang="ts">
import SalesSlipDialog from '@/layouts/dialogs/transactions/SalesSlipDialog.vue'
import MultipleHandPayOverview from '@/views/pay/MultipleHandPayOverview.vue'
import { pay } from '@/views/pay/pay'
import { axios } from '@axios'
import corp from '@corp'

const { pay_module, merchandise, updatePayModule } = pay(1)
const salesslip = ref()
const pgs = ref([])

updatePayModule()
provide('salesslip', salesslip)

onMounted(async () => {
    const res = await axios.get('/api/v1/pay-gateways/' + pay_module.value.pg_id + '/sale-slip')
    pgs.value = res.data
})
</script>
<template>
    <section>
        <div id="pay-container">
            <div style="text-align: center;">
                <img :src="corp.logo_img || ''" width="100" height="100">
                <br>
                <b>환영합니다 !</b>
                <br>
                결제하실 정보를 입력해주세요.
            </div>
            <Suspense>
                <MultipleHandPayOverview :pay_module="pay_module" :merchandise="merchandise"/>
            </Suspense>
        </div>
        <br>
        <SalesSlipDialog ref="salesslip" :pgs="pgs" />

    </section>
</template>
<style>
@media screen and (min-width: 960px) {
  #pay-container {
    inline-size: 960px;
  }
}
</style>
