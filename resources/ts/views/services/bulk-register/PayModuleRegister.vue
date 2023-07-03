<script lang="ts" setup>
import { useStore } from '@/views/services/pay-gateways/useStore'
import { useSearchStore } from '@/views/merchandises/pay-modules/useStore'
import { useRegisterStore } from '@/views/services/bulk-register/PayModRegisterStore'
import { useMchtFilterStore } from '@/views/merchandises/useStore'
import { module_types, installments } from '@/views/merchandises/pay-modules/useStore'
import { allLevels } from '@/views/salesforces/useStore'
import SettleTypeExplainDialog from '@/views/services/bulk-register/SettleTypeExplainDialog.vue'
import PGExplainDialog from '@/views/services/bulk-register/PGExplainDialog.vue'
import UsageTooltip from '@/views/services/bulk-register/UsageTooltip.vue'
import { Registration } from '@/views/registration'
import type { PayModule, Options } from '@/views/types'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import corp from '@corp';

const { store } = useSearchStore()
const { pgs, pss, settle_types, terminals } = useStore()
const { head, headers } = useRegisterStore()
const { merchandises, getAllMerchandises } = useMchtFilterStore()

const all_levels = allLevels()
const auth_types: Options[] = [
        { id: 0, title: '비인증',},
        { id: 1, title: '구인증',},
    ]
const view_types: Options[] = [
        { id: 0, title: '숨김',},
        { id: 1, title: '노출',},
    ]
const { ExcelReader, isEmpty, openFilePicker, bulkRegister } = Registration()

const snackbar = <any>(inject('snackbar'))

const excel = ref()
const items = ref<PayModule[]>([])
const is_clear = ref<boolean>(false)

const settleTypeExplain = ref()
const pgExplain = ref()

getAllMerchandises()
const validate = () => {
    for (let i = 0; i < items.value.length; i++) {
        const pg_id = pgs.find(item => item.id === items.value[i].pg_id)
        const ps_id = pss.find(item => item.id === items.value[i].ps_id)
        const settle_type = settle_types.find(item => item.id === items.value[i].settle_type)
        const terminal_id = terminals.find(item => item.id === items.value[i].terminal_id)
        const module_type = module_types.find(item => item.id === items.value[i].module_type)
        const installment = installments.find(item => item.id === items.value[i].installment)
        const mcht = merchandises.find(item => item.mcht_name === items.value[i].mcht_name)

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
        else if (terminal_id == null) {
            snackbar.value.show((i + 1) + '번째 결제모듈의 장비 종류 이상합니다.', 'error')
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
    const result = await bulkRegister('결제모듈', 'merchandises/pay-modules', items.value)
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
            <VCol>
                <VCol>
                    <UsageTooltip />
                </VCol>
                <VCol>
                    하단 컬럼들은 숫자로 매칭되는 값들입니다.
                    <br>
                    엑셀 작성시 입력하실 내용에 매칭되는 숫자를 작성해주세요.
                </VCol>
                <VCol>
                    컬럼 우측의 <b>O표시는 필수 입력값, X표시는 옵션 입력값</b>을 의미합니다.
                </VCol>
            </VCol>
            <CreateHalfVCol :mdl="6" :mdr="6">
                <template #name>                    
                    <VCol>
                        <b>가맹점/통신비 정산타입 </b>
                        <VBtn size="small" color="success" variant="tonal" @click="settleTypeExplain.show()" style="margin: 0.5em;">
                            상세정보 확인
                        </VBtn>
                    </VCol>                    
                    <VCol>
                        <b>통신비 정산주체
                            <VChip color="primary" style="margin: 0.5em;" v-for="(level, key) in all_levels" :key="key">
                                {{ level.title }} = {{ level.id }}
                            </VChip>
                        </b>
                    </VCol>    
                    <VCol>
                        <b>PG사/구간명 </b>
                        <VBtn size="small" color="success" variant="tonal" @click="pgExplain.show()" style="margin: 0.5em;">
                            상세정보 확인
                        </VBtn>
                    </VCol>               
                    <VCol>
                        <b>결제모듈 타입
                            <VChip color="primary" style="margin: 0.5em;" v-for="(module, key) in module_types" :key="key">
                                {{ module.title }} = {{ module.id }}
                            </VChip>
                        </b>
                    </VCol>                    
                    <VCol>
                        <b>장비 종류
                            <VChip color="primary" style="margin: 0.5em;" v-for="(terminal, key) in terminals" :key="key">
                                {{ terminal.name }} = {{ terminal.id }}
                            </VChip>
                            <span v-if="terminals.length == 0">
                                "운영 관리 - PG사 관리"에서 커스텀 필터 추가 후 입력 가능합니다.
                            </span>
                        </b>
                    </VCol> 
                </template>
                <template #input>
                    <VCol>
                        <b>수기결제 여부
                            <VChip color="primary" style="margin: 0.5em;" v-for="(auth, key) in auth_types" :key="key">
                                {{ auth.title }} = {{ auth.id }}
                            </VChip>
                        </b>
                    </VCol>
                    <VCol>                        
                        <b>결제창 노출여부(기본:노출) ->
                            <VChip color="primary" style="margin: 0.5em;" v-for="(view, key) in view_types" :key="key">
                                {{ view.title }} = {{ view.id }}
                            </VChip>
                        </b>
                    </VCol>
                    <VCol>
                        <b>할부 한도 입력시 주의사항: </b><span>숫자만 입력(예: 0,2,3,4...11)</span>
                    </VCol>
                    <VCol v-if="corp.pv_options.paid.use_pay_limit">
                        <b>결제 한도 입력시 주의사항: </b><span>만원 단위로 숫자만 입력(예: 100만원=100)</span>
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
                                        <span>
                                            {{ header.ko }}
                                        </span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in items" :key="index" style="height: 3.75rem;">
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

