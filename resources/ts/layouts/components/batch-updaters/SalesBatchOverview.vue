

<script lang="ts" setup>
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

const checkAgreeDialog = ref()
const passwordAuthDialog = ref()

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

        if (await alert.value.show(`정말 ${type}${method}하시겠습니까?`)) {
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

const post = async (page: string, _params: any, type='일괄') => {
    try {
        const [result, params] = await getCommonParams(_params, '수정', type)
        if(result) {
            const r = await axios.post('/api/v1/manager/salesforces/batch-updaters/' + page, params)
            snackbar.value.show(r.data.message, 'success')
            emits('update:select_idxs', [])
        }
    }
    catch (e: any) {
        snackbar.value.show(e.response.data.message, 'error')
        const r = errorHandler(e)
    }
}

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

const setSettleTaxType = () => {
    post('set-settle-tax-type', {
        'settle_tax_type': salesforces.settle_tax_type,
    })
}

const setSettleCycle = () => {
    post('set-settle-cycle', {
        'settle_cycle': salesforces.settle_cycle,
    })

}

const setSettleDay = () => {
    post('set-settle-day', {
        'settle_day': salesforces.settle_day,
    })
}

const setIsAbleModifyMcht = () => {
    post('set-is-able-modify-mcht', {
        'is_able_modify_mcht': salesforces.is_able_modify_mcht,
    })
}

const setViewType = () => {
    post('set-view-type', {
        'view_type': salesforces.view_type,
    })
}

const setAccountInfo = () => {
    post('set-account-info', {
        'acct_num': salesforces.acct_num,
        'acct_name': salesforces.acct_name,
        'acct_bank_code': salesforces.bank.code,
        'acct_bank_name': salesforces.bank.title,
    })
}

const setNote = () => {
    post('set-note', {
        'note': salesforces.note,
    })
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
                <div style="width: 100%;">
                    <VRow>
                        <VCol :md="6" :cols="12">
                            <VRow no-gutters style="align-items: center;">
                                <VCol md="3" cols="12">정산세율</VCol>
                                <VCol md="9">
                                    <div class="batch-container">
                                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="salesforces.settle_tax_type"
                                            :items="tax_types" prepend-inner-icon="icon-park-outline:cycle"
                                            label="정산세율 선택" item-title="title" item-value="id" persistent-hint
                                            single-line />
                                        <VBtn style='margin-left: 0.5em;' variant="tonal" size="small" @click="setSettleTaxType()">
                                            즉시적용
                                            <VIcon end size="18" icon="tabler-direction-sign" />
                                        </VBtn>
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol :md="6" :cols="12">
                            <VRow no-gutters style="align-items: center;">
                                <VCol md="3" cols="12">메모</VCol>
                                <VCol md="9">
                                    <div class="batch-container">
                                        <VTextField v-model="salesforces.note" label="메모사항"
                                            prepend-inner-icon="twemoji-spiral-notepad" maxlength="300" auto-grow />
                                        <VBtn style='margin-left: 0.5em;' variant="tonal" size="small"
                                            @click="setNote()">
                                            즉시적용
                                            <VIcon end size="18" icon="tabler-direction-sign" />
                                        </VBtn>
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VRow>
                        <VCol :md="6">
                            <VRow no-gutters style="align-items: center;">
                                <VCol md="3" cols="12">정산주기</VCol>
                                <VCol md="9">
                                    <div class="batch-container">
                                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="salesforces.settle_cycle"
                                            :items="all_cycles" prepend-inner-icon="icon-park-outline:cycle"
                                            label="정산 주기 선택" item-title="title" item-value="id" persistent-hint
                                            single-line />

                                        <VBtn style='margin-left: 0.5em;' variant="tonal" size="small" @click="setSettleCycle()">
                                            즉시적용
                                            <VIcon end size="18" icon="tabler-direction-sign" />
                                        </VBtn>
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol :md="6" :cols="12">
                            <VRow no-gutters style="align-items: center;">
                                <VCol md="3" cols="12">정산요일</VCol>
                                <VCol md="9">
                                    <div class="batch-container">
                                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="salesforces.settle_day"
                                            :items="all_days" prepend-inner-icon="icon-park-outline:cycle" label="정산 요일 선택"
                                            item-title="title" item-value="id" persistent-hint single-line />

                                        <VBtn style='margin-left: 0.5em;' variant="tonal" size="small" @click="setSettleDay()">
                                            즉시적용
                                            <VIcon end size="18" icon="tabler-direction-sign" />
                                        </VBtn>
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VDivider style="margin: 1em 0;" />
                    <VRow>
                        <VCol :md="6" :cols="12">
                            <VRow no-gutters style="align-items: center;">
                                <VCol md="4" cols="12">하위 가맹점 수정</VCol>
                                <VCol md="8">
                                    <div class="batch-container">                                        
                                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="salesforces.is_able_modify_mcht" 
                                            :items="is_ables" item-title="title" item-value="id" single-line/>
                                        <VBtn style='margin-left: 0.5em;' variant="tonal" size="small" @click="setIsAbleModifyMcht()">
                                            즉시적용
                                            <VIcon end size="18" icon="tabler-direction-sign" />
                                        </VBtn>
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol :md="6">
                            <VRow no-gutters style="align-items: center;">
                                <VCol md="3" cols="12">화면 타입</VCol>
                                <VCol md="9" style="padding-left: 0;">
                                    <div class="batch-container">
                                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="salesforces.view_type" 
                                            :items="view_types" item-title="title" item-value="id" single-line/>
                                        <VBtn style='margin-left: 0.5em;' variant="tonal" size="small" @click="setViewType()">
                                            즉시적용
                                            <VIcon end size="18" icon="tabler-direction-sign" />
                                        </VBtn>
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VDivider style="margin: 1em 0;" />
                    <VRow no-gutters style="align-items: center;">
                        <VCol md="2" cols="12" style="padding: 0.25em;margin-bottom: auto !important;">계좌 정보</VCol>
                        <VCol md="3" cols="12" style="padding: 0.25em;margin-bottom: auto !important;">
                            <VTextField v-model="salesforces.acct_num" prepend-inner-icon="ri-bank-card-fill"
                                placeholder="계좌번호 입력" persistent-placeholder />
                        </VCol>
                        <VCol md="2" cols="12" style="padding: 0.25em;margin-bottom: auto !important;">
                            <VTextField v-model="salesforces.acct_name" prepend-inner-icon="tabler-user"
                                placeholder="예금주 입력" persistent-placeholder />
                        </VCol>
                        <VCol md="3" cols="12" style="padding: 0.25em;">
                            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="salesforces.bank"
                                :items="[{ code: null, title: '선택안함' }].concat(banks)"
                                prepend-inner-icon="ph-buildings" label="은행 선택"
                                :hint="`${salesforces.bank.title}, 은행 코드: ${salesforces.bank.code ? salesforces.bank.code : '000'} `"
                                item-title="title" item-value="code" persistent-hint return-object single-line
                                />
                        </VCol>
                        <VCol md="2" cols="12" style="padding: 0.25em;margin-bottom: auto !important;margin-left: auto;">
                            <VBtn variant="tonal" size="small" @click="setAccountInfo()">
                                즉시적용
                                <VIcon end size="18" icon="tabler-direction-sign" />
                            </VBtn>
                        </VCol>
                    </VRow>
                </div>
            </VCardText>
        </VCard>
        <CheckAgreeDialog ref="checkAgreeDialog"/>
        <PasswordAuthDialog ref="passwordAuthDialog"/>
    </section>
</template>
