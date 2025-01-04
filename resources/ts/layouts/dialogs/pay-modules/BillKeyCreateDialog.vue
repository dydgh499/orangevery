<script lang="ts" setup>
import CardOverview from '@/layouts/components/pay-windows/CardOverview.vue';
import CommonOverview from '@/layouts/components/pay-windows/CommonOverview.vue';
import { axios } from '@/plugins/axios';
import { UserPayInfo } from '@/views/types';
import { PayParamTypes } from '@core/enums';
import { VForm } from 'vuetify/components';

interface BillKeyCreate extends UserPayInfo {
    pmod_id: number | null,
    card_num: string,
    yymm: string,
    auth_num: string,
    card_pw: string,
    ord_num: string,
}

const vForm = ref<VForm>()
const visible = ref(false)

const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))
const auth_token = <any>(inject('auth_token'))

const bill_key = ref(<BillKeyCreate>{})
const is_pay_window = ref(true)
const pay_window = ref('')

let resolveCallback: (isAgreed: boolean) => void;
const show = (_is_pay_window: boolean, _hand_pay_info: BillKeyCreate, _pay_window: string) => {
    pay_window.value = _pay_window
    is_pay_window.value = _is_pay_window
    bill_key.value = _hand_pay_info
    visible.value = true

    return new Promise<boolean>((resolve) => {
        resolveCallback = resolve;
    });
}

const submit = async () => {
    const is_valid = await vForm.value.validate()
    if (is_valid.valid) {
        if (await alert.value.show('정말 빌키를 생성하시겠습니까?')) {
            axios.post(`/api/v1/pay/${pay_window.value}/bill-keys`, 
                Object.assign(bill_key.value, {token: auth_token.value})
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

const onAgree = () => {
    visible.value = false
    resolveCallback(true)
};

const onCancel = () => {
    visible.value = false
    resolveCallback(false)
};

watchEffect(() => {
    bill_key.value.ord_num = bill_key.value.pmod_id + "BC" + Date.now().toString().substr(0, 10)
})

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" persistent max-width="800">
        <DialogCloseBtn @click="visible = onCancel()" />
        <VCard title="빌키 생성">
            <VCardText>
                <VForm ref="vForm">
                    <VCol style="padding: 0 12px;">
                        <CommonOverview 
                            :user_pay_info="bill_key"
                            :params_mode="is_pay_window ? PayParamTypes.SMS : 0" 
                            :params="is_pay_window ? bill_key : {}" 
                        />
                        <CardOverview :hand_pay_info="bill_key" :is_old_auth="1" />
                    </VCol>
                </VForm>
            </VCardText>
            <VDivider />
            <VCardText class="d-flex justify-end gap-3 flex-wrap">
                <VBtn @click="submit()">
                    생성하기
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
