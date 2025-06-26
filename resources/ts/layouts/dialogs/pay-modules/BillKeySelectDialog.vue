<script lang="ts" setup>

import BillKeyCreateDialog from '@/layouts/dialogs/pay-modules/BillKeyCreateDialog.vue';
import { operatorActionAuthStore } from '@/views/services/operators/useStore'
//import BillKeyModifyDialog from '@/layouts/dialogs/pay-modules/BillKeyModifyDialog.vue';
import { BillKey, HandPay, Merchandise } from '@/views/types';
import { axios } from '@axios';

const snackbar = <any>(inject('snackbar'))
const auth_token = <any>(inject('auth_token'))

const visible = ref(false)
const bill_keys = ref(<BillKey[]>([]))
const merchandise = ref(<Merchandise>({ id: -1 }))
const hand_pay = ref(<HandPay>{})
const pay_window = ref(<string>(''))
const request_at = ref('')
const { headOfficeAuthValidate } = operatorActionAuthStore()

const store = <any>(inject('store'))

const billKeyCreateDialog = ref()
//const billKeyModifyDialog = ref()
const mobileVerfication = ref()
let resolveCallback: (bill_key: BillKey | null) => void;

const show = (/*_pay_window: string,*/ _hand_pay: HandPay) => {
    /*
    if(_hand_pay.buyer_name.toString().trim() === '')
        snackbar.value.show('구매자명을 입력해주세요.', 'warning')
    else if(_hand_pay.buyer_phone.toString().trim() === '')
        snackbar.value.show('연락처를 입력해주세요.', 'warning')
    else if(_hand_pay.resident_num_front?.toString().trim() === '')
    snackbar.value.show('생년월일을 입력해주세요.', 'warning')
    else {
        hand_pay.value = _hand_pay
        pay_window.value = _pay_window
        visible.value = true
    }

    return new Promise<BillKey | null>((resolve) => {
        resolveCallback = resolve;
    });
    */
        visible.value = true
}

const mobileVerfy = async () => {
    addCardInfo()
    /*
    const [result, token] = await headOfficeAuthValidate('빌키를 추가하기 위해 휴대폰번호 인증이 필요합니다.<br>계속하시겠습니까?')
    if(result) {
        work_time.value.token = token
        const res = await update(`/services/exception-work-times`, work_time.value, vForm.value, false)
        if(res.status === 201) {
            store.setTable()
            visible.value = false
            resolveCallback(true); // 동의 버튼 누름        
        }
    }
    */
};

const processAuth = async (token: string) => {
    // 본인인증 시퀀스
    if(token.length >= 10) {
        auth_token.value = token
        request_at.value = getYmdHis()
        await getCardInfo()
    }
}

const getCardInfo = async () => {
    const res = await axios.get(`/api/v1/pay/${pay_window.value}/bill-keys`, {
        params: {
            token: auth_token.value,
            buyer_name: hand_pay.value.buyer_name,
            buyer_phone: hand_pay.value.buyer_phone,
            resident_num_front: hand_pay.value.resident_num_front,
            request_at: request_at.value,
        }
    })
    bill_keys.value = res.data
}

const addCardInfo = async () => {
    request_at.value = getYmdHis()
    const res = await billKeyCreateDialog.value.show(/*hand_pay.value, pay_window.value,*/ request_at.value)
    /*
    if (res)
        await getCardInfo()
    */
}
/*
const modifyCardInfo = async (bill_key: BillKey) => {
    const res = await billKeyModifyDialog.value.show(bill_key, pay_window.value, request_at.value)
    if (res)
        await getCardInfo()
}
*/
const selectCardInfo = (bill_key: BillKey) => {
    visible.value = false
    resolveCallback(bill_key)
}

const onCancel = () => {
    visible.value = false
    resolveCallback(null)
};

const getYmdHis = () => {
    const date = new Date()
    const year  = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day   = String(date.getDate()).padStart(2, '0')
    const hour  = String(date.getHours()).padStart(2, '0')
    const min   = String(date.getMinutes()).padStart(2, '0')
    const sec   = String(date.getSeconds()).padStart(2, '0')
    const mill  = String(date.getMilliseconds()).padStart(4, '0')
    return `${year}-${month}-${day} ${hour}:${min}:${sec}.${mill}`;
}

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
            <VCardText> <!--v-if="isClearAuth === false"-->
                <span class="text-base">
                    빌키 추가를 위해 <b>휴대폰번호</b> 인증을 진행합니다.
                </span>
                <VDivider style="margin: 1em 0;" />
                <VBtn @click="mobileVerfy()" style="width: 100%;">
                    휴대폰 인증하기
                </VBtn>
            </VCardText>
            <VCardText>
                <VBtn block @click="addCardInfo()">
                    <VIcon style="margin-right: 1em;" icon="tabler:cards" />
                    카드등록하기
                </VBtn>
            </VCardText>
            <!--
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
            -->
        </VCard>
    </VDialog>
    <BillKeyCreateDialog ref="billKeyCreateDialog" />
    <!--<BillKeyModifyDialog ref="billKeyModifyDialog" />-->
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
