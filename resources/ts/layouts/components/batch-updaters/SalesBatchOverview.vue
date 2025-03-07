

<script lang="ts" setup>
import { batch } from '@/layouts/components/batch-updaters/batch'
import FeeBookDialog from '@/layouts/dialogs/users/FeeBookDialog.vue'
import PasswordAuthDialog from '@/layouts/dialogs/users/PasswordAuthDialog.vue'
import CheckAgreeDialog from '@/layouts/dialogs/utils/CheckAgreeDialog.vue'
import { settleCycles, settleDays, settleTaxTypes, authLevels } from '@/views/salesforces/useStore'
import { Salesforce } from '@/views/types'
import { banks } from '@/views/users/useStore'
import { getUserLevel } from '@axios'
import corp from '@corp'

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
    } = batch(emits, '영업점', 'salesforces')
const store = <any>(inject('store'))

const all_cycles = settleCycles()
const all_days = settleDays()
const tax_types = settleTaxTypes()
const view_types = [{id:0, title:'간편보기'}, {id:1, title:'상세보기'}]


const salesforces = reactive<any>({
    settle_tax_type: 0,
    settle_cycle: 0,
    settle_day: 0,
    auth_level: 0,
    view_type: 0,
    note: '',
    acct_num: "",
    acct_name: "",
    acct_bank_name: "",
    acct_bank_code: "000",
})

const setAcctBankName = () => {
    const bank = banks.find(obj => obj.code == salesforces.acct_bank_code)
    salesforces.acct_bank_name = bank ? bank.title : '선택안함'
}

const setSettleTaxType = (apply_type: number) => {
    post('set-settle-tax-type', {
        'settle_tax_type': salesforces.settle_tax_type,
    }, apply_type)
}

const setSettleCycle = (apply_type: number) => {
    post('set-settle-cycle', {
        'settle_cycle': salesforces.settle_cycle,
    }, apply_type)

}

const setSettleDay = (apply_type: number) => {
    post('set-settle-day', {
        'settle_day': salesforces.settle_day,
    }, apply_type)
}

const setIsAbleModifyMcht = (apply_type: number) => {
    post('set-is-able-modify-mcht', {
        'auth_level': salesforces.auth_level,
    }, apply_type)
}

const setViewType = (apply_type: number) => {
    post('set-view-type', {
        'view_type': salesforces.view_type,
    }, apply_type)
}

const setAccountInfo = (apply_type: number) => {
    post('set-account-info', {
        'acct_num': salesforces.acct_num,
        'acct_name': salesforces.acct_name,
        'acct_bank_code': salesforces.acct_bank_code,
        'acct_bank_name': salesforces.acct_bank_name,
    }, apply_type)
}

const setNote = (apply_type: number) => {
    post('set-note', {
        'note': salesforces.note,
    }, apply_type)
}

watchEffect(() => {
    selected_idxs.value = props.selected_idxs
    selected_sales_id.value = null
    selected_level.value = null
})

</script>
<template>
    <section>
        <VCard title="영업점 일괄작업" style="max-height: 55em !important;overflow-y: auto !important;">
            <VCardText>
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <VRadioGroup v-model="selected_all">
                        <VRadio :value="0" @click="">
                            <template #label>
                                <b>선택된 영업점 : {{ props.selected_idxs.length.toLocaleString() }}개</b>
                            </template>
                        </VRadio>
                        <VRadio :value="1" @click="" v-if="corp.pv_options.paid.sales_parent_structure === false && getUserLevel() === 40">
                            <template #label>
                                <b>전체모듈: {{ store.pagenation.total_count }}개</b>
                            </template>
                        </VRadio>
                    </VRadioGroup>
                    <VBtn type="button" color="error" @click="batchRemove()" style="float: inline-end;" size="small">
                        일괄삭제
                        <VIcon size="18" icon="tabler-trash" />
                    </VBtn>
                </div>
                <VDivider style="margin: 1em 0;" />
                <h4 class="pt-3">정산정보 일괄변경</h4>
                <br>
                <div style="width: 100%;">
                    <VRow>
                        <VCol :md="6" :cols="12">
                            <VRow no-gutters style="align-items: center;">
                                <VCol md="6" cols="12">
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="salesforces.settle_tax_type"
                                            :items="tax_types" prepend-inner-icon="icon-park-outline:cycle"
                                            label="정산세율" item-title="title" item-value="id" />
                                </VCol>
                                <VCol md="6">
                                    <div class="button-cantainer">
                                        <VBtn variant="tonal" size="small" @click="setSettleTaxType(0)">
                                            즉시적용
                                            <VIcon end size="18" icon="tabler-direction-sign" />
                                        </VBtn>
                                        <VBtn variant="tonal" size="small" color="secondary" @click="setSettleTaxType(1)"
                                            style='margin-left: 0.5em;'>
                                            예약적용
                                            <VIcon end size="18" icon="tabler-clock-up" />
                                        </VBtn>                 
                                    </div>                                    
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VRow>
                        <VCol :md="6">
                            <VRow no-gutters style="align-items: center;">
                                <VCol md="6" cols="12">
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="salesforces.settle_cycle"
                                        :items="all_cycles" prepend-inner-icon="icon-park-outline:cycle"
                                        label="정산주기" item-title="title" item-value="id"
                                    />
                                </VCol>
                                <VCol md="6">
                                    <div class="button-cantainer">
                                        <VBtn variant="tonal" size="small" @click="setSettleCycle(0)">
                                            즉시적용
                                            <VIcon end size="18" icon="tabler-direction-sign" />
                                        </VBtn>
                                        <VBtn variant="tonal" size="small" color="secondary" @click="setSettleCycle(1)"
                                            style='margin-left: 0.5em;'>
                                            예약적용
                                            <VIcon end size="18" icon="tabler-clock-up" />
                                        </VBtn>                 
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol :md="6" :cols="12">
                            <VRow no-gutters style="align-items: center;">
                                <VCol md="6" cols="12">
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="salesforces.settle_day"
                                        :items="all_days" prepend-inner-icon="icon-park-outline:cycle" label="정산요일"
                                        item-title="title" item-value="id" />
                                </VCol>
                                <VCol md="6">
                                    <div class="button-cantainer">
                                        <VBtn variant="tonal" size="small" @click="setSettleDay(0)">
                                            즉시적용
                                            <VIcon end size="18" icon="tabler-direction-sign" />
                                        </VBtn>
                                        <VBtn variant="tonal" size="small" color="secondary" @click="setSettleDay(1)"
                                            style='margin-left: 0.5em;'>
                                            예약적용
                                            <VIcon end size="18" icon="tabler-clock-up" />
                                        </VBtn>                 
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VDivider style="margin: 1em 0;" />
                    <h4 class="pt-3">영업점정보 일괄변경</h4>
                    <br>
                    <VRow>
                        <VCol :md="6" :cols="12">
                            <VRow no-gutters style="align-items: center;">
                                <VCol md="6" cols="12">
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="salesforces.auth_level" 
                                            :items="authLevels()" item-title="title" item-value="id" label="영업점 권한"/>
                                </VCol>
                                <VCol md="6">
                                    <div class="button-cantainer">
                                        <VBtn variant="tonal" size="small" @click="setIsAbleModifyMcht(0)">
                                            즉시적용
                                            <VIcon end size="18" icon="tabler-direction-sign" />
                                        </VBtn>
                                        <VBtn variant="tonal" size="small" color="secondary" @click="setIsAbleModifyMcht(1)"
                                            style='margin-left: 0.5em;'>
                                            예약적용
                                            <VIcon end size="18" icon="tabler-clock-up" />
                                        </VBtn>                 
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol :md="6">
                            <VRow no-gutters style="align-items: center;">
                                <VCol md="6" cols="12">
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="salesforces.view_type" 
                                            :items="view_types" item-title="title" item-value="id" label="화면타입"/>
                                </VCol>
                                <VCol md="6" style="padding-left: 0;">
                                    <div class="button-cantainer">
                                        <VBtn variant="tonal" size="small" @click="setViewType(0)">
                                            즉시적용
                                            <VIcon end size="18" icon="tabler-direction-sign" />
                                        </VBtn>
                                        <VBtn variant="tonal" size="small" color="secondary" @click="setViewType(1)"
                                            style='margin-left: 0.5em;'>
                                            예약적용
                                            <VIcon end size="18" icon="tabler-clock-up" />
                                        </VBtn>                 
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VRow>
                        <VCol :md="6" :cols="12">
                            <VRow no-gutters style="align-items: center;">
                                <VCol md="6" cols="12">
                                    <VTextField v-model="salesforces.note" label="메모사항"
                                            prepend-inner-icon="twemoji-spiral-notepad" maxlength="300" auto-grow />
                                </VCol>
                                <VCol md="6">
                                    <div class="button-cantainer">
                                        <VBtn variant="tonal" size="small" @click="setNote(0)">
                                            즉시적용
                                            <VIcon end size="18" icon="tabler-direction-sign" />
                                        </VBtn>
                                        <VBtn variant="tonal" size="small" color="secondary" @click="setNote(1)"
                                            style='margin-left: 0.5em;'>
                                            예약적용
                                            <VIcon end size="18" icon="tabler-clock-up" />
                                        </VBtn>                 
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VDivider style="margin: 1em 0;" />
                    <h4 class="pt-3">계좌정보 일괄변경</h4>
                    <br>
                    <VRow no-gutters style="align-items: center;">
                        <VCol md="3" cols="12" style="padding: 0.25em;margin-bottom: auto !important;">
                            <VTextField v-model="salesforces.acct_num" prepend-inner-icon="ri-bank-card-fill"
                                placeholder="계좌번호 입력" persistent-placeholder />
                        </VCol>
                        <VCol md="3" cols="12" style="padding: 0.25em;margin-bottom: auto !important;">
                            <VTextField v-model="salesforces.acct_name" prepend-inner-icon="tabler-user"
                                placeholder="예금주 입력" persistent-placeholder />
                        </VCol>
                        <VCol md="3" cols="12"  style="padding: 0.25em;margin-bottom: auto !important;">
                            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="salesforces.acct_bank_code"
                                    :items="[{ code: null, title: '선택안함' }].concat(banks)" prepend-inner-icon="ph-buildings"
                                    label="은행 선택" item-title="title" item-value="code" single-line
                                    @update:modelValue="setAcctBankName()" />
                        </VCol>
                        <VCol md="3" cols="12" style="padding: 0.25em;margin-bottom: auto !important;margin-left: auto;">
                            <div style="float: inline-end;">
                                <VBtn variant="tonal" size="small" @click="setAccountInfo(0)">
                                    즉시적용
                                    <VIcon end size="18" icon="tabler-direction-sign" />
                                </VBtn>
                                <VBtn variant="tonal" size="small" color="secondary" @click="setAccountInfo(1)"
                                    style='margin-left: 0.5em;'>
                                    예약적용
                                    <VIcon end size="18" icon="tabler-clock-up" />
                                </VBtn>                                
                            </div>
                        </VCol>
                    </VRow>
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
