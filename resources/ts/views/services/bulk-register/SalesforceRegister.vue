<script lang="ts" setup>
import { businessNumValidator, lengthValidatorV2 } from '@validators'
import { useSearchStore } from '@/views/salesforces/useStore'
import { useRegisterStore } from '@/views/services/bulk-register/SalesRegisterStore'
import { salesLevels, settleCycles, settleDays, settleTaxTypes } from '@/views/salesforces/useStore'
import type { Salesforce } from '@/views/types'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import BanksExplainDialog from '@/views/services/bulk-register/BanksExplainDialog.vue'
import UsageTooltip from '@/views/services/bulk-register/UsageTooltip.vue'
import { Registration } from '@/views/registration'
import { banks } from '@/views/users/useStore'
import corp from '@corp'

const { store } = useSearchStore()
const { head, headers } = useRegisterStore()
const { ExcelReader, isEmpty, openFilePicker, bulkRegister } = Registration()
const snackbar = <any>(inject('snackbar'))
const all_sales = salesLevels()
const all_cycles = settleCycles()
const all_days = settleDays()
const tax_types = settleTaxTypes()

const excel = ref()
const saleses = ref<Salesforce[]>([])
const is_clear = ref<boolean>(false)
const banksExplain = ref()
const levels = corp.pv_options.auth.levels


const validate = () => {
    for (let i = 0; i < saleses.value.length; i++) {
        saleses.value[i].settle_day = saleses.value[i].settle_day == -1 ? null : saleses.value[i].settle_day;

        const level = all_sales.find(sales => sales.id === saleses.value[i].level)
        const settle_cycle = all_cycles.find(sales => sales.id === saleses.value[i].settle_cycle)
        const settle_day = all_days.find(sales => sales.id === saleses.value[i].settle_day)
        const settle_tax_type = tax_types.find(sales => sales.id === saleses.value[i].settle_tax_type)

        const acct_bank_code = banks.find(sales => sales.code === saleses.value[i].acct_bank_code)
        const acct_bank_name = banks.find(sales => sales.title === saleses.value[i].acct_bank_name)

        if (isEmpty(saleses.value[i].user_name)) {
            snackbar.value.show((i + 1) + '번째 영업점의 아이디는 필수로 입력해야합니다.', 'error')
            is_clear.value = false
        }
        else if (isEmpty(saleses.value[i].user_pw)) {
            snackbar.value.show((i + 1) + '번째 영업점의 패스워드는 필수로 입력해야합니다.', 'error')
            is_clear.value = false
        }
        else if (isEmpty(saleses.value[i].resident_num)) {
            snackbar.value.show((i + 1) + '번째 영업점의 주민등록번호는 필수로 입력해야합니다.', 'error')
            is_clear.value = false
        }
        else if (isEmpty(saleses.value[i].business_num)) {
            snackbar.value.show((i + 1) + '번째 영업점의 사업자등록번호는 필수로 입력해야합니다.', 'error')
            is_clear.value = false
        }
        else if (isEmpty(saleses.value[i].resident_num)) {
            snackbar.value.show((i + 1) + '번째 영업점의 주민등록번호는 필수로 입력해야합니다.', 'error')
            is_clear.value = false
        }
        else if (isEmpty(saleses.value[i].business_num)) {
            snackbar.value.show((i + 1) + '번째 영업점의 사업자등록번호는 필수로 입력해야합니다.', 'error')
            is_clear.value = false
        }
        else if (typeof businessNumValidator(saleses.value[i].business_num) != 'boolean') {
            snackbar.value.show((i + 1) + '번째 영업점의 사업자등록번호 포멧이 정확하지 않습니다.', 'error')
            is_clear.value = false
        }
        else if (typeof lengthValidatorV2(saleses.value[i].resident_num, 14) != 'boolean') {
            snackbar.value.show((i + 1) + '번째 영업점의 주민등록번호 포멧이 정확하지 않습니다.', 'error')
            is_clear.value = false
        }
        else if (level == null) {
            snackbar.value.show((i + 1) + '번째 영업점의 등급이 이상합니다.', 'error')
            is_clear.value = false
        }
        else if (settle_cycle == null) {
            snackbar.value.show((i + 1) + '번째 영업점의 정산주기가 이상합니다.', 'error')
            is_clear.value = false
        }
        else if (settle_day == null) {
            snackbar.value.show((i + 1) + '번째 영업점의 정산일이 이상합니다.', 'error')
            is_clear.value = false
        }
        else if (settle_tax_type == null) {
            snackbar.value.show((i + 1) + '번째 영업점의 정산세율이 이상합니다.', 'error')
            is_clear.value = false
        }
        else if (isEmpty(saleses.value[i].acct_num)) {
            snackbar.value.show((i + 1) + '번째 영업점의 계좌번호는 필수로 입력해야합니다.', 'error')
            is_clear.value = false
        }
        else if (isEmpty(saleses.value[i].acct_name)) {
            snackbar.value.show((i + 1) + '번째 영업점의 예금주는 필수로 입력해야합니다.', 'error')
            is_clear.value = false
        }
        else if (acct_bank_code == null) {
            snackbar.value.show((i + 1) + '번째 영업점의 은행코드가 이상합니다.', 'error')
            is_clear.value = false
        }
        else if (acct_bank_name == null) {
            snackbar.value.show((i + 1) + '번째 영업점의 입금은행명이 이상합니다.', 'error')
            is_clear.value = false
        }
        else
            is_clear.value = true

        if (is_clear.value == false)
            return
    }
    snackbar.value.show('입력값 1차 검증에 성공하였습니다.', 'success')
    is_clear.value = true
}

const salesRegister = async () => {
    const result = await bulkRegister('영업점', 'salesforces', saleses.value)
}
watchEffect(async () => {
    if (excel.value) {
        saleses.value = await ExcelReader(headers, excel.value[0]) as Salesforce[]
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
                        <b>등급
                            <VChip color="primary" style="margin: 0.5em;" v-if="levels.sales5_use">
                                {{ levels.sales5_name }} = 30
                            </VChip>
                            <VChip color="primary" style="margin: 0.5em;" v-if="levels.sales4_use">
                                {{ levels.sales4_name }} = 25
                            </VChip>
                            <VChip color="primary" style="margin: 0.5em;" v-if="levels.sales3_use">
                                {{ levels.sales3_name }} = 20
                            </VChip>
                            <VChip color="primary" style="margin: 0.5em;" v-if="levels.sales2_use">
                                {{ levels.sales2_name }} = 17
                            </VChip>
                            <VChip color="primary" style="margin: 0.5em;" v-if="levels.sales1_use">
                                {{ levels.sales1_name }} = 15
                            </VChip>
                            <VChip color="primary" style="margin: 0.5em;" v-if="levels.sales1_use">
                                {{ levels.sales1_name }} = 13
                            </VChip>
                        </b>
                    </VCol>
                    <VCol>
                        <b>정산세율
                            <VChip color="primary" style="margin: 0.5em;" v-for="(tax_type, key) in tax_types" :key="key">
                                {{ tax_type.title }} = {{ tax_type.id }}
                            </VChip>
                        </b>
                    </VCol>
                    <VCol>
                        <b>정산주기
                            <VChip color="primary" style="margin: 0.5em;" v-for="(all_cycle, key) in all_cycles" :key="key">
                                {{ all_cycle.title }} = {{ all_cycle.id }}
                            </VChip>
                        </b>
                    </VCol>
                    <VCol>
                        <b>정산일
                            <VChip color="primary" style="margin: 0.5em;" v-for="(all_day, key) in all_days" :key="key">
                                {{ all_day.title }} = {{ all_day.id != null ? all_day.id : -1 }}
                            </VChip>
                        </b>
                    </VCol>
                    <VCol>
                        <b>입금은행명/은행코드 테이블 </b>
                        <VBtn size="small" color="success" variant="tonal" @click="banksExplain.show()" style="margin: 0.5em;">
                            상세정보 확인
                        </VBtn>
                    </VCol>
                </template>
                <template #input>
                    <VCol>
                        <b>사업자등록번호 입력 주의사항: </b><span>정확한 사업자등록번호 입력(예:123-13-12345)</span>
                    </VCol>
                    <VCol>
                        <b>주민등록번호 입력 주의사항: </b><span>14자리 입력(예:800101-7654321)</span>
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
                    <VCardTitle>영업점 정보</VCardTitle>
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
                                <tr v-for="(item, index) in saleses" :key="index" style="height: 3.75rem;">
                                    <template v-for="(_header, _key, _index) in head.headers" :key="_index">
                                        <td class='list-square'>
                                            <span v-if="_key == 'level'">
                                                <VChip
                                                    :color="store.getSelectIdColor(all_sales.find(obj => obj.id === item[_key])?.id)">
                                                    {{ all_sales.find(sales => sales.id === item[_key])?.title }}
                                                </VChip>
                                            </span>
                                            <span v-else-if="_key == 'settle_cycle'">
                                                <VChip
                                                    :color="store.getSelectIdColor(all_cycles.find(obj => obj.id === item[_key])?.id)">
                                                    {{ all_cycles.find(sales => sales.id === item[_key])?.title }}
                                                </VChip>
                                            </span>
                                            <span v-else-if="_key == 'settle_day'">
                                                {{ all_days.find(sales => sales.id === item[_key])?.title }}
                                            </span>
                                            <span v-else-if="_key == 'settle_tax_type'">
                                                <VChip
                                                    :color="store.getSelectIdColor(tax_types.find(obj => obj.id === item[_key])?.id)">
                                                    {{ tax_types.find(sales => sales.id === item[_key])?.title }}
                                                </VChip>
                                            </span>
                                            <span v-else>
                                                {{ item[_key] }}
                                            </span>
                                        </td>
                                    </template>
                                </tr>
                            </tbody>
                            <tfoot v-show="!Boolean(saleses.length)">
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
            <VFileInput id='sales-uploader' accept="xlsx/*" show-size v-model="excel" v-show="false">
            </VFileInput>
            <VBtn type="button" @click="openFilePicker('sales-uploader')">
                양식 업로드
                <VIcon end icon="uiw-file-excel" />
            </VBtn>
            <VBtn type="button" @click="salesRegister()" v-show="is_clear">
                등록
                <VIcon end icon="tabler-pencil" />
            </VBtn>
        </VCol>
    </VCard>
    <BanksExplainDialog ref="banksExplain" />
</template>

