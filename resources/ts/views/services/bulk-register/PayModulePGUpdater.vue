<script lang="ts" setup>
import { axios } from '@axios';
import { useRegisterStore } from '@/views/services/bulk-register/PayModulePGStore'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { useSalesFilterStore } from '@/views/salesforces/useStore'
import PGExplainDialog from '@/views/services/bulk-register/PGExplainDialog.vue'
import { Registration } from '@/views/registration'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'

const { head, headers } = useRegisterStore()
const { mchts } = useSalesFilterStore()
const { pgs, pss } = useStore()


const { ExcelReader, openFilePicker } = Registration()

const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))

const excel = ref()
const items = ref<any[]>([])
const is_clear = ref<boolean>(false)

const pgExplain = ref()

const validate = () => {
    for (let i = 0; i < items.value.length; i++) {
        items.value[i].mcht_name = items.value[i].mcht_name?.trim()
        const pg_id = pgs.find(item => item.id === items.value[i].pg_id)
        const ps_id = pss.find(item => item.id === items.value[i].ps_id)
        const mcht  = mchts.find(item => item.mcht_name == items.value[i].mcht_name)

        if (mcht == null) {
            snackbar.value.show((i + 1) + '번째 가맹점 상호가 이상합니다.', 'error')
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
    if (await alert.value.show('정말 구간 변경정보' + items.value.length + '개를 대량 등록하시겠습니까?')) {
        try {
            const r = await axios.post('/api/v1/manager/merchandises/pay-modules/pg-bulk-updater', items.value)
            snackbar.value.show('성공하였습니다.', 'success')
            location.reload()
        }
        catch (e: any) {
            snackbar.value.show(e.response.data.message, 'error')
            const r = errorHandler(e)
        }
    }
}
watchEffect(async () => {
    if (excel.value) {
        items.value = await ExcelReader(headers, excel.value[0]) as any[]
        validate()
    }
})
</script>
<template>
    <VCard style='margin-top: 1em;'>
        <VRow style="padding: 1em;">
            <VCol style="padding-bottom: 0;">
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
                </template>
                <template #input>
                    <VCol class="pb-0">
                        <b>PG사/구간명</b>
                        <br>
                        <VBtn size="small" color="success" variant="tonal" @click="pgExplain.show()" style="margin: 0.5em;">
                            상세정보 확인
                        </VBtn>
                    </VCol>
                    <VCol>
                        <b>구간 변경 주의사항</b>
                        <br>
                        <span>- 가맹점 하위의 모든 결제모듈의 PG사, 구간정보가 일괄 변경됩니다.</span>
                    </VCol>
                </template>
            </CreateHalfVCol>
        </VRow>
    </VCard>
    <br>
    <VRow class="match-height">
        <VCol cols="12">
            <VCard>
                <VCardItem>
                    <VCardTitle>변경될 가맹점 구간 정보</VCardTitle>
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
            <VFileInput id='payment-pg-uploader' accept="xlsx/*" show-size v-model="excel" v-show="false">
            </VFileInput>
            <VBtn type="button" @click="openFilePicker('payment-pg-uploader')">
                양식 업로드
                <VIcon end icon="uiw-file-excel" />
            </VBtn>
            <VBtn type="button" @click="register()" v-show="is_clear">
                업데이트
                <VIcon end icon="tabler-pencil" />
            </VBtn>
        </VCol>
    </VCard>    
    <PGExplainDialog ref="pgExplain" />
</template>
<style scoped>
.important-text {
  color: red;
}
</style>

