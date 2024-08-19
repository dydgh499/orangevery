<script setup lang="ts">
import SalesSlipDialog from '@/layouts/dialogs/transactions/SalesSlipDialog.vue'
import router from '@/router'
import AuthPayOverview from '@/views/pay/AuthPayOverview.vue'
import HandPayOverview from '@/views/pay/HandPayOverview.vue'
import SimplePayOverview from '@/views/pay/SimplePayOverview.vue'
import type { Merchandise, PayGateway, PayModule, PayWindow } from '@/views/types'
import { axios } from '@axios'
import { hourTimer } from '@core/utils/timer'
import corp from '@corp'

const route = useRoute()
const {remaining_time, expire_time, getRemainTimeColor, updateRemainingTime} = hourTimer()
const payment_gateways = ref(<PayGateway[]>[])
const merchandise = ref(<Merchandise>({}))
const pay_module = ref(<PayModule>{module_type: 0})
const pay_window = ref(<PayWindow>({}))
const params = ref({
    item_name : '',
    buyer_name : '',
    amount : 0,
    buyer_phone : '',
})

const salesslip = ref()
const window_code = decodeURIComponent(route.query.wc as string)
const param_code = decodeURIComponent(route.query.pc as string)

const return_url = window.location.origin + '/pay/result'
const pay_url = ref(<string>(''))

const code = ref(200)
const message = ref()

const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))

provide('salesslip', salesslip)
provide('params', params)

onMounted(async () => {
    try {
        const res = await axios.get('/api/v1/pay-windows/' + window_code, {
            params : {
                pc: param_code
            }
        })
        pay_window.value = res.data.pay_window
        pay_module.value = res.data.payment_module
        merchandise.value = res.data.merchandise
        payment_gateways.value = [res.data.payment_gateway]
        if(res.data.params && Object.keys(res.data.params))
            params.value = res.data.params

        expire_time.value = pay_window.value.holding_able_at
        if (pay_module.value.module_type == 2)
            pay_url.value = import.meta.env.VITE_NOTI_URL + '/v2/online/pay/auth'
        else if (pay_module.value.module_type == 3)
            pay_url.value = import.meta.env.VITE_NOTI_URL + '/v2/online/pay/simple'

        const intervalId = setInterval(updateRemainingTime, 1002);
    }
    catch (e: any) {
        code.value = e.response.data.code
        message.value = e.response.data.message
        snackbar.value.show(e.response.data.message, 'error')
        const r = errorHandler(e)
    }
})
</script>
<template>
    <section>
        <VCard rounded>
            <VCardText style="padding: 0.5em;">
                <div style="position: absolute; display: flex; width: 100%; justify-content: space-between;" v-if="pay_module?.module_type > 0 || code !== 200">
                    <div style="display: inline-flex; flex-direction: column;" v-if="pay_module?.module_type === 1">
                        <h5>결제창 유효시간</h5>
                        <b :class="getRemainTimeColor">{{ remaining_time }}</b>
                    </div>
                    <VBtn
                        @click="router.back()" size="small" 
                        color="warning"
                        style="margin-right: 1em;">
                        뒤로가기
                    </VBtn>
                </div>
                <VRow class="match-height">
                    <VCol cols="12" class="d-flex justify-center align-center" v-if="pay_module?.module_type > 0">
                        <div style="max-width: 700px;">
                            <div style="padding-bottom: 1em;text-align: center;">
                                <img :src="corp.logo_img || ''" width="80">
                                <div>
                                    <b>환영합니다 !</b>
                                    <br>
                                    <span>결제하실 정보를 입력해주세요.</span>
                                </div>
                            </div>
                            <HandPayOverview 
                                v-if="pay_module?.module_type === 1"
                                :pay_module="pay_module" 
                                :merchandise="merchandise"                             
                            />
                            <AuthPayOverview 
                                v-else-if="pay_module?.module_type === 2"
                                :pay_module="pay_module" 
                                :merchandise="merchandise" 
                                :return_url="return_url"
                                :pay_url="pay_url"
                            />
                            <SimplePayOverview 
                                v-else-if="pay_module?.module_type === 3"
                                :pay_module="pay_module" 
                                :merchandise="merchandise"                             
                                :return_url="return_url"
                                :pay_url="pay_url"
                            />
                        </div>
                    </VCol>
                    <VCol v-else-if="code !== 200" style="padding: 3em;">
                        <div style="text-align: center;">
                            <VIcon size="40" icon="line-md:emoji-frown-twotone" color="error"/>                        
                        </div>
                        <br>
                        <h2>결제창을 사용할 수 없습니다. </h2>
                        <div style=" padding: 1em;text-align: center;">
                            <h4>- {{ message }} -</h4>
                        </div>
                    </VCol>
                    <VCol v-else-if="pay_module?.module_type === 0" style="padding: 3em;">
                        <b>결제창을 로딩하고 있습니다...</b>
                        <div style=" padding: 1em;text-align: center;">
                            <VIcon size="40" icon="svg-spinners:3-dots-move" style="margin-right: 0.5em;"/>
                            <VIcon size="40" icon="svg-spinners:3-dots-move" />
                        </div>
                    </VCol>
                </VRow>
            </VCardText>
        </VCard>
        <br>
        <SalesSlipDialog ref="salesslip" :pgs="payment_gateways" />
    </section>
</template>
