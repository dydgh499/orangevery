<script setup lang="ts">
import SalesSlipDialog from '@/layouts/dialogs/transactions/SalesSlipDialog.vue';
import MultipleHandPayOverview from '@/views/pay/MultipleHandPayOverview.vue';
import type { Merchandise, PayGateway, PayModule, PayWindow } from '@/views/types';
import { axios } from '@axios';
import corp from '@corp';
import * as CryptoJS from 'crypto-js';

const route = useRoute()
const payment_gateways = ref(<PayGateway[]>[])
const merchandise = ref(<Merchandise>({}))
const pay_module = ref(<PayModule>{module_type: 0})
const pay_window = ref(<PayWindow>({}))
const pmod_id = ref()

const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))
const salesslip = ref()

provide('salesslip', salesslip)

const decryptQuery = () => {
    const encrypt = decodeURIComponent(route.query.e as string)
    const enc = CryptoJS.AES.decrypt(encrypt, '^^_masking_^^').toString(CryptoJS.enc.Utf8)
    const params = JSON.parse(enc)
    if (params.p) {
        pmod_id.value = params.p
    }
}

onMounted(async () => {
    decryptQuery()
    try {
        const res = await axios.get('/api/v1/pay/test?pmod_id='+pmod_id.value)
        pay_window.value = res.data.pay_window
        pay_module.value = res.data.payment_module
        merchandise.value = res.data.merchandise
        payment_gateways.value = [res.data.payment_gateway]
    }
    catch (e: any) {
        snackbar.value.show(e.response.data.message, 'error')
        const r = errorHandler(e)
    }
})
</script>
<template>
    <section>
        <div id="pay-container">
            <div style="padding-bottom: 1em;text-align: center;">
                <img :src="corp.logo_img || ''" width="100" height="100">
                <div>
                    <b>환영합니다 !</b>
                    <br>
                    <span>결제하실 정보를 입력해주세요.</span>
                </div>
            </div>
            <MultipleHandPayOverview :pay_module="pay_module" :merchandise="merchandise"/>
        </div>
        <br>
        <SalesSlipDialog ref="salesslip" :pgs="payment_gateways" :key="payment_gateways.length"/>
    </section>
</template>
<style>
@media screen and (min-width: 960px) {
  #pay-container {
    inline-size: 960px;
  }
}
</style>
