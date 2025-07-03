
<script setup lang="ts">
import { Registration } from '@/views/registration';
import { useStore } from '@/views/services/options/useStore'
import { useRegisterStore, validateItems } from '@/views/pays/bulk-payments/PaymentRegisterStore';
import { ownerCheck } from '@/views/virtuals/bulk-cms-transactions/BankAccountRegisterStore';
import SettlementInformationDialog from '@/layouts/dialogs/bulks/SettlementInformationDialog.vue'
import { banks } from '@/views/users/useStore'

const { headers } = useRegisterStore()

const search = ref('')
const item_per_page = ref(100)
const page = ref(1)

const error_message = ref('')
const { ExcelFormatV2, ExcelReaderV2, openFilePicker, bulkRegister } = Registration()
const { bill_keys } = useStore()
const snackbar = <any>(inject('snackbar'))

const excel = ref()
const items = ref<any[]>([])
const is_clear = ref<boolean>(false)
const settlementInformationDialog = ref()


const validate = async () => {
    error_message.value = ''
    for (let i = 0; i < items.value.length; i++) {        
        const results = validateItems(items.value[i], i)
        if(results[0] === false) {
            is_clear.value = results[0] as boolean
            error_message.value = '엑셀파일에서 ' + results[1] as string
            snackbar.value.show(error_message.value, 'error')
            return
        }
    }

    snackbar.value.show('예금주 검증 진행중입니다..', 'success')
    const results2 = await ownerCheck(items.value)
    if(results2[0]) {
        snackbar.value.show('입력값 1차 검증에 성공하였습니다.', 'success')
        is_clear.value = true
    }
    else {
        is_clear.value = false
        error_message.value = results2[1]
        snackbar.value.show(error_message.value, 'error')
    }
}

const register = async () => {
    if(await bulkRegister('정산건', 'pays/transactions', items.value, true))
        items.value = []
}

watchEffect(async () => {
    if (excel.value) {
        items.value = await ExcelReaderV2(headers, excel.value[0]) as any[]
        await validate()
    }
})
</script>
<template>
    <div>
        <VRow class="match-height">
            <!-- 👉 개인정보 -->
            <VCol cols="12">
                <VCard>
                    <VCardText class="d-flex flex-wrap py-4 gap-4" style="align-items: center;">
                        <h3>1차 검증 테이블</h3>
                        <VBtn @click="settlementInformationDialog.show()" size="small" color="primary" variant="tonal">
                            페이지 설명 보기
                            <VIcon end icon="ic:outline-help" />
                        </VBtn>
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
                        <br>
                        <VDivider/>
                        <VDataTable v-model:items-per-page="item_per_page" v-model:page="page"                     
                            :items-length="items.length" :items="items" :headers="headers" class="text-no-wrap"
                            no-data-text="양식 업로드후 등록 버튼을 클릭해주세요."
                            item-value="title" :height="700"
                            :search="search">
                            <template v-slot:headers="{ columns, isSorted, getSortIcon, toggleSort }">
                                <tr>
                                    <th v-for="column in columns" :key="column.key + '_headers'">
                                        <span>
                                            {{ column.title }}
                                        </span>
                                    </th>
                                </tr>
                            </template>
                            <template v-slot:item="{ item }">
                                <tr>
                                    <template v-for="header in headers" :key="header.key + '_items'">
                                        <td v-if="header.key === 'bill_id'">
                                            {{ bill_keys.find(obj => obj.id === item.bill_id)?.nick_name }}
                                        </td>
                                        <td v-else-if="header.key === 'amount'">
                                            {{ Number(item.amount).toLocaleString() }}
                                        </td>
                                        <td v-else-if="header.key === 'acct_bank_code'">
                                            {{ `${banks.find(bank => bank.code === item.acct_bank_code)?.title} (${item.acct_bank_code})` }}
                                        </td>
                                        <td v-else>
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
                <VBtn color="secondary" variant="tonal" @click="ExcelFormatV2('정산하기 포멧', headers)" style="margin-left: auto;">
                    양식 다운로드
                    <VIcon end icon="uiw-file-excel" />
                </VBtn>
                <VFileInput id='bulk-payments-uploader' accept="xlsx/*" show-size v-model="excel" v-show="false">
                </VFileInput>
                <VBtn type="button" @click="openFilePicker('bulk-payments-uploader')">
                    양식 업로드
                    <VIcon end icon="uiw-file-excel" />
                </VBtn>
                <VBtn type="button" @click="register()" v-if="is_clear">
                    등록
                    <VIcon end icon="tabler-pencil" />
                </VBtn>
            </VCol>
        </VCard>
        <SettlementInformationDialog ref="settlementInformationDialog"/>
    </div>
</template>
