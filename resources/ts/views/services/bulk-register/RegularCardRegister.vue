<script lang="ts" setup>
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import { Registration } from '@/views/registration'
import { useSalesFilterStore } from '@/views/salesforces/useStore'
import { useRegisterStore } from '@/views/services/bulk-register/RegularCardRegisterStore'
import UsageTooltip from '@/views/services/bulk-register/UsageTooltip.vue'
import type { RegularCreditCard } from '@/views/types'
import { isEmpty } from '@core/utils'

const { head, headers } = useRegisterStore()
const { mchts } = useSalesFilterStore()


const { ExcelReader, openFilePicker, bulkRegister } = Registration()

const snackbar = <any>(inject('snackbar'))

const excel = ref()
const items = ref<RegularCreditCard[]>([])
const is_clear = ref<boolean>(false)


const validate = () => {
    for (let i = 0; i < items.value.length; i++) {
        items.value[i].mcht_name = items.value[i].mcht_name ? items.value[i].mcht_name?.trim() : ''
        const mcht = mchts.find(item => item.mcht_name == items.value[i].mcht_name)
        
        if (mcht == null) {
            snackbar.value.show((i + 2) + '번째 카드정보의 가맹점 상호가 이상합니다.', 'error')
            is_clear.value = false
        }
        else if(isEmpty(items.value[i].card_num)) {
            snackbar.value.show((i + 2) + '번째 카드정보의 카드번호를 찾을 수 없습니다.', 'error')
            is_clear.value = false
        }
        else
            is_clear.value = true

        items.value[i].mcht_id = mcht?.id as number
        if (is_clear.value == false)
            return
    }
    snackbar.value.show('입력값 1차 검증에 성공하였습니다.', 'success')
    is_clear.value = true
}
const register = async () => {
    if(await bulkRegister('단골고객 카드정보', 'merchandises/regular-credit-cards', items.value))
        location.reload()
}
watchEffect(async () => {
    if (excel.value) {
        items.value = await ExcelReader(headers, excel.value[0]) as RegularCreditCard[]
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
                        <b>카드번호 입력 주의사항</b>
                        <br>
                        <span>- 숫자만 입력</span>
                    </VCol>
                </template>
                <template #input>
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
                                <tr v-for="(item, index) in items" :key="index">
                                    <template v-for="(_header, _key, _index) in head.headers" :key="_index">
                                        <td class='list-square'>
                                            {{ item[_key] }}
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
            <VFileInput id='regular-card-uploader' accept="xlsx/*" show-size v-model="excel" v-show="false">
            </VFileInput>
            <VBtn type="button" @click="openFilePicker('regular-card-uploader')">
                양식 업로드
                <VIcon end icon="uiw-file-excel" />
            </VBtn>
            <VBtn type="button" @click="register()" v-show="is_clear">
                등록
                <VIcon end icon="tabler-pencil" />
            </VBtn>
        </VCol>
    </VCard>
</template>
<style scoped>
.important-text {
  color: red;
}
</style>

