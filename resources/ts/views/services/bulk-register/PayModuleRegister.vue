<script lang="ts" setup>
import { useStore } from '@/views/services/pay-gateways/useStore'
import { useSearchStore } from '@/views/merchandises/pay-modules/useStore'
import { useRegisterStore } from '@/views/services/bulk-register/PayModRegisterStore'
import { useSalesFilterStore } from '@/views/salesforces/useStore'
import { module_types, installments, fin_trx_delays, cxl_types, comm_settle_types, under_sales_types } from '@/views/merchandises/pay-modules/useStore'
import SettleTypeExplainDialog from '@/views/services/bulk-register/SettleTypeExplainDialog.vue'
import PGExplainDialog from '@/views/services/bulk-register/PGExplainDialog.vue'
import UsageTooltip from '@/views/services/bulk-register/UsageTooltip.vue'
import { Registration } from '@/views/registration'
import type { PayModule, Options } from '@/views/types'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import corp from '@corp';
import { isEmpty } from '@core/utils'
import { salesLevels } from '@axios'


const { store } = useSearchStore()
const { pgs, pss, settle_types, terminals, finance_vans } = useStore()
const { head, headers, isPrimaryHeader } = useRegisterStore()
const { mchts } = useSalesFilterStore()

const all_levels = [{ id: 10, title: '가맹점' }, ...salesLevels()]
const auth_types: Options[] = [
    { id: 0, title: '비인증', },
    { id: 1, title: '구인증', },
]
const view_types: Options[] = [
    { id: 0, title: '숨김', },
    { id: 1, title: '노출', },
]
const { ExcelReader, openFilePicker, bulkRegister } = Registration()

const snackbar = <any>(inject('snackbar'))

const excel = ref()
const items = ref<PayModule[]>([])
const is_clear = ref<boolean>(false)

const settleTypeExplain = ref()
const pgExplain = ref()

const validate = () => {
    for (let i = 0; i < items.value.length; i++) {
        items.value[i].mcht_name = items.value[i].mcht_name?.trim()
        const pg_id = pgs.find(item => item.id === items.value[i].pg_id)
        const ps_id = pss.find(item => item.id === items.value[i].ps_id)
        const settle_type = settle_types.find(item => item.id === items.value[i].settle_type)
        const module_type = module_types.find(item => item.id === items.value[i].module_type)
        const installment = installments.find(item => item.id === items.value[i].installment)
        const mcht = mchts.find(item => item.mcht_name == items.value[i].mcht_name)

        const finance_van = corp.pv_options.paid.use_realtime_deposit ? finance_vans.find(item => item.id === items.value[i].fin_id) : true
        const fin_trx_delay = corp.pv_options.paid.use_realtime_deposit ? fin_trx_delays.find(item => item.id === items.value[i].fin_trx_delay) : true
        const cxl_type = corp.pv_options.paid.use_realtime_deposit ? cxl_types.find(item => item.id === items.value[i].cxl_type) : true

        if (mcht == null) {
            snackbar.value.show((i + 1) + '번째 결제모듈의 가맹점 상호가 이상합니다.', 'error')
            is_clear.value = false
        }
        else if (pg_id == null) {
            snackbar.value.show((i + 1) + '번째 결제모듈의 PG사명이 이상합니다.', 'error')
            is_clear.value = false
        }
        else if (ps_id == null) {
            snackbar.value.show((i + 1) + '번째 결제모듈의 구간이 이상합니다.', 'error')
            is_clear.value = false
        }
        else if (ps_id.pg_id != pg_id.id) {
            snackbar.value.show((i + 1) + '번째 결제모듈의 구간이 ' + pg_id.pg_name + '에 포함되는 구간이 아닙니다.', 'error')
            is_clear.value = false
        }
        else if (isEmpty(items.value[i].note)) {
            snackbar.value.show((i + 1) + '번째 결제모듈의 별칭은 필수로 입력해야합니다.', 'error')
            is_clear.value = false
        }
        else if (isEmpty(items.value[i].mcht_name ?? '')) {
            snackbar.value.show((i + 1) + '번째 결제모듈의 가맹점 상호는 필수로 입력해야합니다.', 'error');
            is_clear.value = false;
        }
        else if (settle_type == null) {
            snackbar.value.show((i + 1) + '번째 결제모듈의 가맹점 정산타입이 이상합니다.', 'error')
            is_clear.value = false
        }
        else if (module_type == null) {
            snackbar.value.show((i + 1) + '번째 결제모듈의 모듈타입이 이상합니다.', 'error')
            is_clear.value = false
        }
        else if (installment == null) {
            snackbar.value.show((i + 1) + '번째 결제모듈의 할부기간이 이상합니다.', 'error')
            is_clear.value = false
        }
        else if (finance_van == null) {
            snackbar.value.show((i + 1) + '번째 금융 VAN을 찾을 수 없습니다.', 'error')
            is_clear.value = false
        }
        else if (fin_trx_delay == null) {
            snackbar.value.show((i + 1) + '번째 이체 딜레이 타입을 찾을 수 없습니다.', 'error')
            is_clear.value = false
        }
        else if (cxl_type == null) {
            snackbar.value.show((i + 1) + '번째 취소 타입을 찾을 수 없습니다.', 'error')
            is_clear.value = false
        }
        else
            is_clear.value = true

        items.value[i].begin_dt = items.value[i].begin_dt == 0 ? null : items.value[i].begin_dt
        items.value[i].ship_out_dt = items.value[i].ship_out_dt == 0 ? null : items.value[i].ship_out_dt
        items.value[i].mcht_id = mcht?.id || null
        if (is_clear.value == false)
            return
    }
    snackbar.value.show('입력값 1차 검증에 성공하였습니다.', 'success')
    is_clear.value = true
}
const payModRegister = async () => {
    if (await bulkRegister('결제모듈', 'merchandises/pay-modules', items.value))
        location.reload()
}
watchEffect(async () => {
    if (excel.value) {
        items.value = await ExcelReader(headers, excel.value[0]) as PayModule[]
        validate()
    }
})
</script>
<template>
    <VCard style='margin-top: 1em;'>
        <VRow style="padding: 1em;">
            <VCol style="padding-bottom: 0;">
                <VCol>
                    <UsageTooltip />
                </VCol>
                <VCol>
                    하단 컬럼들은 숫자로 매칭되는 값들입니다.
                    <br>
                    엑셀 작성시 <b class="important-text">입력하실 내용에 매칭되는 숫자를 작성</b>해주세요.
                </VCol>
                <VCol>
                    컬럼 우측의 <b>O표시는 필수 입력값, X표시는 옵션 입력값</b>을 의미합니다.
                </VCol>
            </VCol>
            <VDivider />
            <CreateHalfVCol :mdl="8" :mdr="4">
                <template #name>
                    <VCol class="pb-0">
                        <b>수기결제 여부</b>
                        <br>
                        <VChip color="primary" style="margin: 0.5em;" v-for="(auth, key) in auth_types" :key="key">
                            {{ auth.title }} = {{ auth.id }}
                        </VChip>
                    </VCol>
                    <VCol class="pb-0">
                        <b>결제창 노출여부(기본:노출)</b>
                        <br>
                        <VChip color="primary" style="margin: 0.5em;" v-for="(view, key) in view_types" :key="key">
                            {{ view.title }} = {{ view.id }}
                        </VChip>
                    </VCol>
                    <VDivider />
                    <VCol class="pb-0">
                        <b>통신비 정산타입</b>
                        <br>
                        <VChip color="primary" style="margin: 0.5em;" v-for="(level, key) in comm_settle_types" :key="key">
                            {{ level.title }} = {{ level.id }}
                        </VChip>
                    </VCol>
                    <VCol class="pb-0">
                        <b>매출미달 적용기간</b>
                        <br>
                        <VChip color="primary" style="margin: 0.5em;" v-for="(level, key) in under_sales_types" :key="key">
                            {{ level.title }} = {{ level.id }}
                        </VChip>
                    </VCol>
                    <VCol class="pb-0">
                        <b>정산주체</b>
                        <br>
                        <VChip color="primary" style="margin: 0.5em;" v-for="(level, key) in all_levels" :key="key">
                            {{ level.title }} = {{ level.id }}
                        </VChip>
                    </VCol>
                    <VDivider />
                    <VCol class="pb-0">
                        <b>결제모듈 타입</b>
                        <br>
                        <VChip color="primary" style="margin: 0.5em;" v-for="(module, key) in module_types" :key="key">
                            {{ module.title }} = {{ module.id }}
                        </VChip>
                    </VCol>
                    <VCol class="pb-0">
                        <b>장비 종류</b>
                        <br>
                        <VChip color="primary" style="margin: 0.5em;" v-for="(terminal, key) in terminals" :key="key">
                            {{ terminal.name }} = {{ terminal.id }}
                        </VChip>
                        <b v-if="terminals.length == 0" class="important-text">"운영 관리 - PG사 관리 - 구분 정보"에서 장비 종류 추가 후 입력
                            가능합니다.</b>
                    </VCol>
                    <template v-if="corp.pv_options.paid.use_realtime_deposit">
                        <VCol class="pb-0">
                            <b>실시간 사용여부(기본:미사용)</b>
                            <br>
                            <VChip color="primary" style="margin: 0.5em;">
                                미사용 = 0
                            </VChip>
                            <VChip color="primary" style="margin: 0.5em;">
                                사용 = 1
                            </VChip>
                        </VCol>
                        <VCol class="pb-0">
                            <b>이체 모듈 타입</b>
                            <br>
                            <VChip color="primary" style="margin: 0.5em;" v-for="(finance_van, key) in finance_vans"
                                :key="key">
                                {{ finance_van.nick_name }} = {{ finance_van.id }}
                            </VChip>
                            <b v-if="finance_vans.length == 0" class="important-text">"운영 관리 - PG사 관리 - 실시간 이체 모듈"에서 금융 VAN
                                추가 후 입력 가능합니다.</b>
                        </VCol>
                        <VCol class="pb-0">
                            <b>이체 달레이</b>
                            <br>
                            <VChip color="primary" style="margin: 0.5em;" v-for="(fin_trx_delay, key) in fin_trx_delays"
                                :key="key">
                                {{ fin_trx_delay.title }} = {{ fin_trx_delay.id }}
                            </VChip>
                        </VCol>
                    </template>
                    <VCol class="pb-0">
                        <b>취소 타입</b>
                        <br>
                        <VChip color="primary" style="margin: 0.5em;" v-for="(cxl_type, key) in cxl_types" :key="key">
                            {{ cxl_type.title }} = {{ cxl_type.id }}
                        </VChip>
                    </VCol>
                </template>
                <template #input>
                    <VCol class="pb-0">
                        <b>가맹점 정산타입</b>
                        <br>
                        <VBtn size="small" color="success" variant="tonal" @click="settleTypeExplain.show()"
                            style="margin: 0.5em;">
                            상세정보 확인
                        </VBtn>
                    </VCol>
                    <VCol class="pb-0">
                        <b>PG사/구간명</b>
                        <br>
                        <VBtn size="small" color="success" variant="tonal" @click="pgExplain.show()" style="margin: 0.5em;">
                            상세정보 확인
                        </VBtn>
                    </VCol>
                    <VCol>
                        <b>할부 한도 입력시 주의사항</b>
                        <br>
                        <span>- 숫자만 입력(예: 0,2,3,4...11)</span>
                    </VCol>
                    <VCol v-if="corp.pv_options.paid.use_forb_pay_time">
                        <b>결제금지시간 입력시 주의사항</b>
                        <br>
                        <span>- H:i:s 포멧으로 입력(예: 11:00:00)</span>
                    </VCol>
                    <VCol>
                        <b class="important-text">한도, 매출미달 하한금 입력시 주의사항</b>
                        <br>
                        <span>- 만원 단위로 숫자만 입력(예: 100만원=100)</span>
                    </VCol>
                    <VCol>
                        <b>통신비, 매출미달금 적용 주의사항</b>
                        <br>
                        <span>- <b>통신비, 통신비 정산타입, 개통일, 정산일, 정산주체</b>가 설정되어있어야 적용됩니다.</span>
                        <br>
                        <span>
                            <b>- 예시:</b>
                            <br>통신비: 30,000
                            <br>통신비 정산타입: 개통월 M+2부터 적용
                            <br>개통일: 2023-09-25
                            <br>정산일: 1일
                            <br>정산주체: 가맹점
                            <br>
                            <br>
                            <b>통신비 차감적용일: 2023-11-01, 2023-12-01, 2024-01-01 ...</b>
                        </span>
                    </VCol>
                </template>
            </CreateHalfVCol>
        </VRow>
    </VCard>
    <br>
    <VRow class="match-height">
        <!-- 👉 개인정보 -->
        <VCol cols="12">
            <VCard>
                <VCardItem>
                    <VCardTitle>결제모듈 정보</VCardTitle>
                    <VRow class="pt-5 pb-5">
                        <VTable class="text-no-wrap" style="width: 100%;">
                            <!-- 👉 table head -->
                            <thead>
                                <tr>
                                    <th v-for="(header, key) in head.flat_headers" :key="key" class='list-square'>
                                        <span v-if="isPrimaryHeader(key as string)" class="text-primary">
                                            {{ header.ko }}
                                        </span>
                                        <span v-else>
                                            {{ header.ko }}
                                        </span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in items" :key="index">
                                    <template v-for="(_header, _key, _index) in head.headers" :key="_index">
                                        <td class='list-square'>
                                            <span v-if="_key == 'module_type'">
                                                <VChip
                                                    :color="store.getSelectIdColor(module_types.find(obj => obj.id === item[_key])?.id)">
                                                    {{ module_types.find(module_type => module_type['id'] ===
                                                        item[_key])?.title }}
                                                </VChip>
                                            </span>
                                            <span v-else-if="_key == 'installment'">
                                                {{ installments.find(inst => inst['id'] === item[_key])?.title }}
                                            </span>
                                            <span v-else-if="_key == 'pg_id'">
                                                {{ pgs.find(pg => pg['id'] === item[_key])?.pg_name }}
                                            </span>
                                            <span v-else-if="_key == 'ps_id'">
                                                {{ pss.find(ps => ps['id'] === item[_key])?.name }}
                                            </span>
                                            <span v-else-if="_key == 'terminal_id'">
                                                {{ terminals.find(terminal => terminal['id'] === item[_key])?.name }}
                                            </span>
                                            <span v-else-if="_key == 'settle_type'">
                                                {{ settle_types.find(settle_type => settle_type['id'] === item[_key])?.name
                                                }}
                                            </span>
                                            <span v-else>
                                                {{ item[_key] }}
                                            </span>
                                        </td>
                                    </template>
                                </tr>
                            </tbody>
                            <tfoot v-show="!Boolean(items.length)">
                                <tr>
                                    <td :colspan="Object.keys(head.flat_headers).length" class='list-square'
                                        style="border: 0;">
                                        양식 업로드후 등록 버튼을 클릭해주세요.
                                    </td>
                                </tr>
                            </tfoot>
                        </VTable>
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
    </VRow>
    <VCard style="margin-top: 1em;">
        <VCol class="d-flex gap-4">
            <VBtn color="secondary" variant="tonal" @click="head.exportToExcel([])" style="margin-left: auto;">
                양식 다운로드
                <VIcon end icon="uiw-file-excel" />
            </VBtn>
            <VFileInput id='pay-mod-uploader' accept="xlsx/*" show-size v-model="excel" v-show="false">
            </VFileInput>
            <VBtn type="button" @click="openFilePicker('pay-mod-uploader')">
                양식 업로드
                <VIcon end icon="uiw-file-excel" />
            </VBtn>
            <VBtn type="button" @click="payModRegister()" v-show="is_clear">
                등록
                <VIcon end icon="tabler-pencil" />
            </VBtn>
        </VCol>
    </VCard>
    <SettleTypeExplainDialog ref="settleTypeExplain" />
    <PGExplainDialog ref="pgExplain" />
</template>
<style scoped>
.important-text {
  color: red;
}
</style>

