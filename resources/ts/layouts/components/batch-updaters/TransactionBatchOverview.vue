<script lang="ts" setup>

import { batch } from '@/layouts/components/batch-updaters/batch'
import FeeBookDialog from '@/layouts/dialogs/users/FeeBookDialog.vue'
import PasswordAuthDialog from '@/layouts/dialogs/users/PasswordAuthDialog.vue'
import CheckAgreeDialog from '@/layouts/dialogs/utils/CheckAgreeDialog.vue'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { useSalesFilterStore } from '@/views/salesforces/useStore'
import { notiSendHistoryInterface } from '@/views/transactions/transactions'
import { getIndexByLevel, getUserLevel } from '@axios'
import { getAllPayModules, payModFilter } from '@/views/merchandises/pay-modules/useStore'

import corp from '@corp'
import { PayModule } from '@/views/types'

interface Props {
    selected_idxs: number[],
}

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

const { cus_filters, terminals } = useStore()
const { sales, mchts } = useSalesFilterStore()
const { notiSend, notiSelfSend } = notiSendHistoryInterface()

const store = <any>(inject('store'))
const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))

const pay_modules = ref<PayModule[]>([])
const transaction = reactive<any>({
    terminal_id: null,
    custom_id: null,
    settle_dt: null,
    mid: '',
    tid: '',
    mcht_id: null,
    pmod_id: null,
    mcht_fee: 0,
    hold_fee: 0,
    sales0_fee: 0,
    sales1_fee: 0,
    sales2_fee: 0,
    sales3_fee: 0,
    sales4_fee: 0,
    sales5_fee: 0,
})
const levels = corp.pv_options.auth.levels

const setMcht = async (apply_type: number) => {
    if(transaction.pmod_id && transaction.mcht_id) {
        const pmod = pay_modules.value.find(obj => obj.id === transaction.pmod_id)
        post(`merchandises/set-mcht`, {
            'mcht_id': transaction.mcht_id,
            'pmod_id': transaction.pmod_id,
            'module_type': pmod?.module_type
        }, apply_type)
    }
    else
        snackbar.value.show('결제모듈과 가맹점을 선택해주세요.', 'warning')
}

const setMchtFee = async (apply_type: number) => {
    post(`merchandises/set-fee`, {
        'mcht_fee': parseFloat(transaction.mcht_fee),
        'hold_fee': parseFloat(transaction.hold_fee),
    }, apply_type)
}

const setSalesFee = async (sales_idx: number, apply_type: number) => {
    if (await alert.value.show('<b>영업라인 및 수수료율 변경시 변경될 수수료율로인해 정산금액이 변경될 수 있습니다.</b>')) {
        post(`salesforces/set-fee`, {
            'sales_fee': parseFloat(transaction['sales' + sales_idx + "_fee"]),
            'sales_id': transaction['sales' + sales_idx + "_id"],
            'level': getIndexByLevel(sales_idx),
        }, apply_type)
    }
}

const setSettleDay = async (apply_type: number) => {
    for (let i = 0; i < selected_idxs.value.length; i++) {
        let trans = store.getItems.find(obj => obj.id === selected_idxs.value[i])
        if (trans?.mcht_settle_id) {
            snackbar.value.show('이미 정산이 완료된 거래건을 선택하셨습니다.<br>정산이 완료된 건을 해제한 후 다시시도해주세요.', 'warning')
            return
        }
    }
    post('change-settle-date', {
        settle_dt: transaction.settle_dt,
    }, apply_type)
}

const setTerminalId = (apply_type: number) => {
    post('set-terminal-id', {
        'terminal_id': transaction.terminal_id,
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

const setMid = (apply_type: number) => {
    post('set-mid', {
        'mid': transaction.mid,
    }, apply_type)
}

const setTid = (apply_type: number) => {
    post('set-tid', {
        'tid': transaction.tid,
    }, apply_type)
}

const notiReSend = async () => {
    await notiSend(selected_idxs.value);
    emits('update:select_idxs', [])
}

const filterPayMod = computed(() => {
    const filter = pay_modules.value.filter((obj: PayModule) => { return obj.mcht_id == transaction.mcht_id })
    transaction.pmod_id = payModFilter(pay_modules.value, filter, transaction.pmod_id as number)
    return filter
})

watchEffect(() => {
    selected_idxs.value = props.selected_idxs
    selected_sales_id.value = null
    selected_level.value = null
})
onMounted(async () => {
    pay_modules.value = await getAllPayModules()
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
                    <h4 class="pt-3">상위 영업라인 일괄변경</h4>
                    <br>
                    <VRow no-gutters style="align-items: center; margin-bottom: 0.5em;">
                        <template v-for="i in 6" :key="i">
                            <template
                                v-if="levels['sales' + (6 - i) + '_use'] && getUserLevel() >= getIndexByLevel(6 - i)">
                                <VCol md="4" cols="12" style="margin-bottom: 0.5em;">
                                    <div class="batch-container">
                                        <VAutocomplete :menu-props="{ maxHeight: 400 }"
                                            v-model="transaction['sales' + (6 - i) + '_id']" :items="sales[6 - i].value"
                                            item-title="sales_name" item-value="id"
                                            :label="`${levels['sales' + (6 - i) + '_name']} 선택`" />
                                        <VTextField v-model="transaction['sales' + (6 - i) + '_fee']" type="number"
                                            suffix="%" :label="`수수료율`" style="max-width: 8em;" />
                                    </div>
                                </VCol>
                                <VCol md="2" cols="12" style="margin-bottom: 0.5em;">
                                    <div class="button-cantainer" style="margin-right: 0.5em;">
                                        <VBtn variant="tonal" size="small" @click="setSalesFee(6 - i, 0)">
                                            즉시적용
                                            <VIcon end size="18" icon="tabler-direction-sign" />
                                        </VBtn>
                                    </div>
                                </VCol>
                            </template>
                        </template>
                    </VRow>
                    <h4 class="pt-3">가맹점 일괄변경</h4>
                    <br>
                    <VRow>
                        <VCol :md="6" :cols="12">
                            <VRow no-gutters style="align-items: center;">
                                <VCol md="8" cols="12">
                                    <div class="batch-container">
                                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="transaction.mcht_id"
                                            style="max-width: 12em;"
                                            :items="mchts" item-title="mcht_name" item-value="id" :label="`가맹점 선택`" />
                                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="transaction.pmod_id"
                                            :items="filterPayMod" prepend-inner-icon="ic-outline-send-to-mobile"
                                            style="max-width: 12em;"
                                            label="결제모듈 선택" item-title="note" item-value="id" single-line />
                                    </div>
                                </VCol>
                                <VCol md="4" cols="12">
                                    <div class="button-cantainer">
                                        <VBtn variant="tonal" size="small" @click="setMcht(0)">
                                            즉시적용
                                            <VIcon end size="18" icon="tabler-direction-sign" />
                                        </VBtn>
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol :md="6" :cols="12">
                            <VRow no-gutters style="align-items: center;">
                                <VCol md="8" cols="12">
                                    <div class="batch-container">
                                        <VTextField v-model="transaction.mcht_fee" type="number" suffix="%"
                                            :label="`거래 수수료율`" />
                                        <VTextField v-model="transaction.hold_fee" type="number" suffix="%"
                                            :label="`유보금 수수료율`" />
                                    </div>
                                </VCol>
                                <VCol md="4" cols="12">
                                    <div class="button-cantainer">
                                        <VBtn variant="tonal" size="small" @click="setMchtFee(0)">
                                            즉시적용
                                            <VIcon end size="18" icon="tabler-direction-sign" />
                                        </VBtn>
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VDivider style="margin: 1em 0;" />
                    <h4 class="pt-3">개인정보 일괄변경</h4>
                    <br>
                    <VRow no-gutters style="align-items: center;">
                        <VCol md="3" cols="8">
                            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="transaction.terminal_id"
                                :items="[{ id: null, type: 0, name: '사용안함' }].concat(terminals)" label="장비타입"
                                item-title="name" item-value="id" />
                        </VCol>
                        <VCol md="3" cols="4">
                            <div class="button-cantainer">
                                <VBtn variant="tonal" size="small" @click="setTerminalId(0)"
                                    style="margin-right: 0.5em;">
                                    즉시적용
                                    <VIcon end size="18" icon="tabler-direction-sign" />
                                </VBtn>
                            </div>
                        </VCol>
                        <VCol md="3" cols="8">
                            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="transaction.custom_id"
                                :items="[{ id: null, type: 1, name: '사용안함' }].concat(cus_filters)" label="커스텀 필터"
                                item-title="name" item-value="id" />
                        </VCol>
                        <VCol md="3" cols="4">
                            <div class="button-cantainer">
                                <VBtn variant="tonal" size="small" @click="setCustomFilter(0)"
                                    style="margin-right: 0.5em;">
                                    즉시적용
                                    <VIcon end size="18" icon="tabler-direction-sign" />
                                </VBtn>
                            </div>
                        </VCol>
                    </VRow>
                    <VRow no-gutters style="align-items: center;">
                        <VCol md="3" cols="8">
                            <VTextField v-model="transaction.mid" label="MID" />
                        </VCol>
                        <VCol md="3" cols="4">
                            <div class="button-cantainer">
                                <VBtn variant="tonal" size="small" @click="setMid(0)" style="margin-right: 0.5em;">
                                    즉시적용
                                    <VIcon end size="18" icon="tabler-direction-sign" />
                                </VBtn>
                            </div>
                        </VCol>
                        <VCol md="3" cols="8">
                            <VTextField v-model="transaction.tid" label="TID" />
                        </VCol>
                        <VCol md="3" cols="4">
                            <div class="button-cantainer">
                                <VBtn variant="tonal" size="small" @click="setTid(0)" style="margin-right: 0.5em;">
                                    즉시적용
                                    <VIcon end size="18" icon="tabler-direction-sign" />
                                </VBtn>
                            </div>
                        </VCol>
                    </VRow>
                    <VDivider style="margin: 0.5em 0;" />
                    <h4 class="pt-3">정산 예정일 일괄변경</h4>
                    <br>
                    <VRow no-gutters style="align-items: center;">
                        <VCol md="3" cols="8">
                            <VTextField v-model="transaction.settle_dt" type="date" label="정산예정일" />
                        </VCol>
                        <VCol md="3" cols="4">
                            <div class="button-cantainer">
                                <VBtn variant="tonal" size="small" @click="setSettleDay(0)"
                                    style="margin-right: 0.5em;">
                                    즉시적용
                                    <VIcon end size="18" icon="tabler-direction-sign" />
                                </VBtn>
                            </div>
                        </VCol>
                    </VRow>
                    <template v-if="getUserLevel() >= 35">
                        <VDivider style="margin: 1em 0;" />
                        <VRow>
                            <VCol cols="12" md="6" style="display: flex; flex-direction: column;"
                                v-if="corp.pv_options.paid.use_noti">
                                <h4 class="pt-3">노티 재발송</h4>
                                <br>
                                <div style="display: flex; flex-direction: row; justify-content: space-between;">
                                    <VBtn prepend-icon="gridicons:reply" @click="notiReSend()" size="small">
                                        재발송
                                    </VBtn>
                                    <VBtn prepend-icon="gridicons:reply"
                                        @click="notiSelfSend(selected_idxs); emits('update:select_idxs', [])"
                                        v-if="getUserLevel() === 50" size="small" color="info">
                                        개발사 재발송
                                    </VBtn>
                                </div>
                            </VCol>
                            <VCol cols="12" md="6" style="display: flex; flex-direction: column;">
                                <h4 class="pt-3">부가기능</h4>
                                <br>
                                <div style="display: flex; flex-direction: row; justify-content: space-between;">
                                    <VBtn prepend-icon="tabler:column-remove" @click="removeDepositFee(0)" size="small"
                                        color="primary">
                                        건별 수수료 삭제
                                    </VBtn>
                                </div>
                            </VCol>
                        </VRow>
                    </template>

                </div>
            </VCardText>
        </VCard>
        <FeeBookDialog ref="feeBookDialog" />
        <CheckAgreeDialog ref="checkAgreeDialog" />
        <PasswordAuthDialog ref="passwordAuthDialog" />
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
