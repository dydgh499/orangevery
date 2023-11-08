<script lang="ts" setup>
import { lengthValidatorV2 } from '@validators'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { useRegisterStore } from '@/views/services/bulk-register/MchtRegisterStore'
import { useSalesFilterStore } from '@/views/salesforces/useStore'
import { banks } from '@/views/users/useStore'
import type { Merchandise } from '@/views/types'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import BanksExplainDialog from '@/views/services/bulk-register/BanksExplainDialog.vue'
import UsageTooltip from '@/views/services/bulk-register/UsageTooltip.vue'
import { Registration } from '@/views/registration'

interface extendMerchandise extends Merchandise {
    [key: string]: any;
}

const { cus_filters } = useStore()
const { sales } = useSalesFilterStore()
const { head, headers, levels } = useRegisterStore()
const { ExcelReader, isEmpty, openFilePicker, bulkRegister } = Registration()

const snackbar = <any>(inject('snackbar'))
const excel = ref()
const items = ref<extendMerchandise[]>([])
const is_clear = ref<boolean>(false)
const banksExplain = ref()

const isNotExistSalesforce = (is_use: boolean, sales_idx: number, item_idx: number) => {
    const sales_id = 'sales' + sales_idx + '_id';
    const sales_name = 'sales' + sales_idx + '_name';
    
    if (is_use && items.value[item_idx][sales_name]) {
        const salesforce = sales[sales_idx].value.find(sales => sales.sales_name === items.value[item_idx][sales_name])
        if (salesforce)
            items.value[item_idx][sales_id] = salesforce.id
        return salesforce == null ? true : false
    }
    else
        return false
}
const isNotExistCustomFilter = (custom_id: number | null) => {
    if (custom_id) {
        const filter = cus_filters.find(cus => cus.id === custom_id)
        return filter == null ? true : false
    }
    else
        return false
}
const validate = () => {
    for (let i = 0; i < items.value.length; i++) {
        const acct_bank_name = banks.find(sales => sales.title === items.value[i].acct_bank_name)

        if (isNotExistSalesforce(levels.sales5_use, 5, i)) {
            snackbar.value.show((i + 1) + '번째 ' + levels.sales5_name + '이(가) 존재하지 않습니다.', 'error')
            is_clear.value = false
        }
        else if (isNotExistSalesforce(levels.sales4_use, 4, i)) {
            snackbar.value.show((i + 1) + '번째 ' + levels.sales4_name + '이(가) 존재하지 않습니다.', 'error')
            is_clear.value = false
        }
        else if (isNotExistSalesforce(levels.sales3_use, 3, i)) {
            snackbar.value.show((i + 1) + '번째 ' + levels.sales3_name + '이(가) 존재하지 않습니다.', 'error')
            is_clear.value = false
        }
        else if (isNotExistSalesforce(levels.sales2_use, 2, i)) {
            snackbar.value.show((i + 1) + '번째 ' + levels.sales2_name + '이(가) 존재하지 않습니다.', 'error')
            is_clear.value = false
        }
        else if (isNotExistSalesforce(levels.sales1_use, 1, i)) {
            snackbar.value.show((i + 1) + '번째 ' + levels.sales1_name + '이(가) 존재하지 않습니다.', 'error')
            is_clear.value = false
        }
        else if (isNotExistSalesforce(levels.sales0_use, 0, i)) {
            snackbar.value.show((i + 1) + '번째 ' + levels.sales0_name + '이(가) 존재하지 않습니다.', 'error')
            is_clear.value = false
        }
        else if (isNotExistCustomFilter(items.value[i].custom_id)) {
            snackbar.value.show((i + 1) + '번째 커스텀필터가 존재하지 않습니다.', 'error')
            is_clear.value = false
        }
        else if (isEmpty(items.value[i].user_name)) {
            snackbar.value.show((i + 1) + '번째 가맹점의 아이디는 필수로 입력해야합니다.', 'error')
            is_clear.value = false
        }
        else if (isEmpty(items.value[i].mcht_name)) {
            snackbar.value.show((i + 1) + '번째 가맹점의 상호는 필수로 입력해야합니다.', 'error')
            is_clear.value = false
        }
        else if (isEmpty(items.value[i].user_pw)) {
            snackbar.value.show((i + 1) + '번째 가맹점의 패스워드는 필수로 입력해야합니다.', 'error')
            is_clear.value = false
        }
        else if (typeof lengthValidatorV2(items.value[i].resident_num, 14) != 'boolean') {
            snackbar.value.show((i + 1) + '번째 가맹점의 주민등록번호 포멧이 정확하지 않습니다.', 'error')
            is_clear.value = false
        }
        else if (isEmpty(items.value[i].sector)) {
            snackbar.value.show((i + 1) + '번째 가맹점의 업종은 필수로 입력해야합니다.', 'error')
            is_clear.value = false
        }
        else if (isEmpty(items.value[i].acct_num)) {
            snackbar.value.show((i + 1) + '번째 가맹점의 계좌번호는 필수로 입력해야합니다.', 'error')
            is_clear.value = false
        }
        else if (isEmpty(items.value[i].acct_name)) {
            snackbar.value.show((i + 1) + '번째 가맹점의 예금주는 필수로 입력해야합니다.', 'error')
            is_clear.value = false
        }
        else if (acct_bank_name == null) {
            snackbar.value.show((i + 1) + '번째 가맹점의 입금은행명이 이상합니다.', 'error')
            is_clear.value = false
        }
        else {
            items.value[i].acct_bank_code = banks.find(sales => sales.title === items.value[i].acct_bank_name)?.code as string
            is_clear.value = true
        }

        if (is_clear.value == false)
            return
    }
    snackbar.value.show('입력값 1차 검증에 성공하였습니다.', 'success')
    is_clear.value = true
}

const mchtRegister = async () => {
    const result = await bulkRegister('가맹점', 'merchandises', items.value)
}

watchEffect(async () => {
    if (excel.value) {
        items.value = await ExcelReader(headers, excel.value[0]) as extendMerchandise[]
        console.log(items.value)
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
            <VDivider/>
            <CreateHalfVCol :mdl="8" :mdr="4">
                <template #name>
                    <VCol class="pb-0">
                        <b>커스텀 필터</b>
                        <br>
                        <VChip color="primary" style="margin: 0.5em;" v-for="(cus, key) in cus_filters" :key="key">
                            {{ cus.name }} = {{ cus.id }}
                        </VChip>
                        <b v-if="cus_filters.length == 0" class="important-text">"운영 관리 - PG사 관리"에서 커스텀 필터 추가 후 입력 가능합니다.</b>                        
                    </VCol>
                </template>
                <template #input>
                    <VCol class="pb-0">
                        <b>은행명/은행코드 테이블 </b>
                        <br>
                        <VBtn size="small" color="success" variant="tonal" @click="banksExplain.show()" style="margin: 0.5em;">
                            상세정보 확인
                        </VBtn>
                    </VCol>
                    <VCol>
                        <b class="important-text">수수료 입력 주의사항</b>
                        <br>
                        <span>- % 제외 및 실수만 입력(예: 5.00)</span>
                    </VCol>
                    <VCol>
                        <b>사업자등록번호 입력 주의사항</b>
                        <br>
                        <span>- 정확한 사업자등록번호 입력(예:123-13-12345)</span>
                    </VCol>
                    <VCol>
                        <b>주민등록번호 입력 주의사항</b>
                        <br>
                        <span>- 14자리 입력(예:800101-7654321)</span>
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
                    <VCardTitle>가맹점 정보</VCardTitle>
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
                                <tr v-for="(item, index) in items" :key="index">
                                    <template v-for="(_header, _key, _index) in head.headers" :key="_index">
                                        <td class='list-square'>
                                            <span v-if="(_key as string).includes('_fee')">
                                                <VChip v-if="item[_key]">
                                                    {{ item[_key] ? (item[_key] as number).toFixed(3)+'%' : ''}}
                                                </VChip>
                                            </span>
                                            <span v-else-if="_key === 'custom_id'">
                                                {{ cus_filters.find(sales => sales.id === item[_key])?.name }}
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
            <VFileInput id='mcht-uploader' accept="xlsx/*" show-size v-model="excel" v-show="false">
            </VFileInput>
            <VBtn type="button" @click="openFilePicker('mcht-uploader')">
                양식 업로드
                <VIcon end icon="uiw-file-excel" />
            </VBtn>
            <VBtn type="button" @click="mchtRegister()" v-show="is_clear">
                등록
                <VIcon end icon="tabler-pencil" />
            </VBtn>
        </VCol>
    </VCard>
    <BanksExplainDialog ref="banksExplain" />
</template>
<style scoped>
.important-text {
  color: red;
}
</style>
