<script setup lang="ts">
import { pinInputEvent } from '@/@core/utils/pin_input_event';
import SalesSlipDialog from '@/layouts/dialogs/transactions/SalesSlipDialog.vue';
import router from '@/router';
import AuthPayOverview from '@/views/pay/AuthPayOverview.vue';
import HandPayOverview from '@/views/pay/HandPayOverview.vue';
import { payWindowStore } from '@/views/quick-view/payWindowStore';
import type { Merchandise, PayGateway, PayModule, PayWindow } from '@/views/types';
import { axios } from '@axios';
import { hourTimer } from '@core/utils/timer';
import corp from '@corp';

const route = useRoute()
const { isVisiableRemainTime, getPayWindow } = payWindowStore()
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
    const [_code, _message, _params_mode, _data] = await getPayWindow(route.params.window, route.query.pc)
    if(_code === 200) {
        params_mode.value = _params_mode
        pay_window.value = _data.pay_window
        pay_module.value = _data.payment_module
        merchandise.value = _data.merchandise
        payment_gateways.value = [_data.payment_gateway]
        if(params_mode.value)
            params.value = _data.params

        expire_time.value = pay_window.value.holding_able_at
        const intervalId = setInterval(updateRemainingTime, 1002);
    }
    else {
        code.value = _code
        message.value = _message
        snackbar.value.show(_message, 'error')
    }
})
</script>
<template>
    <section>
        <VCard rounded>
            <VCardText style="padding: 0.5em;">
                <div style="position: absolute; display: flex; width: 100%; justify-content: space-between;" v-if="pay_module?.module_type > 0 || code !== 200">
                    <div style="display: inline-flex; flex-direction: column;" v-if="isVisiableRemainTime(pay_module)">
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
                    <VCol v-if="(pay_module?.pay_window_secure_level === 2 || pay_module?.pay_window_secure_level === 4) && sign_in_result === false" style="padding: 3em 1em;">
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
        <SalesSlipDialog ref="salesslip" :pgs="payment_gateways" :key="payment_gateways.length"/>
    </section>
</template>
