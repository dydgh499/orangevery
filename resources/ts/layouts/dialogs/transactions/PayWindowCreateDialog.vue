<script setup lang="ts">
import { payWindowStore } from '@/views/quick-view/payWindowStore';
import { PayModule } from '@/views/types';
import corp from '@corp';
import { requiredValidatorV2 } from '@validators';
import { cloneDeep } from 'lodash';

const alert = <any>(inject('alert'))

const visible = ref(false)

const vForm = ref()
const pay_info = reactive({
    amount : 0,
    item_name : '',
    buyer_name : '',
    buyer_phone : '',
})
const is_sms_link = ref()
const format_amount = ref('0')
const payment_module = ref()
const url = ref()

const { copy, send, getPayWindowUrl, renewPayWindow } = payWindowStore()

const show = async (_payment_module: PayModule) => {
    payment_module.value = _payment_module
    visible.value = true
}

const submit = async () => {
    const is_valid = await vForm.value.validate()
    const message = is_sms_link.value ? '결제링크를 SMS 전송' : '결제 링크를 생성'
    
    if (is_valid.valid && await alert.value.show('정말 ' + message + '하시겠습니까?')) {
        const res = await renewPayWindow(payment_module.value, pay_info)
        payment_module.value.pay_window = res.data
    
        url.value = getPayWindowUrl(payment_module.value, res.data.param_code)
        if (is_sms_link.value) 
            await send({'url': url.value, ...cloneDeep(pay_info)}, pay_info.buyer_phone)
        else 
            copy(url.value)
    }
}

const formatAmount = computed(() => {
    const parse_amount = parseFloat(format_amount.value.replace(/,/g, "")) || 0;
    pay_info.amount = parse_amount
    format_amount.value = parse_amount.toLocaleString()
})


defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" max-width="600">
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="visible = false" />
        <!-- Dialog Content -->
        <VCard title="결제링크 생성하기">
            <VCardText>
                <VForm ref="vForm">
                    <VCol cols="12">
                        <template v-if="payment_module.module_type === 1">
                            <h4>수기결제창은 생성 후 1시간동안 유효합니다.</h4>
                        </template>
                        <VRow class="pt-5">
                            <VCol md="12" cols="12" :style="$vuetify.display.smAndDown ? '' : 'padding: 0 12px;'">
                                <VRow no-gutters style="min-height: 4em;">
                                    <VCol cols="12" :md="4">
                                        <label>상품명</label>
                                    </VCol>
                                    <VCol cols="12" :md="8">
                                        <VTextField v-model="pay_info.item_name" name="item_name"
                                            prepend-icon="tabler:shopping-bag"
                                            maxlength="100" 
                                            variant="underlined"
                                            :rules="[requiredValidatorV2(pay_info.item_name, '상품명')]" 
                                            placeholder="상품명을 입력해주세요" />
                                    </VCol>
                                </VRow>
                            </VCol>
                        </VRow>
                        <VRow>
                            <VCol md="12" cols="12" :style="$vuetify.display.smAndDown ? '' : 'padding: 0 12px;'">
                                <VRow no-gutters style="min-height: 4em;">
                                    <VCol cols="12" :md="4">
                                        <label>구매자명</label>
                                    </VCol>
                                    <VCol cols="12" :md="8">
                                        <VTextField v-model="pay_info.buyer_name" name="buyer_name"
                                            variant="underlined"
                                            placeholder="구매자명을 입력해주세요" 
                                            :rules="[requiredValidatorV2(pay_info.buyer_name, '구매자명')]" 
                                            prepend-icon="tabler-user" />
                                    </VCol>
                                </VRow>
                            </VCol>
                        </VRow>
                        <VRow>
                            <VCol md="12" cols="12" :style="$vuetify.display.smAndDown ? '' : 'padding: 0 12px;'">
                                <VRow no-gutters style="min-height: 4em;">
                                    <VCol cols="12" :md="4">
                                        <label>구매자 연락처</label>
                                    </VCol>
                                    <VCol cols="12" :md="8">
                                        <VTextField 
                                            v-model="pay_info.buyer_phone" 
                                            type="number" 
                                            name="buyer_phone"
                                            variant="underlined"
                                            prepend-icon="tabler-device-mobile" placeholder="구매자 연락처를 입력해주세요"
                                            :rules="[requiredValidatorV2(pay_info.buyer_phone, '구매자 연락처')]" />
                                    </VCol>
                                </VRow>
                            </VCol>
                        </VRow>
                        <VRow>
                            <VCol md="12" cols="12" ::style="$vuetify.display.smAndDown ? '' : 'padding: 0 12px;'">
                                <VRow no-gutters style="min-height: 4em;">
                                    <VCol cols="12" :md="4">
                                        <label>상품금액</label>
                                    </VCol>
                                    <VCol cols="12" :md="8">
                                        <VTextField 
                                        v-model="format_amount" 
                                            suffix="₩" 
                                            name="amount"
                                            @input="formatAmount"
                                            variant="underlined"
                                            placeholder="상품금액을 입력해주세요" prepend-icon="ic:outline-price-change"
                                            :rules="[requiredValidatorV2(pay_info.amount, '상품금액')]" />
                                    </VCol>
                                </VRow>
                            </VCol>
                        </VRow>
                        <VRow v-if="url">
                            <VCol md="12" cols="12" :style="$vuetify.display.smAndDown ? '' : 'padding: 0 12px;'">
                                <VRow no-gutters style="min-height: 4em;">
                                    <VCol cols="12" :md="4">
                                        <b>결제창 주소</b>
                                    </VCol>
                                    <VCol cols="12" :md="8">
                                        <label>{{ url }}</label>
                                    </VCol>
                                </VRow>
                            </VCol>
                        </VRow>

                        <VRow v-if="corp.pv_options.paid.use_hand_pay_sms">
                            <VCheckbox v-model="is_sms_link" 
                                class="check-label" 
                                label="구매자 연락처로 링크 전송하기" 
                                style="margin-left: auto;"/>
                        </VRow>
                    </VCol>
                </VForm>
            </VCardText>
            <VCardText class="d-flex justify-end gap-3 flex-wrap">
                <VBtn color="secondary" variant="tonal" @click="visible = false">
                    취소
                </VBtn>
                <VBtn @click="submit()">생성하기</VBtn>
            </VCardText>
        </VCard>
    </VDialog>
</template>
<style scoped>
:deep(.v-row) {
  align-items: center;
}
</style>
