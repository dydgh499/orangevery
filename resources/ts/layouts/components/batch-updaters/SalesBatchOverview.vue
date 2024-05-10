

<script lang="ts" setup>
import BooleanRadio from '@/layouts/utils/BooleanRadio.vue'
import { settleCycles, settleDays, settleTaxTypes } from '@/views/salesforces/useStore'
import { Salesforce } from '@/views/types'
import { banks } from '@/views/users/useStore'
import { axios } from '@axios'

interface Props {
    selected_idxs: number[],
}

const props = defineProps<Props>()
const all_cycles = settleCycles()
const all_days = settleDays()
const tax_types = settleTaxTypes()

const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))

const post = async (page: string, params: any) => {
    try {
        if (props.selected_idxs.length) {
            if (await alert.value.show('정말 일괄적용하시겠습니까?')) {
                Object.assign(params, { 'selected_idxs': props.selected_idxs })
                const r = await axios.post('/api/v1/manager/salesforces/batch-updaters/' + page, params)
                snackbar.value.show('성공하였습니다.', 'success')
            }
        }
        else
            snackbar.value.show('영업점을 1개이상 선택해주세요.', 'error')
    }
    catch (e: any) {
        snackbar.value.show(e.response.data.message, 'error')
        const r = errorHandler(e)
    }
}

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
        <VCard title="영업점 일괄 작업">
            <VCardText>
                <b>선택된 영업점 : {{ props.selected_idxs.length.toLocaleString() }}개</b>
                <VDivider style="margin: 1em 0;" />
                <div style="width: 100%;">
                    <VRow>
                        <VCol :md="4">정산세율</VCol>
                        <VCol :md="8">
                            <VRow no-gutters style="align-items: center;">
                                <VCol></VCol>
                                <VCol md="10">
                                    <div class="batch-container">
                                        <VRadioGroup v-model="salesforces.settle_tax_type" inline>
                                            <VRadio v-for="(tax_type, key, index) in tax_types" :value="tax_type.id"
                                                :key="index">
                                                <template #label>
                                                    <span>
                                                        {{ tax_type.title }}
                                                    </span>
                                                </template>
                                            </VRadio>
                                        </VRadioGroup>
                                        <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setSettleTaxType()">
                                            즉시적용
                                            <VIcon end icon="tabler-direction-sign" />
                                        </VBtn>
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VRow>
                        <VCol :md="6">
                            <VRow no-gutters style="align-items: center;">
                                <VCol>정산주기</VCol>
                                <VCol md="9">
                                    <div class="batch-container">
                                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="salesforces.settle_cycle"
                                            :items="all_cycles" prepend-inner-icon="icon-park-outline:cycle"
                                            label="정산 주기 선택" item-title="title" item-value="id" persistent-hint
                                            single-line />

                                        <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setSettleCycle()">
                                            즉시적용
                                            <VIcon end icon="tabler-direction-sign" />
                                        </VBtn>
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol :md="6" :cols="12">
                            <VRow no-gutters style="align-items: center;">
                                <VCol>정산요일</VCol>
                                <VCol md="9">
                                    <div class="batch-container">
                                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="salesforces.settle_day"
                                            :items="all_days" prepend-inner-icon="icon-park-outline:cycle" label="정산 요일 선택"
                                            item-title="title" item-value="id" persistent-hint single-line />

                                        <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setSettleDay()">
                                            즉시적용
                                            <VIcon end icon="tabler-direction-sign" />
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
                                <VCol>하위 가맹점 수정</VCol>
                                <VCol md="8">
                                    <div class="batch-container">
                                        <BooleanRadio :radio="salesforces.is_able_modify_mcht"
                                            @update:radio="salesforces.is_able_modify_mcht = $event">
                                            <template #true>가능</template>
                                            <template #false>불가능</template>
                                        </BooleanRadio>
                                        <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setIsAbleModifyMcht()">
                                            즉시적용
                                            <VIcon end icon="tabler-direction-sign" />
                                        </VBtn>
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol :md="6">
                            <VRow no-gutter style="align-items: center;">
                                <VCol>화면 타입</VCol>
                                <VCol md="9" style="padding-left: 0;">
                                    <div class="batch-container">
                                        <BooleanRadio :radio="salesforces.view_type"
                                            @update:radio="salesforces.view_type = $event">
                                            <template #true>상세보기</template>
                                            <template #false>간편보기</template>
                                        </BooleanRadio>
                                        <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setViewType()">
                                            즉시적용
                                            <VIcon end icon="tabler-direction-sign" />
                                        </VBtn>
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VDivider style="margin: 1em 0;" />
                    <VRow>
                        <VCol :cols="12">
                            <VRow no-gutters style="align-items: center;">
                                <VCol>계좌 정보</VCol>
                                <VCol md="9">
                                    <div class="batch-container">
                                        <VTextField v-model="salesforces.acct_num" prepend-inner-icon="ri-bank-card-fill"
                                            placeholder="계좌번호 입력" persistent-placeholder />
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VRow>
                        <VCol :cols="12">
                            <VRow no-gutters style="align-items: center;">
                                <VCol></VCol>
                                <VCol md="9">
                                    <div class="batch-container" style="align-items: baseline;">
                                        <VTextField v-model="salesforces.acct_name" prepend-inner-icon="tabler-user"
                                            placeholder="예금주 입력" persistent-placeholder />
                                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="salesforces.bank"
                                            :items="[{ code: null, title: '선택안함' }].concat(banks)"
                                            prepend-inner-icon="ph-buildings" label="은행 선택"
                                            :hint="`${salesforces.bank.title}, 은행 코드: ${salesforces.bank.code ? salesforces.bank.code : '000'} `"
                                            item-title="title" item-value="code" persistent-hint return-object single-line
                                            create />
                                        <VBtn variant="tonal" @click="setAccountInfo()"
                                            style="margin-bottom: auto; margin-left: 0.5em;">
                                            즉시적용
                                            <VIcon end icon="tabler-direction-sign" />
                                        </VBtn>
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VDivider style="margin: 1em 0;" />
                    <VRow>
                        <VCol :md="12">
                            <VRow no-gutters>
                                <VCol>메모</VCol>
                                <VCol md="9">
                                    <div class="batch-container">
                                        <VTextField v-model="salesforces.note" counter label="메모사항"
                                            prepend-inner-icon="twemoji-spiral-notepad" maxlength="300" auto-grow />
                                        <VBtn style='margin-bottom: auto; margin-left: 0.5em;' variant="tonal"
                                            @click="setNote()">
                                            즉시적용
                                            <VIcon end icon="tabler-direction-sign" />
                                        </VBtn>
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                </div>
            </VCardText>
        </VCard>
</template>
<style scoped>
.batch-container {
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
