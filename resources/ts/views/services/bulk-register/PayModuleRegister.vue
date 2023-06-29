<script lang="ts" setup>
import { useStore } from '@/views/services/pay-gateways/useStore'
import { useRegisterStore } from '@/views/services/bulk-register/PayModRegisterStore'
import type { PayModule } from '@/views/types'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import UsageTooltip from '@/views/services/bulk-register/UsageTooltip.vue'
import { Registration } from '@/views/registration'
import corp from '@corp';


const { pgs, pss, settle_types, terminals } = useStore()
const { head, headers } = useRegisterStore()
const { ExcelReader, isEmpty, openFilePicker, bulkRegister } = Registration()

const snackbar = <any>(inject('snackbar'))

const excel = ref()
const items = ref<PayModule[]>([])
const is_clear = ref<boolean>(false)
const banksExplain = ref()

const validate = () => {
    for (let i = 0; i < items.value.length; i++) {

        else if (isEmpty(items.value[i].user_name)) {
            snackbar.value.show((i + 1) + '번째 결제모듈의 아이디는 필수로 입력해야합니다.', 'error')
            is_clear.value = false
        }
        else if (isEmpty(items.value[i].mcht_name)) {
            snackbar.value.show((i + 1) + '번째 결제모듈의 상호는 필수로 입력해야합니다.', 'error')
            is_clear.value = false
        }
        else if (isEmpty(items.value[i].user_pw)) {
            snackbar.value.show((i + 1) + '번째 결제모듈의 패스워드는 필수로 입력해야합니다.', 'error')
            is_clear.value = false
        }
        else if (isEmpty(items.value[i].resident_num)) {
            snackbar.value.show((i + 1) + '번째 결제모듈의 주민등록번호는 필수로 입력해야합니다.', 'error')
            is_clear.value = false
        }
        else if (isEmpty(items.value[i].business_num)) {
            snackbar.value.show((i + 1) + '번째 결제모듈의 사업자번호는 필수로 입력해야합니다.', 'error')
            is_clear.value = false
        }
        else if (isEmpty(items.value[i].sector)) {
            snackbar.value.show((i + 1) + '번째 결제모듈의 업종은 필수로 입력해야합니다.', 'error')
            is_clear.value = false
        }
        else if (isEmpty(items.value[i].acct_num)) {
            snackbar.value.show((i + 1) + '번째 결제모듈의 계좌번호는 필수로 입력해야합니다.', 'error')
            is_clear.value = false
        }
        else if (isEmpty(items.value[i].acct_name)) {
            snackbar.value.show((i + 1) + '번째 결제모듈의 예금주는 필수로 입력해야합니다.', 'error')
            is_clear.value = false
        }
        else if (acct_bank_code == null) {
            snackbar.value.show((i + 1) + '번째 결제모듈의 은행코드가 이상합니다.', 'error')
            is_clear.value = false
        }
        else if (acct_bank_name == null) {
            snackbar.value.show((i + 1) + '번째 결제모듈의 입금은행명이 이상합니다.', 'error')
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

const mchtRegister = async () => {
    bulkRegister('가맹점', 'merchandises', items.value)
}

watchEffect(async () => {
    if (excel.value) {
        items.value = await ExcelReader(headers, excel.value[0]) as PayModule[]
        validate()
        console.log(items.value)
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
                        <b>커스텀 필터 -> </b><span v-html="getFiltersExplain()"></span>
                    </VCol>
                    <VCol>
                        <b>입금은행명/은행코드 테이블 -> </b>
                        <VBtn variant="tonal" @click="banksExplain.show()">
                            상세정보 확인
                        </VBtn>
                    </VCol>
                </template>
                <template #input>
                    <VCol>
                        <b>수수료 입력시 주의사항 -></b><span>%제외 및 실수만 입력(예: 5.00)</span>
                    </VCol>
                    <VCol>
                        <b>사업자등록번호 입력시 주의사항 -></b><span>정확한 사업자 번호 입력(예:123-13-12345)</span>
                    </VCol>
                    <VCol>
                        <b>주민등록번호 입력시 주의사항 -></b><span>13자리 정수 입력(예:8001017654321)</span>
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
                                <tr v-for="(item, index) in items" :key="index" style="height: 3.75rem;">
                                    <template v-for="(_header, _key, _index) in head.headers" :key="_index">
                                        <td class='list-square'>
                                            <span v-if="(_key as string).includes('_fee')">
                                                <VChip>
                                                    {{ (item[_key] as number).toFixed(3) }} %
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

