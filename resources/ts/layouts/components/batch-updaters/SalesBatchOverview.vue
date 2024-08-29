

<script lang="ts" setup>
import FeeBookDialog from '@/layouts/dialogs/users/FeeBookDialog.vue'
import PasswordAuthDialog from '@/layouts/dialogs/users/PasswordAuthDialog.vue'
import CheckAgreeDialog from '@/layouts/dialogs/utils/CheckAgreeDialog.vue'
import { useRequestStore } from '@/views/request'
import { settleCycles, settleDays, settleTaxTypes } from '@/views/salesforces/useStore'
import { Salesforce } from '@/views/types'
import { banks } from '@/views/users/useStore'
import { axios, getUserLevel, user_info } from '@axios'
import corp from '@corp'

interface Props {
    selected_idxs: number[],
}

const props = defineProps<Props>()
const emits = defineEmits(['update:select_idxs'])

const all_cycles = settleCycles()
const all_days = settleDays()
const tax_types = settleTaxTypes()
const is_ables = [{id:0, title:'불가능'}, {id:1, title:'가능'}]
const view_types = [{id:0, title:'간편보기'}, {id:1, title:'상세보기'}]

const { request } = useRequestStore()

const store = <any>(inject('store'))
const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))
    const formatDate = <any>(inject('$formatDate'))

const feeBookDialog = ref()
const checkAgreeDialog = ref()
const passwordAuthDialog = ref()

const selected_all = ref(0)
const salesforces = reactive<Salesforce>({
    settle_tax_type: 0,
    settle_cycle: 0,
    settle_day: 0,
    is_able_modify_mcht: 0,
    view_type: 0,
    note: '',
    acct_num: "",
    acct_name: "",
    bank: { code: null, title: '선택안함' },
})

const getCommonParams = async (params: any, method: string, type: string) => {
    if (props.selected_idxs.length || selected_all.value) {
        if(selected_all.value) {
            const agree = await checkAgreeDialog.value.show(store.pagenation.total_count, method, '영업점')
            if (agree === false)
                return [false, params]
            else
            {
                if(corp.pv_options.paid.use_head_office_withdraw) {
                    let phone_num = user_info.value.phone_num
                    if(phone_num) {
                        phone_num = phone_num.replaceAll(' ', '').replaceAll('-', '')
                        const token = await passwordAuthDialog.value.show(phone_num)
                        if(token !== '') {
                            
                        }
                        else
                            return [false, params]
                    }
                    else {
                        snackbar.value.show('로그인한 계정의 휴대폰번호를 업데이트한 후 다시 시도해주세요.', 'error')
                        return [false, params]
                    }
                }
            }
        }
        let message = `정말 ${type}${method}하시겠습니까?`;
        if(params['apply_type'] === 1)
            message += `<br><b>${params['apply_dt']}${params['apply_dt'].length > 10 ? '부터' : '일 자정에'}</b> 적용될 예정입니다.`

        if (await alert.value.show(message)) {
            Object.assign(params, { 
                selected_idxs: props.selected_idxs,
                selected_all: selected_all.value,
            })
            if(selected_all.value) {
                Object.assign(params, {
                    filter: store.params
                })
                params.filter.search = (document.getElementById('search') as HTMLInputElement)?.value
                params.total_selected_count = store.pagenation.total_count
            }
            return [true, params]
        }
        return [false, params]
    }
    else {
        snackbar.value.show('영업점을 1개이상 선택해주세요.', 'error')
        return [false, params]
    }
}

const batchRemove = async () => {
    const [result, params] = await getCommonParams({}, '삭제', '일괄')
    if(result) {
        const r = await request({ url: `/api/v1/manager/salesforces/batch-updaters/remove`, method: 'delete', data: params }, true)
        emits('update:select_idxs', [])
    }
}

const post = async (page: string, _params: any, apply_type: number) => {
    try {
        const apply_dt = await getApplyDt(page, apply_type)
        if(apply_dt !== '') {
            _params['apply_dt'] = apply_dt
            _params['apply_type'] = apply_type
            const [result, params] = await getCommonParams(_params, '적용', apply_type ? '예약' : '일괄')
            if(result) {
                const r = await axios.post('/api/v1/manager/salesforces/batch-updaters/' + page, params)
                snackbar.value.show(r.data.message, 'success')
                emits('update:select_idxs', [])
            }
        }
    }
    catch (e: any) {
        snackbar.value.show(e.response.data.message, 'error')
        const r = errorHandler(e)
    }
}

const getApplyDt = async (page: string, type: number) => {
    let apply_dt = ''
    if(type === 0)
        apply_dt = formatDate(new Date)
    else 
        apply_dt = await feeBookDialog.value.show(page.includes('set-fee') ? false : true)
    return apply_dt
}

const setSettleTaxType= (apply_type: number) => {
    post('set-settle-tax-type', {
        'settle_tax_type': salesforces.settle_tax_type,
    }, apply_type)
}

const setSettleCycle= (apply_type: number) => {
    post('set-settle-cycle', {
        'settle_cycle': salesforces.settle_cycle,
    }, apply_type)

}

const setSettleDay= (apply_type: number) => {
    post('set-settle-day', {
        'settle_day': salesforces.settle_day,
    }, apply_type)
}

const setIsAbleModifyMcht= (apply_type: number) => {
    post('set-is-able-modify-mcht', {
        'is_able_modify_mcht': salesforces.is_able_modify_mcht,
    }, apply_type)
}

const setViewType= (apply_type: number) => {
    post('set-view-type', {
        'view_type': salesforces.view_type,
    }, apply_type)
}

const setAccountInfo= (apply_type: number) => {
    post('set-account-info', {
        'acct_num': salesforces.acct_num,
        'acct_name': salesforces.acct_name,
        'acct_bank_code': salesforces.bank.code,
        'acct_bank_name': salesforces.bank.title,
    }, apply_type)
}

const setNote= (apply_type: number) => {
    post('set-note', {
        'note': salesforces.note,
    }, apply_type)
}


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
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="salesforces.is_able_modify_mcht" 
                                            :items="is_ables" item-title="title" item-value="id" label="하위 가맹점 수정권한"/>
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
                            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="salesforces.bank"
                                :items="[{ code: null, title: '선택안함' }].concat(banks)"
                                prepend-inner-icon="ph-buildings" label="은행 선택"
                                :hint="`${salesforces.bank.title}, 은행 코드: ${salesforces.bank.code ? salesforces.bank.code : '000'} `"
                                item-title="title" item-value="code" persistent-hint return-object
                                />
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
