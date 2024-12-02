<script setup lang="ts">
import { getAllPayModules } from '@/views/merchandises/pay-modules/useStore';
import { useSalesFilterStore } from '@/views/salesforces/useStore';
import type { PayModule } from '@/views/types';
import { axios } from '@axios';

const { mchts } = useSalesFilterStore()

const mcht_id = ref(null)
const pmod_id = ref(null)

const payment_gateways = <any>(inject('payment_gateways'))
const merchandise = <any>(inject('merchandise'))
const pay_module = <any>(inject('pay_module'))
const pay_window = <any>(inject('pay_window'))

const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))
const pay_modules = ref<PayModule[]>([])

const mchtUpdate = async () => {
    if(mcht_id.value) {
        pmod_id.value = null
        pay_module.value = {}
        pay_modules.value = await getAllPayModules(mcht_id.value)
    }
}

const pmodUpdate = async () => {
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
}

</script>
<template>
    <VCardTitle><b>테스트 가맹점 정보</b></VCardTitle>
    <VCol>
        <VRow>
            <VCol md="6" cols="12">
                <VRow no-gutters>
                    <VCol cols="4" :md="3">
                        <label>가맹점</label>
                    </VCol>
                    <VCol cols="8" :md="9">
                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="mcht_id" :items="mchts"
                            variant="underlined"
                            prepend-icon="tabler-building-store" item-title="mcht_name" item-value="id" single-line
                            placeholder="테스트 가맹점 선택" 
                            @update:modelValue="mchtUpdate"/>
                    </VCol>
                </VRow>
            </VCol>
            <VCol md="6" cols="12">
                <VRow no-gutters>
                    <VCol cols="4" :md="3">
                        <label>결제모듈</label>
                    </VCol>
                    <VCol cols="8" :md="9">
                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="pmod_id" :items="pay_modules"
                            variant="underlined"
                            prepend-icon="ic-outline-send-to-mobile" item-title="note" item-value="id" single-line
                            placeholder="테스트 결제모듈 선택" 
                            @update:modelValue="pmodUpdate"/>
                    </VCol>
                </VRow>
            </VCol>
        </VRow>
        <VCol cols="12" v-if="pmod_id === null">
            <VCard flat rounded>
                <VDivider />
                <VCardText style="padding: 0; text-align: center;">
                    <br>
                    <h4>테스트 할 결제모듈을 선택해주세요.</h4>
                </VCardText>
            </VCard>
        </VCol>
    </VCol>
</template>
<style scoped>
:deep(.v-row) {
  align-items: center;
}
</style>
