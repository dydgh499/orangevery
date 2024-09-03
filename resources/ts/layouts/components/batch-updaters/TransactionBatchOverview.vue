

<script lang="ts" setup>

import { batch } from '@/layouts/components/batch-updaters/batch'
import FeeBookDialog from '@/layouts/dialogs/users/FeeBookDialog.vue'
import PasswordAuthDialog from '@/layouts/dialogs/users/PasswordAuthDialog.vue'
import CheckAgreeDialog from '@/layouts/dialogs/utils/CheckAgreeDialog.vue'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { notiSendHistoryInterface, realtimeHistoryInterface } from '@/views/transactions/transactions'
import { getUserLevel } from '@axios'
import corp from '@corp'

interface Props {
    selected_idxs: number[],
}

const formatTime = <any>(inject('$formatTime'))
const props = defineProps<Props>()
const emits = defineEmits(['update:select_idxs'])
const {
        selected_idxs,
        selected_sales_id,
        selected_level,
        selected_all,
        feeBookDialog,
        checkAgreeDialog,
        passwordAuthDialog,
        post,
        batchRemove
    } = batch(emits, '매출', 'transactions')

const { cus_filters } = useStore()
const { notiBatchSendByTrans } = notiSendHistoryInterface()
const { isRealtimeTransaction, singleDepositCancelJobReservation } = realtimeHistoryInterface(formatTime)

const store = <any>(inject('store'))
const snackbar = <any>(inject('snackbar'))

const transaction = reactive<any>({
    custom_id: null,
    settle_dt: null,
})

const setSettleDay = async (apply_type: number) => {
    for (let i = 0; i < selected_idxs.value.length; i++) {
        let trans = store.getItems.find(obj => obj.id === selected_idxs.value[i])
        if(trans?.mcht_settle_id) {
            snackbar.value.show('이미 정산이 완료된 거래건을 선택하셨습니다.<br>정산이 완료된 건을 해제한 후 다시시도해주세요.', 'warning')
            return
        }
    }
    post('change-settle-date', {
        settle_dt: transaction.settle_dt,
    }, apply_type)
}

const setCustomFilter = (apply_type: number) => {
    post('set-custom-filter', {
        'custom_id': transaction.custom_id,
    }, apply_type)
}

const removeDepositFee = (apply_type: number) => {
    post('remove-deposit-fee', {}, apply_type)
}

watchEffect(() => {
    selected_idxs.value = props.selected_idxs
    selected_sales_id.value = null
    selected_level.value = null
})

</script>
<template>
    <section>
        <VCard title="매출 일괄작업" style="max-height: 55em !important;overflow-y: auto !important;">
            <VCardText>
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <VRadioGroup v-model="selected_all">
                        <VRadio :value="0" @click="">
                            <template #label>
                                <b>선택된 매출 : {{ selected_idxs.length.toLocaleString() }}개</b>
                            </template>
                        </VRadio>
                    </VRadioGroup>
                    <VBtn type="button" color="error" @click="batchRemove()" style="float: inline-end;" size="small">
                        일괄삭제
                        <VIcon size="18" icon="tabler-trash" />
                    </VBtn>
                </div>
                <VDivider style="margin: 1em 0;" />
                <div style="width: 100%;">
                    <VRow>
                        <VCol :md="6" :cols="12">
                            <h4 class="pt-3">개인정보 일괄변경</h4>
                            <br>
                            <VRow no-gutters style="align-items: center;">
                                <VCol md="7" cols="12">
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="transaction.custom_id"
                                            :items="[{ id: null, type: 1, name: '사용안함' }].concat(cus_filters)" label="커스텀 필터"
                                            item-title="name" item-value="id" />
                                </VCol>
                                <VCol md="5" cols="12">
                                    <div class="button-cantainer">
                                        <VBtn variant="tonal" size="small" @click="setCustomFilter(0)">
                                            즉시적용
                                            <VIcon end size="18" icon="tabler-direction-sign" />
                                        </VBtn>
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol :md="6" :cols="12">
                            <h4 class="pt-3">정산 예정일 일괄변경</h4>
                            <br>
                            <VRow no-gutters style="align-items: center;">
                                <VCol md="7" cols="12">
                                    <VTextField v-model="transaction.settle_dt" type="date"
                                       label="정산예정일" />
                                </VCol>
                                <VCol md="5" cols="12">
                                    <div class="button-cantainer">
                                        <VBtn variant="tonal" size="small" @click="setSettleDay(0)">
                                            즉시적용
                                            <VIcon end size="18" icon="tabler-direction-sign" />
                                        </VBtn>
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <template v-if="getUserLevel() >= 35">
                        <VDivider style="margin: 1em 0;" />
                            <VRow>
                                <VCol cols="12" md="6" style="display: flex; flex-direction: column;" v-if="corp.pv_options.paid.use_noti">
                                    <h4 class="pt-3">노티 재발송</h4>
                                    <br>
                                    <div style="display: flex; flex-direction: row; justify-content: space-evenly;">
                                        <VBtn prepend-icon="tabler-calculator" @click="notiBatchSendByTrans(`batch-retry`, selected_idxs, emits)" size="small">
                                            재발송
                                        </VBtn>
                                        <VBtn prepend-icon="tabler-calculator" @click="notiBatchSendByTrans(`batch-self-retry`, selected_idxs, emits)" v-if="getUserLevel() >= 50" size="small" color="info">
                                            개발사 재발송
                                        </VBtn>
                                    </div>
                                </VCol>
                                <template v-if="isRealtimeTransaction() || corp.pv_options.paid.use_collect_withdraw_scheduler">
                                    <VDivider :vertical="$vuetify.display.mdAndUp"/>
                                    <VCol cols="12" md="6" style="display: flex; flex-direction: column;" >
                                        <h4 class="pt-3">부가기능</h4>
                                        <br>
                                        <div style="display: flex; flex-direction: row; justify-content: space-evenly;">
                                            <VBtn prepend-icon="tabler-calculator" @click="singleDepositCancelJobReservation(selected_idxs)" size="small" color="warning"
                                                v-if="isRealtimeTransaction()">
                                                이체예약취소
                                            </VBtn>
                                            <template v-if="corp.pv_options.paid.use_collect_withdraw_scheduler">
                                                <VBtn prepend-icon="tabler:column-remove" @click="removeDepositFee(0)" size="small" color="primary">
                                                    입금 수수료 삭제
                                                </VBtn>
                                            </template>
                                        </div>
                                    </VCol>
                                </template>
                            </VRow>
                    </template>
                    
                </div>
            </VCardText>
        </VCard>
        <FeeBookDialog ref="feeBookDialog"/>
        <CheckAgreeDialog ref="checkAgreeDialog"/>
        <PasswordAuthDialog ref="passwordAuthDialog"/>
    </section>
</template>
<style scoped>
.button-cantainer {
  display: flex;
  padding: 0.25em;
  float: inline-end;
}

:deep(.v-input) {
  padding: 0.25em !important;
}
</style>
