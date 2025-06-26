<script lang="ts" setup>
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue';
import { Registration } from '@/views/registration';
import UsageTooltip from '@/views/services/bulk-cms-transactions/UsageTooltip.vue';
import { axios } from '@axios';
import { useRegisterStore, validateItems } from '@/views/services/bulk-cms-transactions/BankAccountRegisterStore';
import type { PayModule, FinanceVan, VirtualAccount } from '@/views/types';
import { banks } from '@/views/users/useStore'
import corp from '@corp';

const { headers, isPrimaryHeader } = useRegisterStore()

const search = ref('')
const item_per_page = ref(100)
const page = ref(1)

const error_message = ref('')
const { ExcelFormatV2, ExcelReaderV2, openFilePicker, bulkRegister } = Registration()

const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))

const excel = ref()
const items = ref<VirtualAccount[]>([])
const is_clear = ref<boolean>(false)
const bank_clear = ref<boolean>(false)

const bank = ref(banks[0])

const validate = async () => {
    error_message.value = ''
    const acct_nums = new Set()
    for (let i = 0; i < items.value.length; i++) {        
        const results = validateItems(items.value[i], i, acct_nums)
        is_clear.value = results[0] as boolean
        error_message.value = results[1] as string

        if(is_clear.value) {
            acct_nums.add(items.value[i].acct_num)
        }

        if(is_clear.value === false) {
            error_message.value = '엑셀파일에서 ' + error_message.value
            snackbar.value.show(error_message.value, 'error')
            return
        }
    }
    snackbar.value.show('입력값 1차 검증에 성공하였습니다.', 'success')
    is_clear.value = true
}

const checkBankAccount = async () => {
    if(await alert.value.show('예금주 검증을 하시겠습니까?')) {
        try {
            await bulkRegister('예금주 검증', 'owner-check-test', items.value) // owner-check-test
        } catch (e:any) {
            snackbar.value.show(e.response.data.message, 'error')
            const r = errorHandler(e)
        }
    }
}
watchEffect(async () => {
    if (excel.value) {
        items.value = await ExcelReaderV2(headers, excel.value[0]) as VirtualAccount[]
        await validate()
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
                    엑셀 작성시 <b class="important-text">주의사항을 숙지하신 후 작성</b>해주세요.
                    <br><br>
                    컬럼 우측의 <b>O표시는 필수 입력값, X표시는 옵션 입력값</b>을 의미합니다.
                </template>
                <template #input>
                </template>
            </CreateHalfVCol>
            <VDivider />
            <CreateHalfVCol :mdl="8" :mdr="4">
                <template #name>
                    <VCol>
                        <h3 class="pt-3">입금 정보</h3>
                        <VRow>
                            <VCol md="6" cols="12">
                                <VRow>
                                    <VCol class="font-weight-bold" md="6">입금 은행명 검색</VCol>
                                    <VCol md="6">
                                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="bank"
                                            :items="banks"
                                            label="은행 검색"
                                            item-title="title" item-value="code"  return-object
                                        />
                                    </VCol>
                                </VRow>
                            </VCol>
                        </VRow>
                    </VCol>
                </template>
                <template #input>
                    <VCol>
                        <b>입금 계좌번호 입력시 </b><b class="important-text">주의사항</b>
                        <br>
                        <span>- 숫자만 입력 (예: 12345123451234)</span>
                    </VCol>
                    <VCol>
                        <b>입금 은행명 입력시 </b><b class="important-text">주의사항</b>
                        <br>
                        <span>- 입금 은행명 검색 목록에 있는 은행명과 동일하게 입력 (예: 한국은행)</span>
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
                <VCardText class="d-flex flex-wrap py-4 gap-4">
                    <h3>1차 검증 테이블</h3>
                    <div class="app-user-search-filter d-flex flex-wrap gap-4" style="margin-left: auto;">
                        <b v-if="error_message !== '' && is_clear === false" style="display: inline-flex; align-items: center;">
                            <span class="text-error">
                                {{ error_message }}
                            </span>
                        </b>
                        <div style="inline-size: 15rem;">
                            <AppTextField
                                v-model="search"
                                placeholder="검색"
                                density="compact"
                                prepend-inner-icon="tabler:search"
                            >
                            </AppTextField>
                        </div>
                    </div>
                    <VDivider/>
                    <VDataTable v-model:items-per-page="item_per_page" v-model:page="page"                     
                        :items-length="items.length" :items="items" :headers="headers" class="text-no-wrap"
                        no-data-text="양식 업로드후 등록 버튼을 클릭해주세요."
                        item-value="title" :height="corp.pv_options.free.fix_table_size"
                        :search="search">
                        <template v-slot:headers="{ columns, isSorted, getSortIcon, toggleSort }">
                            <tr>
                                <th v-for="column in columns" :key="column.key + '_headers'">
                                    <span :class="isPrimaryHeader(column.key) ? 'text-primary' : ''">
                                        {{ column.title }}
                                    </span>
                                </th>
                            </tr>
                        </template>
                        <template v-slot:item="{ item }">
                            <tr>
                                <template v-for="header in headers" :key="header.key + '_items'">
                                    <td>
                                        <span>{{ item[header.key] }}</span>                                        
                                    </td>
                                </template>
                            </tr>
                        </template>
                    </VDataTable>
                </VCardText>
            </VCard>
        </VCol>
    </VRow>
    <VCard style="margin-top: 1em;">
        <VCol class="d-flex gap-4">
            <VBtn color="secondary" variant="tonal" @click="ExcelFormatV2('계좌등록 포멧', headers)" style="margin-left: auto;">
                양식 다운로드
                <VIcon end icon="uiw-file-excel" />
            </VBtn>
            <VFileInput id='bank-accounts-uploader' accept="xlsx/*" show-size v-model="excel" v-show="false">
            </VFileInput>
            <VBtn type="button" @click="openFilePicker('bank-accounts-uploader')">
                양식 업로드
                <VIcon end icon="uiw-file-excel" />
            </VBtn>
            <VBtn type="button" @click="checkBankAccount()" v-show="is_clear">
                예금주 검증 및 등록
                <VIcon end icon="tabler-pencil" />
            </VBtn>
        </VCol>
    </VCard>
</template>
<style scoped>
.important-text {
  color: red;
}

:deep(.v-row) {
  align-items: center;
}
</style>
