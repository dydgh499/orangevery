<script lang="ts" setup>
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import { Registration } from '@/views/registration'
import { settleCycles, settleDays, settleTaxTypes, useSearchStore } from '@/views/salesforces/useStore'
import { useRegisterStore, validateItems } from '@/views/services/bulk-register/SalesforceRegisterStore'
import UsageTooltip from '@/views/services/bulk-register/UsageTooltip.vue'
import type { Salesforce } from '@/views/types'
import { banks } from '@/views/users/useStore'
import { salesLevels } from '@axios'
import corp from '@corp'

const { store } = useSearchStore()
const { head, headers, isPrimaryHeader } = useRegisterStore()
const { ExcelReaderV2, openFilePicker, bulkRegister } = Registration()
const snackbar = <any>(inject('snackbar'))
const all_sales = salesLevels()
const all_cycles = settleCycles()
const all_days = settleDays()
const tax_types = settleTaxTypes()
const view_types = [
    { id: 0, title: '간편보기'},
    { id: 1, title: '상세보기'},
]

const search = ref('')
const item_per_page = ref(100)
const page = ref(1)

const excel = ref()
const items = ref<Salesforce[]>([])
const is_clear = ref<boolean>(false)
const error_message = ref('')
const bank = ref(banks[0])
const level = ref(all_sales[0])
const tax_type = ref(tax_types[0])
const all_cycle = ref(all_cycles[0])
const all_day = ref(all_days[0])

const validate = () => {
    error_message.value = ''
    const user_names = new Set()
    for (let i = 0; i < items.value.length; i++) {
        const results = validateItems(items.value[i], i, user_names)
        is_clear.value = results[0] as boolean
        error_message.value = results[1] as string
        if (is_clear.value == false)
            return
        else
            user_names.add(items.value[i].user_name)
    }
    snackbar.value.show('입력값 1차 검증에 성공하였습니다.', 'success')
    is_clear.value = true
}

const register = async () => {
    if(await bulkRegister('영업점', 'salesforces', items.value))
        location.reload()
}
watchEffect(async () => {
    if (excel.value) {
        items.value = await ExcelReaderV2(headers, excel.value[0]) as Salesforce[]
        validate()
    }
})
</script>
<template>
    <VCard style='margin-top: 1em;'>
        <VRow style="padding: 1em;">
            <VCol class="pb-0">
                <VCol>
                    <UsageTooltip />
                </VCol>
                <VCol>
                    하단 검색란들은 숫자로 매칭되는 값들입니다.
                    <br>
                    엑셀 작성시 <b class="important-text">입력하실 내용에 매칭되는 코드를 작성</b>해주세요.
                </VCol>
                <VCol>
                    컬럼 우측의 <b>O표시는 필수 입력값, X표시는 옵션 입력값</b>을 의미합니다.
                </VCol>
            </VCol>
            <VDivider/>
            <CreateHalfVCol :mdl="8" :mdr="4">
                <template #name>
                    <VCol style="padding: 0 2em;">
                        <h3 class="pt-3">영업점 정보</h3>
                        <br>
                        <VRow>
                            <VCol md="4" cols="12">
                                <VRow>
                                    <VCol class="font-weight-bold" md="6">은행코드 검색</VCol>
                                    <VCol md="6">
                                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="bank"
                                            :items="banks"
                                            label="은행 검색"
                                            :hint="`은행 코드: ${bank.code} `"
                                            item-title="title" item-value="code" persistent-hint return-object
                                        />
                                    </VCol>

                                </VRow>
                            </VCol>
                            <VCol md="4" cols="12">
                                <VRow>
                                    <VCol class="font-weight-bold" md="6">등급 검색</VCol>
                                    <VCol md="6">
                                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="level"
                                            :items="all_sales"
                                            label="등급 검색"
                                            :hint="`등급 코드: ${level.id} `"
                                            item-title="title" item-value="id" persistent-hint return-object
                                        />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol md="4" cols="12">
                                <VRow>
                                    <VCol class="font-weight-bold" md="6">화면타입 사용여부</VCol>
                                    <VCol md="6">
                                        <VRow>
                                            <VChip color="primary" style="margin: 0.5em;" v-for="(cus, key) in view_types" :key="key">
                                                {{ cus.title }} = {{ cus.id }}
                                            </VChip>                                            
                                        </VRow>
                                    </VCol>
                                </VRow>
                            </VCol>
                        </VRow>
                        <VDivider style="margin: 1em 0;" />
                        <h3 class="pt-3">정산정보</h3>
                        <br>
                        <VRow>
                            <VCol md="4" cols="12">
                                <VRow>
                                    <VCol class="font-weight-bold" md="6">정산세율 검색</VCol>
                                    <VCol md="6">
                                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="tax_type"
                                            :items="tax_types"
                                            label="정산세율 검색"
                                            :hint="`정산세율 코드: ${tax_type.id} `"
                                            item-title="title" item-value="id" persistent-hint return-object
                                        />
                                    </VCol>

                                </VRow>
                            </VCol>
                            <VCol md="4" cols="12">
                                <VRow>
                                    <VCol class="font-weight-bold" md="6">정산주기 검색</VCol>
                                    <VCol md="6">
                                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="all_cycle"
                                            :items="all_cycles"
                                            label="등급 검색"
                                            :hint="`정산주기 코드: ${all_cycle.id} `"
                                            item-title="title" item-value="id" persistent-hint return-object
                                        />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol md="4" cols="12">
                                <VRow>
                                    <VCol class="font-weight-bold" md="6">정산일 검색</VCol>
                                    <VCol md="6">
                                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="all_day"
                                            :items="all_days"
                                            label="정산일 검색"
                                            :hint="all_day.id === null ? `정산일 코드:` : `정산일 코드: ${all_day.id} `"
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
                        <b>입력가능한 입금은행명 확인</b>
                        <br>
                        <span>- 은행코드 검색 목록에 있는 은행명과 동일하게 입력</span>
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
                                        <td v-if="header.key == 'level'">
                                            <VChip
                                                :color="store.getSelectIdColor(all_sales.find(obj => obj.id === item[header.key])?.id)">
                                                {{ all_sales.find(sales => sales.id === item[header.key])?.title }}
                                            </VChip>
                                        </td>
                                        <td v-else-if="header.key == 'settle_cycle'">
                                            <VChip
                                                :color="store.getSelectIdColor(all_cycles.find(obj => obj.id === item[header.key])?.id)">
                                                {{ all_cycles.find(sales => sales.id === item[header.key])?.title }}
                                            </VChip>
                                        </td>
                                        <td v-else-if="header.key == 'settle_day'">
                                            {{ all_days.find(sales => sales.id === item[header.key])?.title }}
                                        </td>
                                        <td v-else-if="header.key == 'settle_tax_type'">
                                            <VChip
                                                :color="store.getSelectIdColor(tax_types.find(obj => obj.id === item[header.key])?.id)">
                                                {{ tax_types.find(sales => sales.id === item[header.key])?.title }}
                                            </VChip>
                                        </td>
                                        <td v-else>
                                            {{ item[header.key] }}
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

:deep(.v-row) {
  align-items: center;
}
</style>

