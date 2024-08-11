<script setup lang="ts">
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import { axios } from '@axios'
import { requiredValidatorV2 } from '@validators'

const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))

const visible = ref(false)
const title = ref()
const button_text = ref()
const dialog_type = ref()
const base_url = ref()

const vForm = ref()
const item_name = ref()
const buyer_name = ref()
const amount = ref()
const phone_num = ref()

const show = (_dialog_type: string, _base_url: string) => {
    base_url.value = _base_url
    dialog_type.value = _dialog_type

    if (dialog_type.value == 'PAY-LINK') {
        title.value = '결제 링크 생성하기'
        button_text.value = '생성하기'
    }
    else {
        title.value = 'SMS 결제 전송하기'
        button_text.value = '전송하기'
    }
    visible.value = true
}

const submit = async () => {
    const is_valid = await vForm.value.validate()
    const message = dialog_type.value == 'PAY-LINK' ? '결제 링크를 생성' : 'SMS 결제 전송을'
    if (is_valid.valid && await alert.value.show('정말 ' + message + '하시겠습니까?')) {
        const p = {
            'item_name' : item_name.value,
            'buyer_name': buyer_name.value,
            'amount'    : amount.value,
            'phone_num' : phone_num.value,
        }

        const sub_query = Object.keys(p).map(key => `${encodeURIComponent(key)}=${encodeURIComponent(p[key])}`).join('&')
        const url = base_url.value+"&"+sub_query
        
        if (dialog_type.value == 'PAY-LINK') {
            try {
                const textarea = document.createElement('textarea');
                textarea.value = url;
                textarea.setAttribute('readonly', '');
                textarea.style.position = 'fixed';
                document.body.appendChild(textarea);
                textarea.focus();
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                snackbar.value.show('생성하신 결제링크가 클립보드에 복사되었습니다.', 'success')
            } catch (err) {
                snackbar.value.show('결제링크 복사를 실패하였습니다.', 'error')
            }
        }
        else {
            try {
                const params = {
                    'url': url,
                    ...p,
                }
                const r = await axios.post('/api/v1/bonaejas/sms-link-send', params)
                snackbar.value.show(phone_num.value+'으로 결제링크를 전송하였습니다.', 'success')
            }
            catch (e: any) {
                snackbar.value.show(e.response.data.message, 'error')
                const r = errorHandler(e)
            }
        }
        visible.value = false
    }
}

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" max-width="600">
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="visible = false" />
        <!-- Dialog Content -->
        <VCard :title="title">
            <VCardText>
                <VForm ref="vForm">
                    <VCol cols="12">
                        <VRow class="pt-5">
                            <CreateHalfVCol :mdl="3" :mdr="9">
                                <template #name>상품명</template>
                                <template #input>
                                    <VTextField v-model="item_name"
                                        prepend-inner-icon="tabler:shopping-bag"
                                        maxlength="100" placeholder="상품명을 입력해주세요" persistent-placeholder
                                        :rules="[requiredValidatorV2(item_name, '상품명')]" />
                                </template>
                            </CreateHalfVCol>
                            <CreateHalfVCol :mdl="3" :mdr="9">
                                <template #name>구매자명</template>
                                <template #input>
                                    <VTextField v-model="buyer_name" placeholder="구매자명을 입력해주세요"
                                        prepend-inner-icon="tabler-user" maxlength="50" :rules="[requiredValidatorV2(buyer_name, '구매자명')]" />
                                </template>
                            </CreateHalfVCol>

                            <CreateHalfVCol :mdl="3" :mdr="9">
                                <template #name>구매자 연락처</template>
                                <template #input>
                                    <VTextField v-model="phone_num" placeholder="구매자 연락처를 입력해주세요"
                                        prepend-inner-icon="tabler-device-mobile" maxlength="20"
                                        :rules="[requiredValidatorV2(phone_num, '구매자 연락처')]" />
                                </template>
                            </CreateHalfVCol>

                            <CreateHalfVCol :mdl="3" :mdr="9">
                                <template #name>결제금액</template>
                                <template #input>
                                    <VTextField v-model="amount" type="number" suffix="￦"
                                        placeholder="결제금액을 입력해주세요" prepend-inner-icon="ic:outline-price-change"
                                        :rules="[requiredValidatorV2(amount, '결제금액')]" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                    </VCol>
                </VForm>
            </VCardText>
            <VCardText class="d-flex justify-end gap-3 flex-wrap">
                <VBtn color="secondary" variant="tonal" @click="visible = false">
                    취소
                </VBtn>
                <VBtn @click="submit()">
                    {{ button_text }}
                </VBtn>
            </VCardText>
        </VCard>
    </VDialog>
</template>
