<script setup lang="ts">
import { pinInputEvent } from '@/@core/utils/pin_input_event';
import SalesSlipDialog from '@/layouts/dialogs/transactions/SalesSlipDialog.vue';
import router from '@/router';
import AuthPayOverview from '@/views/pay/AuthPayOverview.vue';
import HandPayOverview from '@/views/pay/HandPayOverview.vue';
import type { Merchandise, PayGateway, PayModule, PayWindow } from '@/views/types';
import { axios } from '@axios';
import { hourTimer } from '@core/utils/timer';
import corp from '@corp';

const route = useRoute()
const {remaining_time, expire_time, getRemainTimeColor, updateRemainingTime} = hourTimer()
const { digits, ref_opt_comp, handleKeyDown, defaultStyle} = pinInputEvent(6)

const payment_gateways = ref(<PayGateway[]>[])
const merchandise = ref(<Merchandise>({}))
const pay_module = ref(<PayModule>{module_type: 0})
const pay_window = ref(<PayWindow>({}))
const params_mode = ref(false)
const params = ref({
    item_name : '',
    buyer_name : '',
    amount : 0,
    buyer_phone : '',
})
const salesslip = ref()

const code = ref(200)
const message = ref()
const sign_in_result = ref(false)

const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))

provide('salesslip', salesslip)
provide('params_mode', params_mode)
provide('params', params)

const handleKeyDownEvent = async (index: number) => {
    handleKeyDown(index)
    if (digits.value.join('').length === 6) {
        try {
            const res = await axios.post('/api/v1/pay/' + route.params.window + '/auth', {
                pin_code: digits.value.join('')
            })
            sign_in_result.value = true
        }
        catch (e: any) {
            snackbar.value.show(e.response.data.message, 'error')
        }
    }
}

onMounted(async () => {
    try {
        const res = await axios.get('/api/v1/pay/' + route.params.window, {
            params : {
                pc: route.query.pc
            }
        })
        if(route.query.pc && route.query.pc.length && res.data.params === null) {
            code.value = 409
            message.value = '상품정보를 찾을 수 없습니다. 결제창을 재생성해주세요.'
        }
        else {
            pay_window.value = res.data.pay_window
            pay_module.value = res.data.payment_module
            merchandise.value = res.data.merchandise
            payment_gateways.value = [res.data.payment_gateway]

            if(res.data.params && Object.keys(res.data.params)) {
                params.value = res.data.params
                params_mode.value = true
            }
        }

        expire_time.value = pay_window.value.holding_able_at
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
                    <VCol v-if="pay_module?.pay_window_secure_level > 1 && sign_in_result === false" style="padding: 3em 1em;">
                        <br>
                        <h4 style="text-align: center;">전달받은 PIN번호를 입력해주세요.</h4>                        
                        <div style=" padding: 1em;text-align: center;">
                            <div ref="ref_opt_comp" class="d-flex align-center gap-4">
                                <VTextField v-for="i in 6" :key="i" :model-value="digits[i - 1]" 
                                    v-bind="defaultStyle" maxlength="1" @input="handleKeyDownEvent(i)" />
                            </div>
                        </div>
                    </VCol>
                    <VCol cols="12" v-else-if="pay_module?.module_type > 0" class="d-flex justify-center align-center">
                        <div :style="$vuetify.display.smAndDown ? '' : 'min-width: 700px; max-width: 700px;'">
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
                                v-else-if="pay_module?.module_type === 2 || pay_module?.module_type === 3"
                                :pay_module="pay_module" 
                                :merchandise="merchandise"
                                :pay_window="pay_window"
                            />
                        </div>
                    </VCol>
                    <VCol v-else-if="code !== 200" style="padding: 3em;">
                        <div style="text-align: center;">
                            <VIcon size="40" icon="line-md:emoji-frown-twotone" color="error"/>                        
                        </div>
                        <br>
                        <h3 style="text-align: center;">결제창을 사용할 수 없습니다. </h3>
                        <div style=" padding: 1em;text-align: center;">
                            <h4>- {{ message }} -</h4>
                        </div>
                    </VCol>
                    <VCol v-else-if="pay_module?.module_type === 0" style="padding: 3em;">
                        <b style="text-align: center;">결제창을 로딩하고 있습니다...</b>
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
