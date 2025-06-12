<script lang="ts" setup>
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue';
import { Registration } from '@/views/registration';
import UsageTooltip from '@/views/services/bulk-register/UsageTooltip.vue';
import { axios } from '@axios';
import { useRegisterStore, validateItems } from '@/views/services/bulk-register/WithdrawRegisterStore';
import { useStore } from '@/views/services/pay-gateways/useStore';
import type { FinanceVan, Withdraw } from '@/views/types';
import { banks } from '@/views/users/useStore'
import corp from '@corp';

const { finance_vans } = useStore()
const { headers, isPrimaryHeader } = useRegisterStore()

const search = ref('')
const item_per_page = ref(100)
const page = ref(1)

const error_message = ref('')
const { ExcelFormatV2, ExcelReaderV2, openFilePicker, bulkRegister } = Registration()

const snackbar = <any>(inject('snackbar'))

const excel = ref()
const transferTime = ref<string>('') // 이체 시간
const items = ref<Withdraw[]>([])
const is_clear = ref<boolean>(false)

const bank = ref(banks[0])
const fin_id = ref(null)

const getToday = () => {
    const today = new Date()
    const yyyy = today.getFullYear()
    const mm = String(today.getMonth() + 1).padStart(2, '0')
    const dd = String(today.getDate()).padStart(2, '0')
    return `${yyyy}-${mm}-${dd}`
}

const normalizeTime = (time: string) => {
    if (/^\d{2}:\d{2}$/.test(time)) {
        return `${time}:00`
    }
    return time
}

const makeWithdrawBookTime = (time: string) => {
    return `${getToday()} ${normalizeTime(time)}`
}

const isPastTime = (timeStr: string): boolean => {
    if (!timeStr) return false
    
    const now = new Date()
    const [hours, minutes] = timeStr.split(':').map(Number)
    
    // 현재 시간과 분 가져오기
    const currentHours = now.getHours()
    const currentMinutes = now.getMinutes()
    
    // 시간 비교
    if (hours < currentHours) {
        return true
    }
    
    // 시간이 같을 경우 분 비교
    if (hours === currentHours && minutes <= currentMinutes) {
        return true
    }
    
    return false
}

const validateTransferTime = (time: string): boolean => {
    if (!time) return false
    
    // 형식 검사 (HH:MM 또는 HH:MM:SS)
    const timeFormatRegex = /^([01]\d|2[0-3]):([0-5]\d)(:([0-5]\d))?$/
    if (!timeFormatRegex.test(time)) {
        error_message.value = '올바른 시간 형식(HH:MM 또는 HH:MM:SS)을 입력해주세요.'
        return false
    }
    
    // 과거 시간 검사
    if (isPastTime(time)) {
        error_message.value = '이체 시간은 현재 시각보다 미래여야 합니다.'
        return false
    }
    
    return true
}

const withdrawAcctHint = () => {
    const finance_van = <FinanceVan>(finance_vans.find(obj => obj.id == fin_id.value))
    if(finance_van)
        return `이체모듈타입 코드: ${finance_van.id}, 은행코드: ${finance_van.bank_code}, 계좌번호: ${finance_van.withdraw_acct_num}`
    else
        return ``
}

const validate = async () => {
    error_message.value = ''
    const deposit_acct_nums = new Set()
    for (let i = 0; i < items.value.length; i++) {        
        const results = validateItems(items.value[i], i, deposit_acct_nums, finance_vans)
        is_clear.value = results[0] as boolean
        error_message.value = results[1] as string

        if(is_clear.value) {
            deposit_acct_nums.add(items.value[i].deposit_acct_num)
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

const bulkWithdrawRequest = async () => {
  if (!transferTime.value) {
    snackbar.value.show('이체 시간을 설정해주세요.', 'error')
    return
  }
  
  if (!validateTransferTime(transferTime.value)) {
    snackbar.value.show(error_message.value, 'error')
    return
  }
   
  const [hh, mm] = transferTime.value.split(':')
  const totalItems = items.value.length
  // 60초에 아이템을 고르게 분배
  const base = Math.floor(totalItems / 60)
  const remainder = totalItems % 60

  // 초당 배정 개수 배열 생성
  const distribution = Array(60).fill(base).map((v, i) => v + (i < remainder ? 1 : 0))

  let itemIndex = 0

  const newItems: Withdraw[] = []

  for (let second = 0; second < 60; second++) {
    const count = distribution[second]
    for (let i = 0; i < count; i++) {
      const ss = String(second).padStart(2, '0')
      newItems.push({
        ...items.value[itemIndex],
        withdraw_book_time: `${getToday()} ${hh}:${mm}:${ss}`
      })
      itemIndex++
    }
  }

  items.value = newItems
  await bulkRegister('출금예약', 'bulk-withdraws', items.value)
}

watchEffect(async () => {
    if (excel.value) {
        items.value = await ExcelReaderV2(headers, excel.value[0]) as Withdraw[]
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
                        <h3 class="pt-3">출금 정보</h3>
                        <VRow>
                            <VCol md="6" cols="12">
                                <VRow>
                                    <VCol class="font-weight-bold" md="6">이체모듈 타입 검색</VCol>
                                    <VCol md="6">
                                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="fin_id"
                                            :items="finance_vans"
                                            label="출금 이체모듈타입 검색"
                                            item-title="nick_name"
                                            item-value="id"
                                            persistent-hint single-line
                                            :hint="withdrawAcctHint()"
                                        />
                                    </VCol>
                                </VRow>
                            </VCol>
                        </VRow>
                    </VCol>
                </template>
                <template #input>
                    <VCol>
                        <b>출금 금액 입력시 </b><b class="important-text">주의사항</b>
                        <br>
                        <span>- 금액 전체를 숫자로 입력 (예: 100만원=1000000)</span>
                    </VCol>
                    <VCol>
                        <b>입금 계좌번호 입력시 </b><b class="important-text">주의사항</b>
                        <br>
                        <span>- 숫자만 입력 (예: 12345123451234)</span>
                    </VCol>
                    <VCol>
                        <b>이체모듈 타입 입력시 </b><b class="important-text">주의사항</b>
                        <br>
                        <span>- 이체모듈타입 검색색에 있는 이체모듈타입 코드만 입력 (예: 3)</span>
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
            <VTextField type="time" label="이체 시간 설정"
                v-model="transferTime" 
                style="max-width: 10em;"
            />
            <VBtn color="secondary" variant="tonal" @click="ExcelFormatV2('출금예약 포멧', headers)" style="margin-left: auto;">
                양식 다운로드
                <VIcon end icon="uiw-file-excel" />
            </VBtn>
            <VFileInput id='withdraw-uploader' accept="xlsx/*" show-size v-model="excel" v-show="false">
            </VFileInput>
            <VBtn type="button" @click="openFilePicker('withdraw-uploader')">
                양식 업로드
                <VIcon end icon="uiw-file-excel" />
            </VBtn>
            <VBtn type="button" @click="bulkWithdrawRequest()" v-show="is_clear">
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
