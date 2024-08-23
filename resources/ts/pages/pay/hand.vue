<script setup lang="ts">
import SalesSlipDialog from '@/layouts/dialogs/transactions/SalesSlipDialog.vue'
import HandPayOverview from '@/views/pay/HandPayOverview.vue'
import type { Merchandise, PayGateway, PayModule, PayWindow } from '@/views/types';
import { pay } from '@/views/pay/pay'
import { axios } from '@axios'
import corp from '@corp'
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
        <VCard rounded>
            <VCardText>
                <VRow class="match-height">
                    <VCol cols="12" class="d-flex justify-center align-center">
                        <div style="max-width: 700px;">
                            <div style="padding-bottom: 1em;text-align: center;">
                                <img :src="corp.logo_img || ''" width="100" height="100">
                                <div>
                                    <b>환영합니다 !</b>
                                    <br>
                                    <span>결제하실 정보를 입력해주세요.</span>
                                </div>
                            </div>
                            <HandPayOverview :pay_module="pay_module" :merchandise="merchandise"/>
                        </div>
                    </VCol>
                </VRow>
            </VCardText>
        </VCard>
        <br>
        <SalesSlipDialog ref="salesslip" :pgs="payment_gateways" />
    </section>
</template>
