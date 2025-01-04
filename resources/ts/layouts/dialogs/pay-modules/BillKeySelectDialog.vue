<script lang="ts" setup>

import BillKeyCreateDialog from '@/layouts/dialogs/pay-modules/BillKeyCreateDialog.vue';
import BillKeyModifyDialog from '@/layouts/dialogs/pay-modules/BillKeyModifyDialog.vue';
import { BillKey, HandPay, Merchandise } from '@/views/types';
import { axios } from '@axios';

const snackbar = <any>(inject('snackbar'))
const auth_token = <any>(inject('auth_token'))

const visible = ref(false)
const bill_keys = ref(<BillKey[]>([]))
const merchandise = ref(<Merchandise>({ id: -1 }))
const hand_pay_info = ref(<HandPay>{})
const pay_window = ref(<string>(''))

const billKeyCreateDialog = ref()
const billKeyModifyDialog = ref()
const mobileVerfication = ref()
let resolveCallback: (bill_key: BillKey | null) => void;

const show = (_pay_window: string, _hand_pay_info: HandPay) => {
    if (_hand_pay_info.buyer_name.trim() === '' && _hand_pay_info.buyer_phone.trim() === '')
        snackbar.value.show('구매자명 및 연락처 정보를 입력해주세요.', 'warning')
    else {
        hand_pay_info.value = _hand_pay_info
        pay_window.value = _pay_window
        visible.value = true
    }

    return new Promise<BillKey | null>((resolve) => {
        resolveCallback = resolve;
    });
}

const processAuth = async (token: string) => {
    // 본인인증 시퀀스
    if(token.length >= 10) {
        auth_token.value = token
        await getCardInfo()
    }
}

const getCardInfo = async () => {
    const res = await axios.get(`/api/v1/pay/${pay_window.value}/bill-keys`, {
        params: {
            token: auth_token.value,
            buyer_name: hand_pay_info.value.buyer_name,
            buyer_phone: hand_pay_info.value.buyer_phone,
        }
    })
    bill_keys.value = res.data
}

const addCardInfo = async () => {
    const res = await billKeyCreateDialog.value.show(true, hand_pay_info.value, pay_window.value)
    if (res)
        await getCardInfo()
}

const modifyCardInfo = async (bill_key: BillKey) => {
    const res = await billKeyModifyDialog.value.show(bill_key, pay_window.value)
    if (res)
        await getCardInfo()
}

const selectCardInfo = (bill_key: BillKey) => {
    visible.value = false
    resolveCallback(bill_key)
}

const onCancel = () => {
    visible.value = false
    resolveCallback(null)
};

const isClearAuth = computed(() => {
    return auth_token.value.length >= 10
})

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" persistent max-width="450">
        <DialogCloseBtn @click="onCancel" />
        <VCard>
            <VCardText v-if="isClearAuth === false">
                <span class="text-base">
                    입력하신 <b>구매자정보</b>로 본인인증을 진행합니다.
                </span>
                <VDivider style="margin: 1em 0;" />
                <MobileVerification block :totalInput="6" :phone_num="hand_pay_info.buyer_phone"
                    :merchandise="merchandise" @update:token="processAuth($event)" ref="mobileVerfication" />
            </VCardText>
            <template v-else>
                <VCardText>
                    <span class="text-base">
                        빌키결제에 사용할 카드를 선택해주세요.
                    </span>
                </VCardText>
                <VDivider />
                <VCardText v-for="(bill_key, index) in bill_keys" :key="index" class="d-flex justify-end gap-3 flex-wrap">
                        <VBtn 
                            @click="selectCardInfo(bill_key)" 
                            variant="tonal"
                            style="justify-content: space-evenly;">
                            <VIcon style="margin-right: 1em;" icon="tabler:credit-card" />
                            <span style="margin-right: 1em;">{{ bill_key.issuer }}</span>
                            <span class="card-num">{{ bill_key.card_num }}</span>
                        </VBtn>
                        <VBtn @click="modifyCardInfo(bill_key)" variant="outlined">수정</VBtn>
                </VCardText>
                <VDivider />
                <VCardText>
                    <VBtn block @click="addCardInfo()">
                        <VIcon style="margin-right: 1em;" icon="tabler:cards" />
                        카드등록하기
                    </VBtn>
                </VCardText>
            </template>
        </VCard>
    </VDialog>
    <BillKeyCreateDialog ref="billKeyCreateDialog" />
    <BillKeyModifyDialog ref="billKeyModifyDialog" />
</template>
<style scoped>
.card-num {
  overflow: hidden;
  inline-size: 10em;
  text-overflow: ellipsis;
  white-space: nowrap;
  word-break: break-all;
}

@media (max-width: 460px) {
  .card-num {
    inline-size: 8em;
  }
}

@media (max-width: 430px) {
  .card-num {
    inline-size: 6em;
  }
}

@media (max-width: 400px) {
  .card-num {
    inline-size: 4em;
  }
}

@media (max-width: 370px) {
  .card-num {
    inline-size: 2em;
  }
}

:deep(.v-row) {
  align-items: center;
}

</style>
