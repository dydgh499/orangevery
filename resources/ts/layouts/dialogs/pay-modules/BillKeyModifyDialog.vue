<script lang="ts" setup>
import { inputFormater } from '@/@core/utils/formatters';
import { axios } from '@/plugins/axios';
import { BillKey } from '@/views/types';
import { requiredValidatorV2 } from '@validators';
import { VForm } from 'vuetify/components';

const vForm = ref<VForm>()
const visible = ref(false)

const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))
const auth_token = <any>(inject('auth_token'))

const bill_key = ref(<BillKey>{})
const pay_window = ref('')
const {
    phone_num_format,
    phone_num,
    formatPhoneNum,
} = inputFormater()

let resolveCallback: (isAgreed: boolean) => void;

const show = (_bill_key: BillKey, _pay_window: string) => {
    phone_num.value = _bill_key.buyer_phone
    phone_num_format.value = _bill_key.buyer_phone.toString()
    pay_window.value = _pay_window
    bill_key.value = _bill_key
    visible.value = true

    return new Promise<boolean>((resolve) => {
        resolveCallback = resolve;
    });
}

const submit = async () => {
    const is_valid = await vForm.value.validate()
    if (is_valid.valid) {
        if (await alert.value.show('정말 수정하시겠습니까?')) {
            axios.post(`/api/v1/pay/${pay_window.value}/bill-keys/${bill_key.value.id}`, 
                Object.assign(bill_key.value, {
                    token: auth_token.value,
                })
            ).then(r => {
                snackbar.value.show('성공하였습니다.', 'success')
                onAgree()
            })
            .catch(async e => {
                if([951, 956, 958].includes(e.response.data.code))
                    auth_token.value = false
                snackbar.value.show(e.response.data.message, 'error')
                const r = errorHandler(e)
            })
        }
    }
}

const remove = async () => {
    if (await alert.value.show('정말 삭제하시겠습니까?')) {
        axios.delete(`/api/v1/pay/${pay_window.value}/bill-keys/${bill_key.value.id}`, {
            params: {
                token: auth_token.value,
                ord_num: bill_key.value.id + "BD" + Date.now().toString().substr(0, 10),
            }
        }).then(r => {
            snackbar.value.show('성공하였습니다.', 'success')
            onAgree()
        })
        .catch(async e => {
            if([951, 956, 958].includes(e.response.data.code))
                auth_token.value = false
            snackbar.value.show(e.response.data.message, 'error')
            const r = errorHandler(e)
        })
    }
}

const onAgree = () => {
    visible.value = false
    resolveCallback(true)
};

const onCancel = () => {
    visible.value = false
    resolveCallback(false)
};

watchEffect(() => {
    bill_key.value.buyer_phone = phone_num.value
})

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" persistent max-width="800">
        <DialogCloseBtn @click="visible = onCancel()" />
        <VCard title="빌키 수정">
            <VCardText>
                <VForm ref="vForm">
                    <VCol style="padding: 0 12px;">
                        <VRow>
                            <VDivider />
                            <VCardTitle style="margin-top: 0.5em;"><b>구매자정보</b></VCardTitle>
                        </VRow>
                        <VRow>
                            <VCol md="6" cols="12" style="padding: 0 12px;">
                                <VRow no-gutters style="min-height: 3.5em;">
                                    <VCol cols="4" :md="4">
                                        <label>구매자명</label>
                                    </VCol>
                                    <VCol cols="8" :md="8">
                                        <VTextField 
                                            v-model="bill_key.buyer_name"
                                            variant="underlined"
                                            placeholder="구매자명을 입력해주세요" :rules="[requiredValidatorV2(bill_key.buyer_name, '구매자명')]" 
                                            prepend-icon="tabler-user" />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol md="6" cols="12" style="padding: 0 12px;">
                                <VRow no-gutters style="min-height: 3.5em;">
                                    <VCol cols="4" :md="4">
                                        <label>연락처</label>
                                    </VCol>
                                    <VCol cols="8" :md="8">
                                        <VTextField 
                                            v-model="phone_num_format" 
                                            @input="formatPhoneNum"
                                            variant="underlined"
                                            prepend-icon="tabler-device-mobile" placeholder="구매자 연락처를 입력해주세요"
                                            :rules="[requiredValidatorV2(bill_key.buyer_phone, '구매자 연락처')]" 
                                            />
                                    </VCol>
                                </VRow>
                            </VCol>
                        </VRow>                        
                    </VCol>
                </VForm>
            </VCardText>
            <VDivider />
            <VCardText class="d-flex justify-end gap-3 flex-wrap">
                <VBtn @click="submit()">
                    수정
                    <VIcon end icon="tabler-pencil" />
                </VBtn>
                <VBtn type="button" color="error" @click="remove()">
                    삭제
                    <VIcon size="22" icon="tabler-trash" />
                </VBtn>
            </VCardText>
        </VCard>
    </VDialog>
</template>
<style scoped>
.table-th,
.table-td {
  border-inline-end: thin solid rgba(var(--v-border-color), var(--v-border-opacity)) !important;
}

:deep(.v-row) {
  align-items: center;
}

.card-pay-th {
  padding: 0.5em;
}

.card-pay-td {
  padding: 0.5em;
}

:deep(.v-table__wrapper) {
  block-size: auto !important;
}
</style>
