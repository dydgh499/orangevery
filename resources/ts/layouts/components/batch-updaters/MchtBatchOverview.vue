

<script lang="ts" setup>
import { batch } from '@/layouts/components/batch-updaters/batch'
import FeeBookDialog from '@/layouts/dialogs/users/FeeBookDialog.vue'
import PasswordAuthDialog from '@/layouts/dialogs/users/PasswordAuthDialog.vue'
import CheckAgreeDialog from '@/layouts/dialogs/utils/CheckAgreeDialog.vue'
import { useSalesFilterStore } from '@/views/salesforces/useStore'
import { useStore } from '@/views/services/pay-gateways/useStore'
import type { Options } from '@/views/types'
import { banks, getOnlyNumber } from '@/views/users/useStore'
import { getIndexByLevel, getLevelByIndex, getUserLevel } from '@axios'
import corp from '@corp'

interface Props {
    selected_idxs: number[],
    selected_sales_id: number,
    selected_level: number,
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
    } = batch(emits, '가맹점', 'merchandises')

const store = <any>(inject('store'))

const { cus_filters } = useStore()
const { sales, initAllSales } = useSalesFilterStore()

const levels = corp.pv_options.auth.levels
const show_fees = <Options[]>([{id:0, title:"숨김"}, {id:1, title:"노출"}])

const merchandise = reactive<any>({
    resident_num: '',
    custom_id: null,
    trx_fee: 0,
    hold_fee: 0,
    sales5_fee: 0,
    sales4_fee: 0,
    sales3_fee: 0,
    sales2_fee: 0,
    sales1_fee: 0,
    sales0_fee: 0,

    acct_num: "",
    acct_name: "",
    bank: { code: null, title: '선택안함' },
    business_num: "",
    enabled: 1,
    is_show_fee: 0,
})

const noti = reactive<any>({
    noti_url: "",
    noti_note: "",
    noti_status: true,
})
const resident_num_front = ref('')
const resident_num_back = ref('')

const setSalesFee = async (sales_idx: number, apply_type: number) => {
    post(`salesforces/set-fee`, {
        'sales_fee': parseFloat(merchandise['sales' + sales_idx + "_fee"]),
        'sales_id': merchandise['sales' + sales_idx + "_id"],
        'level': getIndexByLevel(sales_idx),
    }, apply_type)
}

const setMchtFee = async (apply_type: number) => {
    post(`merchandises/set-fee`, {
        'trx_fee': parseFloat(merchandise.trx_fee),
        'hold_fee': parseFloat(merchandise.hold_fee),
    }, apply_type)
}

const setEnabled = (apply_type: number) => {
    post('set-enabled', {
        'enabled': Number(merchandise.enabled),
    }, apply_type)
}

const setCustomFilter = (apply_type: number) => {
    post('set-custom-filter', {
        'custom_id': merchandise.custom_id,
    }, apply_type)
}
const setBusinessNum = (apply_type: number) => {
    post('set-business-num', {
        'business_num': merchandise.business_num,
    }, apply_type)
}

const setResidentNum = (apply_type: number) => {
    post('set-resident-num', {
        'resident_num': merchandise.resident_num,
    }, apply_type)
}

const setAccountInfo = (apply_type: number) => {
    post('set-account-info', {
        'acct_num': merchandise.acct_num,
        'acct_name': merchandise.acct_name,
        'acct_bank_code': merchandise.bank.code,
        'acct_bank_name': merchandise.bank.title,
    }, apply_type)
}

const setIsShowFee = async (apply_type: number) => {
    post('set-show-fee', {
        'is_show_fee': merchandise.is_show_fee,
    }, apply_type)
}

// -------------- noti ----------------
const setNotiUrl = (apply_type: number) => {
    post('set-noti-url', {
        'noti_status': noti.noti_status,
        'send_url': noti.noti_url,
        'note': noti.noti_note,
    }, apply_type)
}

initAllSales()

watchEffect(() => {
    if(props.selected_sales_id && props.selected_level) {
        const idx = getLevelByIndex(props.selected_level)
        merchandise[`sales${idx}_id`] = props.selected_sales_id
    }
})
watchEffect(() => {
    selected_idxs.value = props.selected_idxs
    selected_sales_id.value = props.selected_sales_id
    selected_level.value = props.selected_level
})
watchEffect(() => {
    merchandise.resident_num = resident_num_front.value + resident_num_back.value
})
</script>
<template>
    <section>
        <VCard title="가맹점 일괄작업" style="max-height: 55em !important;overflow-y: auto !important;">
            <VCardText>
                <template v-if="props.selected_sales_id === 0 && props.selected_level === 0">
                    <div style=" display: flex;align-items: center; justify-content: space-between;">
                        <VRadioGroup v-model="selected_all">
                            <VRadio :value="0">
                                <template #label>
                                    <b>선택된 가맹점 : {{ props.selected_idxs.length.toLocaleString() }}개</b>
                                </template>
                            </VRadio>
                            <VRadio :value="1" v-if="getUserLevel() === 40">
                                <template #label>
                                    <b>가맹점: {{ store.pagenation.total_count }}개</b>
                                </template>
                            </VRadio>
                        </VRadioGroup>
                        <VBtn type="button" color="error" @click="batchRemove()" style="float: inline-end;" size="small">
                            일괄삭제
                            <VIcon size="18" icon="tabler-trash" />
                        </VBtn>
                    </div>
                    <VDivider style="margin: 0.5em 0;" />
                </template>
                <div style="width: 100%;">
                    <h4 class="pt-3">상위 영업점 일괄변경</h4>
                    <br>
                    <VRow no-gutters style="align-items: center; margin-bottom: 0.5em;">
                            <template v-for="i in 6" :key="i">
                                <template v-if="levels['sales' + (6 - i) + '_use'] && getUserLevel() >= getIndexByLevel(6 - i)">
                                    <VCol md="3" cols="12" style="margin-bottom: 0.5em;">
                                        <div class="batch-container">
                                            <VAutocomplete :menu-props="{ maxHeight: 400 }"
                                                v-model="merchandise['sales' + (6 - i) + '_id']" :items="sales[6 - i].value"
                                                item-title="sales_name"
                                                item-value="id" 
                                                :label="`${ levels['sales' + (6 - i) + '_name'] } 선택`"
                                                >
                                            </VAutocomplete>
                                            <VTextField v-model="merchandise['sales' + (6 - i) + '_fee']" type="number"
                                                suffix="%" 
                                                :label="`수수료율`"
                                                style="max-width: 6em;"/>
                                        </div>
                                    </VCol>
                                    <VCol md="3" cols="12" style="margin-bottom: 0.5em;">
                                        <div class="button-cantainer" style="margin-right: 0.5em;">
                                            <VBtn variant="tonal" size="small" @click="setSalesFee(6 - i, 0)">
                                                즉시적용
                                                <VIcon end size="18" icon="tabler-direction-sign" />
                                            </VBtn>
                                            <VBtn variant="tonal" size="small" color="secondary" @click="setSalesFee(6 - i, 1)" style="margin-left: 0.5em;">
                                                예약적용
                                                <VIcon end size="18" icon="tabler-clock-up" />
                                            </VBtn>
                                        </div>
                                    </VCol>
                                </template>
                        </template>
                    </VRow>
                    <VDivider style="margin: 1em 0;" />
                    <VRow>
                        <VCol :md="6" :cols="12">
                            <h4 class="pt-3">가맹점 수수료 일괄변경</h4>
                            <br>
                            <VRow no-gutters style="align-items: center;">
                                <VCol md="6" cols="12">
                                    <div class="batch-container">
                                        <VTextField v-model="merchandise.trx_fee" type="number" suffix="%" :label="`거래 수수료율`"/>
                                        <VTextField v-model="merchandise.hold_fee" type="number" suffix="%" :label="`유보금 수수료율`"/>
                                    </div>
                                </VCol>
                                <VCol md="6" cols="12">
                                    <div class="button-cantainer">
                                        <VBtn variant="tonal" size="small" @click="setMchtFee(0)">
                                            즉시적용
                                            <VIcon end size="18" icon="tabler-direction-sign" />
                                        </VBtn>
                                        <VBtn variant="tonal" size="small" color="secondary" @click="setMchtFee(1)" style="margin-left: 0.5em;">
                                            예약적용
                                            <VIcon end size="18" icon="tabler-clock-up" />
                                        </VBtn>
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                        <template v-if="corp.pv_options.paid.use_noti">
                            <VCol :md="6" :cols="12">
                                <h4 class="pt-3">노티정보 일괄변경</h4>
                                <br>
                                <VRow no-gutters style="align-items: center;">                    
                                    <VCol md="8" cols="12">
                                        <div class="batch-container">
                                            <VTextField v-model="noti.noti_url" type="text" label="노티 URL" />
                                            <VTextField v-model="noti.noti_note" label="메모사항"
                                                prepend-inner-icon="twemoji-spiral-notepad" maxlength="300" />
                                        </div>
                                    </VCol>
                                    <VCol md="4" cols="12">
                                        <div class="button-cantainer">
                                            <VBtn variant="tonal" size="small" @click="setNotiUrl(0)">
                                                즉시적용
                                                <VIcon end size="18" icon="tabler-direction-sign" />
                                            </VBtn>
                                        </div>
                                    </VCol>
                                </VRow>
                            </VCol>
                        </template>
                    </VRow>

                    <VDivider style="margin: 1em 0;" />
                    <h4 class="pt-3">계좌정보 일괄변경</h4>
                    <br>
                    <VRow no-gutters style="align-items: center;">
                        <VCol md="3" cols="12" style="padding: 0.25em;margin-bottom: auto !important;">
                            <VTextField v-model="merchandise.acct_num" prepend-inner-icon="ri-bank-card-fill"
                                label="계좌번호 입력"  />
                        </VCol>
                        <VCol md="3" cols="6" style="padding: 0.25em;margin-bottom: auto !important;">
                            <VTextField v-model="merchandise.acct_name" prepend-inner-icon="tabler-user"
                                label="예금주 입력"  />
                        </VCol>
                        <VCol md="3" cols="6" style="padding: 0.25em;margin-bottom: auto !important;">
                            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="merchandise.bank"
                                :items="[{ code: null, title: '선택안함' }].concat(banks)"
                                prepend-inner-icon="ph-buildings" label="은행 선택"
                                :hint="`${merchandise.bank.title}, 은행 코드: ${merchandise.bank.code ? merchandise.bank.code : '000'} `"
                                item-title="title" item-value="code" persistent-hint return-object
                            />                            
                        </VCol>
                        <VCol md="3" cols="12" style="padding: 0.25em;margin-bottom: auto !important; margin-left: auto;">
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
                    <VDivider style="margin: 1em 0;" />
                    <h4 class="pt-3">가맹점정보 일괄변경</h4>
                    <br>
                    <VRow>
                        <VCol :md="6" :cols="12">
                            <VRow no-gutters style="align-items: center;">
                                <VCol md="6" cols="12" style="display: flex;">
                                    <VTextField 
                                        v-model="resident_num_front" 
                                        maxlength="6"
                                        @update:model-value="resident_num_front = getOnlyNumber($event)"
                                        style="width: 13em;"
                                        label="주민번호(앞)"
                                    />
                                    <span style="margin: 0.5em;text-align: center;"> - </span>
                                    <VTextField 
                                        v-model="resident_num_back" 
                                        maxlength="7"
                                        @update:model-value="resident_num_back = getOnlyNumber($event)"
                                        style="width: 13em;"
                                        label="주민번호(뒤)"
                                    />
                                </VCol>
                                <VCol md="6" cols="12">
                                    <div class="button-cantainer">
                                        <VBtn variant="tonal" size="small" @click="setResidentNum(0)">
                                            즉시적용
                                            <VIcon end size="18" icon="tabler-direction-sign" />
                                        </VBtn>
                                        <VBtn variant="tonal" size="small" color="secondary" @click="setResidentNum(1)"
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
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="merchandise.custom_id"
                                            :items="[{ id: null, type: 1, name: '사용안함' }].concat(cus_filters)" label="커스텀 필터"
                                            item-title="name" item-value="id" />
                                </VCol>
                                <VCol md="6" cols="12">
                                    <div class="button-cantainer">
                                        <VBtn variant="tonal" size="small" @click="setCustomFilter(0)">
                                            즉시적용
                                            <VIcon end size="18" icon="tabler-direction-sign" />
                                        </VBtn>
                                        <VBtn variant="tonal" size="small" color="secondary" @click="setCustomFilter(1)"
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
                                    <VTextField v-model="merchandise.business_num" type="number" 
                                        @update:model-value="merchandise.business_num = getOnlyNumber($event)"
                                        label="사업자 번호"/>
                                </VCol>
                                <VCol md="6" cols="12">
                                    <div class="button-cantainer">
                                        <VBtn variant="tonal" size="small" @click="setBusinessNum(0)">
                                            즉시적용
                                            <VIcon end size="18" icon="tabler-direction-sign" />
                                        </VBtn>
                                        <VBtn variant="tonal" size="small" color="secondary" @click="setBusinessNum(1)"
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
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="merchandise.is_show_fee" 
                                        :items="show_fees" item-title="title" item-value="id" label="수수료율 노출"/>
                                </VCol>
                                <VCol md="6" cols="12">
                                    <div class="button-cantainer">
                                        <VBtn variant="tonal" size="small" @click="setIsShowFee(0)">
                                            즉시적용
                                            <VIcon end size="18" icon="tabler-direction-sign" />
                                        </VBtn>
                                        <VBtn variant="tonal" size="small" color="secondary" @click="setIsShowFee(1)"
                                            style='margin-left: 0.5em;'>
                                            예약적용
                                            <VIcon end size="18" icon="tabler-clock-up" />
                                        </VBtn>                 
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol :md="6" :cols="12" v-if="corp.pv_options.paid.subsidiary_use_control">
                            <VRow no-gutters style="align-items: center;">
                                <VCol md="6" cols="12">
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="merchandise.enabled" 
                                        :items="[{id:0, title:'OFF'}, {id:1, title:'ON'}]" item-title="title" item-value="id" 
                                        label="전산 사용상태"/>
                                </VCol>
                                <VCol md="6" cols="12">
                                    <div class="button-cantainer">
                                        <VBtn variant="tonal" size="small" @click="setEnabled(0)">
                                            즉시적용
                                            <VIcon end size="18" icon="tabler-direction-sign" />
                                        </VBtn>
                                        <VBtn variant="tonal" size="small" color="secondary" @click="setEnabled(1)"
                                            style='margin-left: 0.5em;'>
                                            예약적용
                                            <VIcon end size="18" icon="tabler-clock-up" />
                                        </VBtn>                 
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                </div>
            </VCardText>
            <VDivider />
            <div style="padding: 1em; text-align: end;">
                <h5>0시(자정) 예약정보의 경우 수수료율 예약정보가 먼저 적용된 후 나머지 예약정보가 적용됩니다.</h5>
            </div>
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
