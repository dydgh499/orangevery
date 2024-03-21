<script lang="ts" setup>
import MidCreateDialog from '@/layouts/dialogs/MidCreateDialog.vue'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import { comm_settle_types, cxl_types, fin_trx_delays, installments, module_types, under_sales_types, useSearchStore } from '@/views/merchandises/pay-modules/useStore'
import { Registration } from '@/views/registration'
import { useSalesFilterStore } from '@/views/salesforces/useStore'
import PGExplainDialog from '@/views/services/bulk-register/PGExplainDialog.vue'
import { useRegisterStore } from '@/views/services/bulk-register/PayModRegisterStore'
import SettleTypeExplainDialog from '@/views/services/bulk-register/SettleTypeExplainDialog.vue'
import UsageTooltip from '@/views/services/bulk-register/UsageTooltip.vue'
import { useStore } from '@/views/services/pay-gateways/useStore'
import type { Options, PayModule } from '@/views/types'
import { axios, salesLevels } from '@axios'
import { isEmpty } from '@core/utils'
import corp from '@corp'


const { store } = useSearchStore()
const { pgs, pss, settle_types, terminals, finance_vans } = useStore()
const { head, headers, isPrimaryHeader } = useRegisterStore()
const { mchts } = useSalesFilterStore()

const use_mid_create = ref(Number(corp.pv_options.paid.use_mid_create))
const use_online_pay = ref(0)
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

const midCreateDlg = ref()
const settleTypeExplain = ref()
const pgExplain = ref()

const validate = () => {
    var date_regex = RegExp(/^\d{4}-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])$/);
    
    for (let i = 0; i < items.value.length; i++) {
        items.value[i].mcht_name = items.value[i].mcht_name?.trim()
        const pg_id = pgs.find(item => item.id === items.value[i].pg_id)
        const ps_id = pss.find(item => item.id === items.value[i].ps_id)
        const settle_type = settle_types.find(item => item.id === items.value[i].settle_type)
        const module_type = module_types.find(item => item.id === items.value[i].module_type)
        const installment = installments.find(item => item.id === items.value[i].installment)
        const mcht = mchts.find(item => item.mcht_name == items.value[i].mcht_name)

        let finance_van = corp.pv_options.paid.use_realtime_deposit ? finance_vans.find(item => item.id === items.value[i].fin_id) : true
        let fin_trx_delay = corp.pv_options.paid.use_realtime_deposit ? fin_trx_delays.find(item => item.id === items.value[i].fin_trx_delay) : true
        let cxl_type = corp.pv_options.paid.use_realtime_deposit ? cxl_types.find(item => item.id === items.value[i].cxl_type) : true

        if(items.value[i].fin_id == null)
            finance_van = true
        if(items.value[i].fin_trx_delay == null)
            fin_trx_delay = true
        if(items.value[i].cxl_type == null)
            cxl_type = true
        
        if (mcht == null) {
            snackbar.value.show((i + 2) + '번째 결제모듈의 가맹점 상호가 이상합니다.('+items.value[i].mcht_name+")", 'error')
            is_clear.value = false
        }
        else if (corp.pv_options.paid.use_pmid && items.value[i].p_mid == null) {
            snackbar.value.show((i + 2) + '번째 PMID가 입력되지 않았습니다.', 'error')
            is_clear.value = false
        }
        else if (pg_id == null) {
            snackbar.value.show((i + 2) + '번째 결제모듈의 PG사명이 이상합니다.', 'error')
            is_clear.value = false
        }
        else if (ps_id == null) {
            snackbar.value.show((i + 2) + '번째 결제모듈의 구간이 이상합니다.', 'error')
            is_clear.value = false
        }
        else if (ps_id.pg_id != pg_id.id) {
            snackbar.value.show((i + 2) + '번째 결제모듈의 구간이 ' + pg_id.pg_name + '에 포함되는 구간이 아닙니다.', 'error')
            is_clear.value = false
        }
        else if (isEmpty(items.value[i].note)) {
            snackbar.value.show((i + 2) + '번째 결제모듈의 별칭은 필수로 입력해야합니다.', 'error')
            is_clear.value = false
        }
        else if (isEmpty(items.value[i].mcht_name ?? '')) {
            snackbar.value.show((i + 2) + '번째 결제모듈의 가맹점 상호는 필수로 입력해야합니다.', 'error');
            is_clear.value = false;
        }
        else if (settle_type == null) {
            snackbar.value.show((i + 2) + '번째 결제모듈의 가맹점 정산타입이 이상합니다.', 'error')
            is_clear.value = false
        }
        else if (module_type == null) {
            snackbar.value.show((i + 2) + '번째 결제모듈의 모듈타입이 이상합니다.', 'error')
            is_clear.value = false
        }
        else if (installment == null) {
            snackbar.value.show((i + 2) + '번째 결제모듈의 할부기간이 이상합니다.', 'error')
            is_clear.value = false
        }
        else if (finance_van == null) {
            snackbar.value.show((i + 2) + '번째 금융 VAN을 찾을 수 없습니다.', 'error')
            is_clear.value = false
        }
        else if (fin_trx_delay == null) {
            snackbar.value.show((i + 2) + '번째 이체 딜레이 타입을 찾을 수 없습니다.', 'error')
            is_clear.value = false
        }
        else if (cxl_type == null) {
            snackbar.value.show((i + 2) + '번째 취소 타입을 찾을 수 없습니다.', 'error')
            is_clear.value = false
        }
        else if(items.value[i].contract_s_dt && date_regex.test(items.value[i].contract_s_dt) == false)
        {            
            snackbar.value.show((i + 2) + '번째 계약 시작일 포멧이 이상합니다.', 'error')
            is_clear.value = false
        }
        else if(items.value[i].contract_e_dt && date_regex.test(items.value[i].contract_e_dt) == false)
        {            
            snackbar.value.show((i + 2) + '번째 계약 종료일 포멧이 이상합니다.', 'error')
            is_clear.value = false
        }
        else if(items.value[i].begin_dt && date_regex.test(items.value[i].begin_dt) == false)
        {            
            snackbar.value.show((i + 2) + '번째 장비 개통일 포멧이 이상합니다.', 'error')
            is_clear.value = false
        }
        else if(items.value[i].ship_out_dt && date_regex.test(items.value[i].ship_out_dt) == false)
        {            
            snackbar.value.show((i + 2) + '번째 장비 출고일 포멧이 이상합니다.', 'error')
            is_clear.value = false
        }
        else
            is_clear.value = true

        items.value[i].mcht_id = mcht?.id || null
        if (is_clear.value == false)
            return
    }
    snackbar.value.show('입력값 1차 검증에 성공하였습니다.', 'success')
    is_clear.value = true

    if(corp.pv_options.paid.use_mid_create && use_mid_create.value)
        midCreater()
    if(corp.pv_options.paid.use_online_pay && use_online_pay.value)
       payKeyCreater()
}

const midCreater = async () => {
    const mid_code = await midCreateDlg.value.show()
    if(mid_code) {
        snackbar.value.show('MID들을 자동 발급 중입니다.', 'primary')
        const params = {
            mid_code : mid_code,
            pay_mod_count : items.value.length
        }
        const r = await axios.post('/api/v1/manager/merchandises/pay-modules/mid-bulk-create', params)
        const new_mids = r.data.new_mids
        for (let i = 0; i < items.value.length; i++) {
            items.value[i].mid = new_mids[i]
        }
        snackbar.value.show('MID들이 발급 되었습니다.', 'success')
    }
}

const payKeyCreater = () => {
    const getRandomNumber = (min: number, max: number) => {
        return Math.floor(Math.random() * (max - min + 1)) + min;
    }

    const generateRandomString = (id: number) => {
        const remaining_length = 64 - id.toString().length
        const characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let result = '';
        for (let i = 0; i < remaining_length; i++) {
            const rand_idx = Math.floor(Math.random() * characters.length);
            result += characters.charAt(rand_idx);
        }
        return id + result;
    }
    
    snackbar.value.show('PAY KEY들을 자동 발급 중입니다.', 'primary')
    for (let i = 0; i < items.value.length; i++) {
        items.value[i].pay_key = generateRandomString(getRandomNumber(1, 99999))        
    }
    snackbar.value.show('PAY KEY들이 발급 되었습니다.', 'success')
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
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name>            
                    <UsageTooltip />
                    <br><br> 
                    하단 컬럼들은 숫자로 매칭되는 값들입니다.
                    <br>
                    엑셀 작성시 <b class="important-text">입력하실 내용에 매칭되는 숫자를 작성</b>해주세요.
                    <br><br>                
                    컬럼 우측의 <b>O표시는 필수 입력값, X표시는 옵션 입력값</b>을 의미합니다.
                </template>
                <template #input>
                    <VSwitch hide-details :false-value=0 :true-value=1 v-model="use_mid_create" label="MID 자동발급 여부" color="primary" v-if="corp.pv_options.paid.use_mid_create"/>
                    <VSwitch hide-details :false-value=0 :true-value=1 v-model="use_online_pay" label="PAY KEY 자동발급 여부" color="primary" v-if="corp.pv_options.paid.use_online_pay"/>
                </template>
            </CreateHalfVCol>
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
                        <b>날짜타입 입력시 주의사항</b>
                        <br>
                        <span>0000-00-00 포멧으로 입력(예: 2024-01-01)</span>
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
    <MidCreateDialog ref="midCreateDlg"/>
</template>
<style scoped>
.important-text {
  color: red;
}
</style>

