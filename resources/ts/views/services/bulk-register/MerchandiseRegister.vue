<script lang="ts" setup>
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import { tax_category_types } from '@/views/merchandises/useStore'
import { Registration } from '@/views/registration'
import { useRegisterStore, validateItems } from '@/views/services/bulk-register/MerchandiseRegisterStore'
import UsageTooltip from '@/views/services/bulk-register/UsageTooltip.vue'
import { useMchtBlacklistStore } from '@/views/services/mcht-blacklists/useStore'
import { useStore } from '@/views/services/pay-gateways/useStore'
import type { Merchandise, Options } from '@/views/types'
import { banks } from '@/views/users/useStore'
import corp from '@corp'

const { cus_filters } = useStore()
const { headers, isPrimaryHeader } = useRegisterStore()
const { ExcelFormatV2, ExcelReaderV2, openFilePicker, bulkRegister } = Registration()

const search = ref('')
const item_per_page = ref(100)
const page = ref(1)

const error_message = ref('')
const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const excel = ref()
const items = ref<Merchandise[]>([])
const is_clear = ref<boolean>(false)

const bank = ref(banks[0])
const tax_category_type = ref(tax_category_types[0])
const cus_filter = ref({id:null, name:''})

const use_types: Options[] = [
    { id: 0, title: '미사용'},
    { id: 1, title: '사용'},
]
const { isMchtBlackList } =  useMchtBlacklistStore()


const validate = async() => {
    error_message.value = ''
    const user_names = new Set()
    const mcht_names = new Set()
    for (let i = 0; i < items.value.length; i++) {
        const results = validateItems(items.value[i], i, user_names, mcht_names)
        is_clear.value = results[0] as boolean
        error_message.value = results[1] as string
        
        if(is_clear.value) {
            user_names.add(items.value[i].user_name)
            mcht_names.add(items.value[i].mcht_name)
            if (Number(corp.pv_options.paid.use_mcht_blacklist)) {
                let [result, blacklist] = isMchtBlackList(items.value[i])
                if(result)
                    is_clear.value = await alert.value.show((i + 2) + '번째 가맹점은 아래이유로 인해 블랙리스트로 등록된 가맹점입니다. 그래도 진행하시겠습니까?<br><br><b style="color:red">'+blacklist?.block_reason+'</b>')
            }
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

const mchtRegister = async () => {
    if(await bulkRegister('가맹점', 'merchandises', items.value))
        location.reload()
}

watchEffect(async () => {
    if (excel.value) {
        items.value = await ExcelReaderV2(headers, excel.value[0]) as Merchandise[]
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
            <VDivider/>
            <CreateHalfVCol :mdl="8" :mdr="4">
                <template #name>
                    <VCol style="padding: 0 2em;">
                        <h3 class="pt-3">가맹점 정보</h3>
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
                                    <VCol class="font-weight-bold" md="6">사업자 유형</VCol>
                                    <VCol md="6">
                                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="tax_category_type"
                                            :items="tax_category_types"
                                            label="사업자 유형 검색"
                                            :hint="`사업자 유형 코드: ${tax_category_type.id} `"
                                            item-title="title" item-value="id" persistent-hint return-object
                                        />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol md="4" cols="12">
                                <VRow>
                                    <VCol class="font-weight-bold" md="6">커스텀 필터 검색</VCol>
                                    <VCol md="6">
                                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="cus_filter"
                                            :items="cus_filters"
                                            label="커스텀 필터 검색"
                                            :hint="`커스텀 필터 코드: ${cus_filter.id} `"
                                            item-title="name" item-value="id" persistent-hint return-object
                                        />
                                        <VTooltip activator="parent" location="top" transition="scale-transition" v-if="cus_filters.length == 0">
                                            <b>"운영 관리 - PG사 관리"에서 커스텀 필터 추가 후 입력 가능합니다.</b>
                                        </VTooltip>
                                    </VCol>
                                </VRow>
                            </VCol>
                        </VRow>
                        <VDivider style="margin: 1em 0;" />
                        <h3 class="pt-3">옵션 정보</h3>
                        <br>
                        <VRow>
                            <VCol md="4" cols="12" v-if="corp.pv_options.paid.use_collect_withdraw">
                                <VRow>
                                    <VCol class="font-weight-bold" md="6">모아서출금 사용여부</VCol>
                                    <VCol md="6">
                                        <VRow>
                                            <VChip color="primary" style="margin: 0.5em;" v-for="(cus, key) in use_types" :key="key">
                                                {{ cus.title }} = {{ cus.id }}
                                            </VChip>
                                        </VRow>
                                    </VCol>
                                </VRow>
                            </VCol>                            
                            <VCol md="4" cols="12" v-if="corp.pv_options.paid.use_regular_card">
                                <VRow>
                                    <VCol class="font-weight-bold" md="6">단골고객 사용여부</VCol>
                                    <VCol md="6">
                                        <VRow>
                                            <VChip color="primary" style="margin: 0.5em;" v-for="(cus, key) in use_types" :key="key">
                                                {{ cus.title }} = {{ cus.id }}
                                            </VChip>
                                        </VRow>
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol md="4" cols="12" v-if="corp.pv_options.paid.use_pay_verification_mobile">
                                <VRow>
                                    <VCol class="font-weight-bold" md="6">SMS 인증회수 사용여부</VCol>
                                    <VCol md="6">
                                        <VRow>
                                            <VChip color="primary" style="margin: 0.5em;" v-for="(cus, key) in use_types" :key="key">
                                                {{ cus.title }} = {{ cus.id }}
                                            </VChip>                                            
                                        </VRow>
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol md="4" cols="12" v-if="corp.pv_options.paid.use_multiple_hand_pay">
                                <VRow>
                                    <VCol class="font-weight-bold" md="6">다중결제 사용여부</VCol>
                                    <VCol md="6">
                                        <VRow>
                                            <VChip color="primary" style="margin: 0.5em;" v-for="(cus, key) in use_types" :key="key">
                                                {{ cus.title }} = {{ cus.id }}
                                            </VChip>                                            
                                        </VRow>
                                    </VCol>
                                </VRow>
                            </VCol>
                        </VRow>
                    </VCol>
                </template>
                <template #input>
                    <VCol>
                        <b class="important-text">수수료 입력 주의사항</b>
                        <br>
                        <span>- % 제외 및 실수만 입력(예: 5.00)</span>
                    </VCol>
                    <VCol>
                        <b>입력가능한 입금은행명 확인</b>
                        <br>
                        <span>- 은행코드 검색 목록에 있는 은행명과 동일하게 입력</span>
                    </VCol>
                    <VCol>
                        <b>사업자등록번호 입력 주의사항</b>
                        <br>
                        <span>- 숫자만 입력(예:1231312345)</span>
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
                                <td class='list-square'>
                                    <span v-if="(header.key).includes('_fee') && header.key != 'withdraw_fee' && header.key != 'collect_withdraw_fee'">
                                        <VChip v-if="item[header.key]">
                                            {{ item[header.key] ? (item[header.key] as number).toFixed(3)+'%' : ''}}
                                        </VChip>
                                    </span>
                                    <span v-else-if="header.key === 'custom_id'">
                                        {{ cus_filters.find(sales => sales.id === item[header.key])?.name }}
                                    </span>
                                    <span v-else>
                                        {{ item[header.key] }}
                                    </span>
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
            <VBtn color="secondary" variant="tonal" @click="ExcelFormatV2('가맹점 대량등록 포멧', headers)" style="margin-left: auto;">
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
</template>
<style scoped>
.important-text {
  color: red;
}

:deep(.v-row) {
  align-items: center;
}
</style>
