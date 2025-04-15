<script lang="ts" setup>
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue';
import { Registration } from '@/views/registration';
import { useSalesFilterStore } from '@/views/salesforces/useStore';
import UsageTooltip from '@/views/services/bulk-register/UsageTooltip.vue';
import { useRegisterStore, validateItems } from '@/views/services/bulk-register/VirtualAccountRegisterStore';
import { useStore } from '@/views/services/pay-gateways/useStore';
import type { PayModule } from '@/views/types';
import { fin_trx_delays, useSearchStore, withdraw_limit_types, withdraw_types } from '@/views/virtual-accounts/wallets/useStore';
import corp from '@corp';

const { store } = useSearchStore()
const { finance_vans } = useStore()
const { headers, isPrimaryHeader } = useRegisterStore()
const { mchts } = useSalesFilterStore()

const search = ref('')
const item_per_page = ref(100)
const page = ref(1)

const error_message = ref('')
const { ExcelFormatV2, ExcelReaderV2, openFilePicker, bulkRegister } = Registration()

const snackbar = <any>(inject('snackbar'))

const excel = ref()
const items = ref<PayModule[]>([])
const is_clear = ref<boolean>(false)

const finance_van  = ref({'id':null, 'nick_name': ''})
const fin_trx_delay = ref(fin_trx_delays[0])
const withdraw_limit_type = ref(withdraw_limit_types[0])
const withdraw_type = ref(withdraw_types[0])

const validate = async () => {
    error_message.value = ''
    for (let i = 0; i < items.value.length; i++) {        
        const results = validateItems(items.value[i], i, mchts, finance_vans)
        is_clear.value = results[0] as boolean
        error_message.value = results[1] as string

        if(is_clear.value === false) {
            error_message.value = '엑셀파일에서 ' + error_message.value
            snackbar.value.show(error_message.value, 'error')
            return
        }
    }
    snackbar.value.show('입력값 1차 검증에 성공하였습니다.', 'success')
    is_clear.value = true
}

const virtualAccountRegister = async () => {
    if (await bulkRegister('정산지갑', 'virtual-accounts/wallets', items.value))
        location.reload()
}

watchEffect(async () => {
    if (excel.value) {
        items.value = await ExcelReaderV2(headers, excel.value[0]) as PayModule[]
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
                    하단 컬럼들은 숫자로 매칭되는 값들입니다.
                    <br>
                    엑셀 작성시 <b class="important-text">입력하실 내용에 매칭되는 코드를 작성</b>해주세요.
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
                        <h3 class="pt-3">출금 정보</h3>
                        <VRow>
                            <VCol md="3" cols="12">
                                <VRow>
                                    <VCol class="font-weight-bold" md="6">이체모듈 타입</VCol>
                                    <VCol md="6">
                                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="finance_van"
                                            :items="finance_vans"
                                            label="이체모듈타입 검색"
                                            :hint="`이체모듈타입 코드: ${finance_van ? finance_van.id : ''} `"
                                            item-title="nick_name" item-value="id" persistent-hint return-object
                                        />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol md="3" cols="12">
                                <VRow>
                                    <VCol class="font-weight-bold" md="6">이체딜레이</VCol>
                                    <VCol md="6">
                                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="fin_trx_delay"
                                            :items="fin_trx_delays"
                                            label="이체딜레이 검색"
                                            :hint="`이체딜레이 코드: ${fin_trx_delay ? fin_trx_delay.id : ''} `"
                                            item-title="title" item-value="id" persistent-hint return-object
                                        />                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol md="3" cols="12">
                                <VRow>
                                    <VCol class="font-weight-bold" md="6">출금타입</VCol>
                                    <VCol md="6">
                                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="withdraw_type"
                                            :items="withdraw_types"
                                            label="출금타입 검색"
                                            :hint="`출금타입 코드: ${withdraw_type ? withdraw_type.id : ''} `"
                                            item-title="title" item-value="id" persistent-hint return-object
                                        />
                                    </VCol>
                                </VRow>
                            </VCol>
                        </VRow>
                        <VDivider style="margin: 1em 0;" />
                        <h3 class="pt-3">제한 정보</h3>
                        <VRow>
                            <VCol md="3" cols="12">
                                <VRow>
                                    <VCol class="font-weight-bold" md="6">출금제한 타입</VCol>
                                    <VCol md="6">
                                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="withdraw_limit_type"
                                            :items="withdraw_limit_types"
                                            label="출금제한 타입 검색"
                                            :hint="`출금제한 타입 코드: ${withdraw_limit_type ? withdraw_limit_type.id : ''} `"
                                            item-title="title" item-value="id" persistent-hint return-object
                                        />
                                    </VCol>
                                </VRow>
                            </VCol>
                        </VRow>
                    </VCol>
                </template>
                <template #input>
                    <VCol>
                        <b class="important-text">한도 입력시 주의사항</b>
                        <br>
                        <span>- 만원 단위로 숫자만 입력(예: 100만원=100)</span>
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
                                    <td v-if="header.key === 'fin_id'">
                                        {{ finance_vans.find(obj => obj.id === item.fin_id)?.nick_name }}
                                    </td>
                                    <td v-else-if="header.key === 'fin_trx_delay'">
                                        {{ fin_trx_delays.find(obj => obj.id === item.fin_trx_delay)?.title }}
                                    </td>
                                    <td v-else-if="header.key === 'withdraw_limit_type'">
                                        <VChip :color="store.getSelectIdColor(withdraw_limit_types.find(obj => obj.id === item.withdraw_limit_type)?.id)">
                                            {{ withdraw_limit_types.find(obj => obj.id === item.withdraw_limit_type)?.title }}
                                        </VChip>
                                    </td>
                                    <td v-else-if="header.key === 'withdraw_type'">
                                        {{ withdraw_types.find(obj => obj.id === item.withdraw_type)?.title }}
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
            <VBtn color="secondary" variant="tonal" @click="ExcelFormatV2('정산지갑 포멧', headers)" style="margin-left: auto;">
                양식 다운로드
                <VIcon end icon="uiw-file-excel" />
            </VBtn>
            <VFileInput id='virtual-account-uploader' accept="xlsx/*" show-size v-model="excel" v-show="false">
            </VFileInput>
            <VBtn type="button" @click="openFilePicker('virtual-account-uploader')">
                양식 업로드
                <VIcon end icon="uiw-file-excel" />
            </VBtn>
            <VBtn type="button" @click="virtualAccountRegister()" v-show="is_clear">
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

:deep(.v-row) {
  align-items: center;
}
</style>
